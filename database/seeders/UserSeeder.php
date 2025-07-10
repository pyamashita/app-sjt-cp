<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 管理者ユーザーを作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => '管理者',
        ]);

        // 競技委員ユーザーを作成
        User::create([
            'name' => '競技委員',
            'email' => 'committee@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => '競技委員',
        ]);

        // 補佐員ユーザーを作成
        User::create([
            'name' => '補佐員',
            'email' => 'assistant@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => '補佐員',
        ]);
    }
}
