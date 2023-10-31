<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Student
        Role::create(['name' => 'Student']);  // UserObserber requires this role to be the default role!
        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
        ]);
        
        // Teacher
        $role = Role::create(['name' => 'Teacher']);
        $user = User::factory()->create([
            'name' => 'Teacher',
            'email' => 'teacher@gmail.com',
        ]);
        $user->assignRole($role);

        // Admin
        Role::create(['name' => 'Admin']);
        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);
        $user->assignRole($role);
    }
}
