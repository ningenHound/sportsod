<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;


class UsersAndRolesSeeder extends Seeder
{

    public function run() {
        
        $role1 = Role::create(['description' => 'SYSTEM_ADMIN']);
        $role2 = Role::create(['description' => 'ADMIN']);
        $role3 = Role::create(['description' => 'PLAYER']);

        $user1 = User::create(['name' => 'Juan Perez', 'email'=>'juanperez@sportsod.com', 'password'=> Hash::make('123'), 'role_id'=> $role1->id, 'enabled'=>true]);
        $user2 = User::create(['name' => 'John Doe', 'email'=>'johndoe@sportsod.com', 'password'=> Hash::make('111'), 'role_id'=> $role2->id, 'enabled'=>true]);
        $user3 = User::create(['name' => 'Mark Twain', 'email'=>'marktwain@sportsod.com', 'password'=> Hash::make('222'), 'role_id'=> $role3->id, 'enabled'=>true]);
        
    }
}