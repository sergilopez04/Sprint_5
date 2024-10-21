<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create((['name' => 'manage players']));
        Permission::create((['name' => 'play games']));
        
        // Role::create(['name' => 'Super Admin']);
        // $admin = Role::create(['name' => 'admin']);
        // $player = Role::create(['name' => 'player']);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('manage players');

        $player = Role::create(['name' => 'player']);
        $player->givePermissionTo('play games');

        $user = User::find(1);
        $user->assignRole('admin');
        // $admin->givePermissionTo([
        //     'edit-player',
        //     'view-game',
        //     'create-game',
        //     'view-ranking',
        //     'view-worst',
        //     'view-best',
        //     'delete-games',
        //     'view-player'
        // ]);

        // $player->givePermissionTo([
        //     'edit-player',
        //     'view-game',
        //     'create-game',
        //     'view-ranking',
        //     'view-worst',
        //     'view-best'
        // ]);
    }
    }
