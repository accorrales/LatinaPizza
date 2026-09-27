<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\MesaSesion;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiningRoomOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_can_open_and_close_an_empty_table_in_own_branch(): void
    {
        $branch = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $waiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branch->id,
        ]);
        $table = Mesa::create([
            'sucursal_id' => $branch->id,
            'numero' => '8',
            'capacidad' => 4,
        ]);
        Sanctum::actingAs($waiter);

        $open = $this->postJson("/api/salon/mesas/{$table->id}/abrir", [
            'personas' => 3,
        ])->assertCreated()
            ->assertJsonPath('data.estado', Mesa::OCUPADA)
            ->assertJsonPath('data.session.personas', 3)
            ->assertJsonPath('data.session.mesero.id', $waiter->id);

        $sessionId = $open->json('session_id');
        $this->assertDatabaseHas('mesa_sesiones', [
            'id' => $sessionId,
            'mesa_id' => $table->id,
            'mesero_user_id' => $waiter->id,
            'estado' => MesaSesion::ABIERTA,
        ]);

        $this->postJson("/api/salon/mesas/{$table->id}/abrir", ['personas' => 2])
            ->assertConflict();

        $this->postJson("/api/salon/sesiones/{$sessionId}/cerrar")
            ->assertOk()
            ->assertJsonPath('data.estado', Mesa::DISPONIBLE);

        $this->assertDatabaseHas('mesa_sesiones', [
            'id' => $sessionId,
            'estado' => MesaSesion::CERRADA,
        ]);
    }

    public function test_database_rejects_two_open_sessions_for_same_table(): void
    {
        $branch = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $table = Mesa::create([
            'sucursal_id' => $branch->id,
            'numero' => '9',
            'capacidad' => 4,
        ]);

        MesaSesion::create([
            'mesa_id' => $table->id,
            'sucursal_id' => $branch->id,
            'personas' => 2,
        ]);

        $this->expectException(QueryException::class);

        MesaSesion::create([
            'mesa_id' => $table->id,
            'sucursal_id' => $branch->id,
            'personas' => 3,
        ]);
    }

    public function test_waiter_cannot_operate_tables_from_another_branch(): void
    {
        $branchA = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $branchB = Sucursal::create(['nombre' => 'Grecia', 'direccion' => 'Centro']);
        $waiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branchA->id,
        ]);
        $foreignTable = Mesa::create([
            'sucursal_id' => $branchB->id,
            'numero' => '1',
            'capacidad' => 4,
        ]);
        Sanctum::actingAs($waiter);

        $this->postJson("/api/salon/mesas/{$foreignTable->id}/abrir", ['personas' => 2])
            ->assertForbidden();

        $response = $this->getJson("/api/salon/mesas?sucursal_id={$branchB->id}")
            ->assertOk();

        $this->assertSame($branchA->id, $response->json('meta.sucursal_id'));
        $this->assertCount(0, $response->json('data'));
    }

    public function test_manager_can_create_tables_only_in_assigned_branch(): void
    {
        $branchA = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $branchB = Sucursal::create(['nombre' => 'Grecia', 'direccion' => 'Centro']);
        $manager = User::factory()->create([
            'role' => User::ROLE_GERENTE,
            'sucursal_id' => $branchA->id,
        ]);
        Sanctum::actingAs($manager);

        $this->postJson('/api/salon/mesas', [
            'sucursal_id' => $branchB->id,
            'numero' => '12',
            'zona' => 'Terraza',
            'capacidad' => 6,
        ])->assertCreated();

        $this->assertDatabaseHas('mesas', [
            'sucursal_id' => $branchA->id,
            'numero' => '12',
            'zona' => 'Terraza',
        ]);
        $this->assertDatabaseMissing('mesas', [
            'sucursal_id' => $branchB->id,
            'numero' => '12',
        ]);
    }

    public function test_manager_must_assign_verified_waiter_from_own_branch_when_opening_table(): void
    {
        $branchA = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $branchB = Sucursal::create(['nombre' => 'Grecia', 'direccion' => 'Centro']);
        $manager = User::factory()->create([
            'role' => User::ROLE_GERENTE,
            'sucursal_id' => $branchA->id,
        ]);
        $waiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branchA->id,
            'email_verified_at' => now(),
        ]);
        $foreignWaiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branchB->id,
            'email_verified_at' => now(),
        ]);
        $unverifiedWaiter = User::factory()->unverified()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branchA->id,
        ]);
        $table = Mesa::create([
            'sucursal_id' => $branchA->id,
            'numero' => '15',
            'capacidad' => 4,
        ]);
        Sanctum::actingAs($manager);

        $this->postJson("/api/salon/mesas/{$table->id}/abrir", ['personas' => 2])
            ->assertUnprocessable();

        $this->postJson("/api/salon/mesas/{$table->id}/abrir", [
            'personas' => 2,
            'mesero_user_id' => $foreignWaiter->id,
        ])->assertUnprocessable();

        $this->postJson("/api/salon/mesas/{$table->id}/abrir", [
            'personas' => 2,
            'mesero_user_id' => $unverifiedWaiter->id,
        ])->assertUnprocessable();

        $this->postJson("/api/salon/mesas/{$table->id}/abrir", [
            'personas' => 2,
            'mesero_user_id' => $waiter->id,
        ])->assertCreated()
            ->assertJsonPath('data.session.mesero.id', $waiter->id);
    }

    public function test_waiter_cannot_close_another_waiters_table(): void
    {
        $branch = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $owner = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branch->id,
        ]);
        $other = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branch->id,
        ]);
        $table = Mesa::create([
            'sucursal_id' => $branch->id,
            'numero' => '4',
            'capacidad' => 4,
            'estado' => Mesa::OCUPADA,
        ]);
        $session = MesaSesion::create([
            'mesa_id' => $table->id,
            'sucursal_id' => $branch->id,
            'mesero_user_id' => $owner->id,
            'personas' => 2,
        ]);
        Sanctum::actingAs($other);

        $this->postJson("/api/salon/sesiones/{$session->id}/cerrar")
            ->assertForbidden();

        $this->assertDatabaseHas('mesa_sesiones', [
            'id' => $session->id,
            'estado' => MesaSesion::ABIERTA,
        ]);
    }
}
