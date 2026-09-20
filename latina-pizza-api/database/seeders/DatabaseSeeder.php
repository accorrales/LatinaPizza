<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            PromocionSeeder::class,
        ]);

        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');

        if ($email && $password) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('SEED_ADMIN_NAME', 'Administrador'),
                    'password' => Hash::make($password),
                    'role' => 'admin',
                    'sucursal_id' => Sucursal::orderBy('id')->value('id'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
