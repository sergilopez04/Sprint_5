<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        Permission::create(['name' => 'manage players']);
        Permission::create(['name' => 'play games']);

        // Crear roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('manage players');

        $player = Role::create(['name' => 'player']);
        $player->givePermissionTo('play games');

        // Crear un usuario
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('123456789'), // Asegúrate de usar bcrypt para la contraseña
            'nickname' => 'Admin'
        ]);

        // Asignar rol al usuario
        $user->assignRole('admin');
    }
}
