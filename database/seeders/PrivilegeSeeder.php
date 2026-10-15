<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\{Privilege, Role, User};

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Role::all() as $role) {
            $user = User::where('role_id', $role->id)->first();
            Privilege::updateOrCreate(['role_id' => $role->id, 'user_id' => $user->id], [
                'save' => $role->role === 'Administrador' ? 'S' : 'N',
                'update' => $role->role === 'Administrador' ? 'S' : 'N',
                'delete' => $role->role === 'Administrador' ? 'S' : 'N',
                'export' => $role->role === 'Administrador' ? 'S' : 'N',
            ]);
        }
    }
}
