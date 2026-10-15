<?php

namespace Tests\Feature;

use App\Models\{Home, Role, User, Visit};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CondominiumApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['role' => 'Administrador']);
        return User::create(['username' => 'admin', 'password' => Hash::make('Hola123'), 'name' => 'Admin', 'gender' => 'No especificado', 'birth' => '2000-01-01', 'email' => 'admin@test.dev', 'status' => 'active', 'role_id' => $role->id]);
    }

    public function test_login_and_condominium_visit_flow(): void
    {
        $admin = $this->admin();
        $token = $this->postJson('/api/login', ['username' => 'admin', 'password' => 'Hola123'])->assertOk()->json('token');
        $headers = ['Authorization' => "Bearer {$token}"];
        $home = $this->postJson('/api/homes', ['code' => 'A-01', 'address' => 'Bloque A, casa 01', 'status' => 'active'], $headers)->assertCreated()->json('data');
        $visit = $this->postJson('/api/visits', ['home_id' => $home['id'], 'visitor_name' => 'Visita Demo', 'scheduled_at' => now()->addHour()->toDateTimeString()], $headers)->assertCreated()->json('data');
        $this->postJson("/api/visits/{$visit['id']}/authorize", [], $headers)->assertOk()->assertJsonPath('data.status', 'authorized');
        $this->postJson("/api/visits/{$visit['id']}/entry", [], $headers)->assertOk()->assertJsonPath('data.status', 'entered');
        $this->postJson("/api/visits/{$visit['id']}/exit", [], $headers)->assertOk()->assertJsonPath('data.status', 'exited');
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = $this->admin(); $user->update(['status' => 'inactive']);
        $this->postJson('/api/login', ['username' => 'admin', 'password' => 'Hola123'])->assertForbidden();
    }
}
