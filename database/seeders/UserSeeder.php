<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'warehouse', 'finance', 'procurement', 'konstruksi', 'commerce'];

        foreach ($roles as $role) {
            User::updateOrCreate(
                ['email' => $role . '@taskgate.id'],
                [
                    'name' => ucfirst($role) . ' User',
                    'password' => bcrypt('password123'),
                    'role' => $role,
                ]
            );
        }
    }
}
