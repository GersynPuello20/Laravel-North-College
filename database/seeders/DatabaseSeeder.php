<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $superAdminRole = Role::firstWhere('slug', 'superadmin');

        if ($superAdminRole) {
            User::updateOrCreate(
                ['email' => 'admin@northcollege.test'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $superAdminRole->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
