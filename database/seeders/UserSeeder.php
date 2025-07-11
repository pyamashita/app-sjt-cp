<?php

namespace Database\Seeders;

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
        // 管理者ユーザーを作成
        $adminRole = Role::where('name', 'admin')->first();
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
        ]);

        // 競技委員ユーザーを作成
        $committeeRole = Role::where('name', 'committee')->first();
        User::create([
            'name' => '競技委員',
            'email' => 'committee@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $committeeRole->id,
        ]);

        // 補佐員ユーザーを作成
        $assistantRole = Role::where('name', 'assistant')->first();
        User::create([
            'name' => '補佐員',
            'email' => 'assistant@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role_id' => $assistantRole->id,
        ]);
    }
}
