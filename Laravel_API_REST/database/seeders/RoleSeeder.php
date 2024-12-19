<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Super Admin']);
        $admin = Role::create(['name' => 'admin']);
        $player = Role::create(['name' => 'player']);

        $admin->givePermissionTo([
            'edit-player',
            'view-game',
            'create-game',
            'view-ranking',
            'view-worst',
            'view-best',
            'delete-games',
            'view-player'
        ]);

        $player->givePermissionTo([
            'edit-player',
            'view-game',
            'create-game',
            'view-ranking',
            'view-worst',
            'view-best'
        ]);
    }
}
