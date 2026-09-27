<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeliveryRoutePlanner
{
    public function planForDriver(int $driverId): array
    {
        $orders = Pedido::query()
            ->with('sucursal:id,nombre,latitud,longitud')
            ->where('delivery_user_id', $driverId)
            ->where('estado', Pedido::EN_CAMINO)
            ->orderBy('id')
            ->get();

        return $this->plan($orders);
    }

    public function plan(Collection $orders): array
    {
        $orders = $orders->values();
        $stops = [];
        $missing = [];

        foreach ($orders as $order) {
            $point = $this->destination($order);
            if (! $point) {
                $missing[] = $order->id;
                continue;
            }

            $stops[] = [
                'order_id' => $order->id,
                'latitude' => $point['latitude'],
                'longitude' => $point['longitude'],
                'address' => collect($order->delivery_address_json ?? [])->only([
                    'nombre', 'direccion_exacta', 'provincia', 'canton', 'distrito', 'referencias', 'telefono_contacto',
                ])->all(),
            ];
        }

        $origin = $this->origin($orders);
        $base = [
            'generated_at' => now()->toIso8601String(),
            'origin' => $origin,
            'order_count' => count($stops),
            'missing_coordinates' => $missing,
            'stops' => [],
            'geometry' => null,
            'distance_meters' => 0,
            'drive_seconds' => 0,
            'service_seconds' => 0,
            'total_seconds' => 0,
            'provider' => null,
            'approximate' => true,
            'traffic_aware' => false,
        ];

        if (! $origin || $stops === []) {
            return $base;
        }

        $signature = sha1(json_encode([
            $origin,
            array_map(fn ($stop) => [$stop['order_id'], $stop['latitude'], $stop['longitude']], $stops),
            (int) config('services.routing.stop_service_minutes', 4),
        ], JSON_PRESERVE_ZERO_FRACTION));

        return Cache::remember('delivery-route:'.$signature, now()->addSeconds(8), function () use ($base, $origin, $stops) {
            try {
                return $this->roadPlan($base, $origin, $stops);
            } catch (Throwable) {
                return $this->fallbackPlan($base, $origin, $stops);
            }
        });
    }

    private function roadPlan(array $base, array $origin, array $stops): array
    {
        $coordinates = array_merge([$origin], array_map(fn ($stop) => [
            'latitude' => $stop['latitude'],
            'longitude' => $stop['longitude'],
        ], $stops));
        $coordinateString = $this->coordinateString($coordinates);
        $baseUrl = rtrim((string) config('services.routing.base_url'), '/');
        $profile = (string) config('services.routing.profile', 'driving');
        $timeout = max(2, (int) config('services.routing.timeout', 6));

        if ($baseUrl === '') {
            throw new \RuntimeException('Routing provider is not configured.');
        }

        $matrixResponse = Http::connectTimeout(2)->timeout($timeout)->acceptJson()->get(
            $baseUrl.'/table/v1/'.$profile.'/'.$coordinateString,
            ['annotations' => 'duration,distance']
        );
        if (! $matrixResponse->successful() || $matrixResponse->json('code') !== 'Ok') {
            throw new \RuntimeException('Routing matrix failed.');
        }

        $durations = $matrixResponse->json('durations');
        $distances = $matrixResponse->json('distances');
        if (! is_array($durations) || count($durations) !== count($coordinates)) {
            throw new \RuntimeException('Routing matrix is incomplete.');
        }

        $sequence = $this->optimalSequence($durations, count($stops));
        if (count($sequence) !== count($stops)) {
            throw new \RuntimeException('Could not build an optimized route.');
        }

        $orderedStops = array_map(fn ($index) => $stops[$index], $sequence);
        $orderedCoordinates = array_merge([$origin], array_map(fn ($stop) => [
            'latitude' => $stop['latitude'],
            'longitude' => $stop['longitude'],
        ], $orderedStops));

        $routeResponse = Http::connectTimeout(2)->timeout($timeout)->acceptJson()->get(
            $baseUrl.'/route/v1/'.$profile.'/'.$this->coordinateString($orderedCoordinates),
            ['overview' => 'full', 'geometries' => 'geojson', 'steps' => 'false']
        );
        $route = $routeResponse->successful() && $routeResponse->json('code') === 'Ok'
            ? $routeResponse->json('routes.0')
            : null;
        $legs = is_array($route['legs'] ?? null) ? $route['legs'] : [];

        $legDurations = [];
        $legDistances = [];
        $previousMatrixIndex = 0;
        foreach ($sequence as $position => $stopIndex) {
            $matrixIndex = $stopIndex + 1;
            $legDurations[] = (float) ($legs[$position]['duration'] ?? $durations[$previousMatrixIndex][$matrixIndex] ?? 0);
            $legDistances[] = (float) ($legs[$position]['distance'] ?? $distances[$previousMatrixIndex][$matrixIndex] ?? 0);
            $previousMatrixIndex = $matrixIndex;
        }

        return $this->decoratePlan(
            $base,
            $orderedStops,
            $legDurations,
            $legDistances,
            is_array($route['geometry'] ?? null) ? $route['geometry'] : $this->lineGeometry($orderedCoordinates),
            'osrm',
            false
        );
    }

    private function fallbackPlan(array $base, array $origin, array $stops): array
    {
        $remaining = array_keys($stops);
        $sequence = [];
        $current = $origin;
        while ($remaining !== []) {
            $bestKey = null;
            $bestDistance = INF;
            foreach ($remaining as $key => $index) {
                $distance = $this->haversineMeters($current, $stops[$index]);
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestKey = $key;
                }
            }
            $index = $remaining[$bestKey];
            $sequence[] = $index;
            $current = $stops[$index];
            unset($remaining[$bestKey]);
        }

        $orderedStops = array_map(fn ($index) => $stops[$index], $sequence);
        $speedMetersPerSecond = max(5, (float) config('services.routing.fallback_speed_kmh', 30) / 3.6);
        $legDurations = [];
        $legDistances = [];
        $previous = $origin;
        foreach ($orderedStops as $stop) {
            $distance = $this->haversineMeters($previous, $stop);
            $legDistances[] = $distance;
            $legDurations[] = $distance / $speedMetersPerSecond;
            $previous = $stop;
        }

        $coordinates = array_merge([$origin], array_map(fn ($stop) => [
            'latitude' => $stop['latitude'],
            'longitude' => $stop['longitude'],
        ], $orderedStops));

        return $this->decoratePlan(
            $base,
            $orderedStops,
            $legDurations,
            $legDistances,
            $this->lineGeometry($coordinates),
            'fallback',
            true
        );
    }

    private function decoratePlan(array $base, array $orderedStops, array $legDurations, array $legDistances, array $geometry, string $provider, bool $approximate): array
    {
        $serviceSeconds = max(0, (int) config('services.routing.stop_service_minutes', 4)) * 60;
        $elapsed = 0.0;
        $drive = 0.0;
        $distance = 0.0;
        $generated = now();
        $decorated = [];

        foreach ($orderedStops as $index => $stop) {
            $legDuration = max(0, (float) ($legDurations[$index] ?? 0));
            $legDistance = max(0, (float) ($legDistances[$index] ?? 0));
            $drive += $legDuration;
            $distance += $legDistance;
            $elapsed += $legDuration;
            $arrival = $generated->copy()->addSeconds((int) round($elapsed));
            $decorated[] = array_merge($stop, [
                'sequence' => $index + 1,
                'leg_seconds' => (int) round($legDuration),
                'leg_distance_meters' => (int) round($legDistance),
                'eta_seconds' => (int) round($elapsed),
                'eta_at' => $arrival->toIso8601String(),
                'service_seconds' => $serviceSeconds,
            ]);
            $elapsed += $serviceSeconds;
        }

        return array_merge($base, [
            'generated_at' => $generated->toIso8601String(),
            'stops' => $decorated,
            'geometry' => $geometry,
            'distance_meters' => (int) round($distance),
            'drive_seconds' => (int) round($drive),
            'service_seconds' => $serviceSeconds * count($decorated),
            'total_seconds' => (int) round($elapsed),
            'provider' => $provider,
            'approximate' => $approximate,
            'traffic_aware' => false,
        ]);
    }

    private function optimalSequence(array $matrix, int $stopCount): array
    {
        if ($stopCount <= 0) {
            return [];
        }
        if ($stopCount > 12) {
            return $this->greedySequence($matrix, $stopCount);
        }

        $dp = [];
        $previous = [];
        for ($j = 0; $j < $stopCount; $j++) {
            $cost = $this->matrixCost($matrix, 0, $j + 1);
            $mask = 1 << $j;
            $dp[$mask][$j] = $cost;
            $previous[$mask][$j] = null;
        }

        $fullMask = (1 << $stopCount) - 1;
        for ($mask = 1; $mask <= $fullMask; $mask++) {
            for ($j = 0; $j < $stopCount; $j++) {
                if (($mask & (1 << $j)) === 0 || ! isset($dp[$mask][$j])) {
                    continue;
                }
                for ($next = 0; $next < $stopCount; $next++) {
                    if (($mask & (1 << $next)) !== 0) {
                        continue;
                    }
                    $newMask = $mask | (1 << $next);
                    $candidate = $dp[$mask][$j] + $this->matrixCost($matrix, $j + 1, $next + 1);
                    if (! isset($dp[$newMask][$next]) || $candidate < $dp[$newMask][$next]) {
                        $dp[$newMask][$next] = $candidate;
                        $previous[$newMask][$next] = $j;
                    }
                }
            }
        }

        if (! isset($dp[$fullMask])) {
            return [];
        }
        $last = array_key_first($dp[$fullMask]);
        foreach ($dp[$fullMask] as $index => $cost) {
            if ($cost < $dp[$fullMask][$last]) {
                $last = $index;
            }
        }

        $sequence = [];
        $mask = $fullMask;
        while ($last !== null) {
            array_unshift($sequence, $last);
            $prior = $previous[$mask][$last] ?? null;
            $mask ^= 1 << $last;
            $last = $prior;
        }

        return $sequence;
    }

    private function greedySequence(array $matrix, int $stopCount): array
    {
        $remaining = range(0, $stopCount - 1);
        $sequence = [];
        $current = 0;
        while ($remaining !== []) {
            $bestPosition = 0;
            $bestCost = INF;
            foreach ($remaining as $position => $stopIndex) {
                $cost = $this->matrixCost($matrix, $current, $stopIndex + 1);
                if ($cost < $bestCost) {
                    $bestCost = $cost;
                    $bestPosition = $position;
                }
            }
            $selected = $remaining[$bestPosition];
            $sequence[] = $selected;
            $current = $selected + 1;
            array_splice($remaining, $bestPosition, 1);
        }

        return $sequence;
    }

    private function matrixCost(array $matrix, int $from, int $to): float
    {
        $value = $matrix[$from][$to] ?? null;
        return is_numeric($value) ? (float) $value : 1.0e12;
    }

    private function origin(Collection $orders): ?array
    {
        $withGps = $orders->filter(fn (Pedido $order) => $order->liveLocation() !== null)
            ->sortByDesc(fn (Pedido $order) => $order->delivery_recorded_at?->timestamp ?? 0)
            ->first();
        if ($withGps) {
            $location = $withGps->liveLocation();
            return [
                'latitude' => (float) $location['latitude'],
                'longitude' => (float) $location['longitude'],
                'source' => 'gps',
                'recorded_at' => $location['recorded_at'],
            ];
        }

        $branch = $orders->pluck('sucursal')->filter()->first(fn ($branch) => $this->validCoordinate($branch->latitud, $branch->longitud));
        if ($branch) {
            return [
                'latitude' => (float) $branch->latitud,
                'longitude' => (float) $branch->longitud,
                'source' => 'branch',
                'recorded_at' => null,
            ];
        }

        return null;
    }

    private function destination(Pedido $order): ?array
    {
        $address = $order->delivery_address_json ?? [];
        if (! $this->validCoordinate($address['latitud'] ?? null, $address['longitud'] ?? null)) {
            return null;
        }

        return ['latitude' => (float) $address['latitud'], 'longitude' => (float) $address['longitud']];
    }

    private function validCoordinate(mixed $latitude, mixed $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && (float) $latitude >= -90 && (float) $latitude <= 90
            && (float) $longitude >= -180 && (float) $longitude <= 180;
    }

    private function coordinateString(array $points): string
    {
        return implode(';', array_map(fn ($point) => sprintf('%.6F,%.6F', $point['longitude'], $point['latitude']), $points));
    }

    private function lineGeometry(array $points): array
    {
        return [
            'type' => 'LineString',
            'coordinates' => array_map(fn ($point) => [(float) $point['longitude'], (float) $point['latitude']], $points),
        ];
    }

    private function haversineMeters(array $a, array $b): float
    {
        $radius = 6371000;
        $lat1 = deg2rad((float) $a['latitude']);
        $lat2 = deg2rad((float) $b['latitude']);
        $dLat = $lat2 - $lat1;
        $dLon = deg2rad((float) $b['longitude'] - (float) $a['longitude']);
        $h = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

        return $radius * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }
}
