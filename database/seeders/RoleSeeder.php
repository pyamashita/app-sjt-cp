<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => '管理者',
                'description' => 'システム全体の管理権限を持つユーザー',
                'is_active' => true,
            ],
            [
                'name' => 'committee',
                'display_name' => '競技委員',
                'description' => '競技の運営・管理を行うユーザー',
                'is_active' => true,
            ],
            [
                'name' => 'assistant',
                'display_name' => '補佐員',
                'description' => '競技運営を補佐するユーザー',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
