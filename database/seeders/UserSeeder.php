<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::pluck('id', 'role');
        foreach ([
            ['username' => 'admin', 'name' => 'Administrador Condominio', 'email' => 'admin@condominio.test', 'role' => 'Administrador'],
            ['username' => 'residente', 'name' => 'Residente Demo', 'email' => 'residente@condominio.test', 'role' => 'Residente'],
            ['username' => 'guardia', 'name' => 'Guardia Demo', 'email' => 'guardia@condominio.test', 'role' => 'Guardia'],
        ] as $data) {
            User::updateOrCreate(['username' => $data['username']], [
                'password' => Hash::make('Hola123'), 'name' => $data['name'], 'gender' => 'No especificado',
                'birth' => '2000-01-01', 'email' => $data['email'], 'role_id' => $roles[$data['role']], 'status' => 'active',
            ]);
        }
    }
}
