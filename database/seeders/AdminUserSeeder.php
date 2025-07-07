<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 管理者アカウントが存在しない場合のみ作成
        if (!User::where('email', 'admin@skilljapan.test')->exists()) {
            User::create([
                'name' => '管理者',
                'email' => 'admin@skilljapan.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]);
        }

        // 競技委員のテストアカウント
        if (!User::where('email', 'committee@skilljapan.test')->exists()) {
            User::create([
                'name' => '競技委員',
                'email' => 'committee@skilljapan.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_COMPETITION_COMMITTEE,
                'email_verified_at' => now(),
            ]);
        }

        // 補佐員のテストアカウント
        if (!User::where('email', 'assistant@skilljapan.test')->exists()) {
            User::create([
                'name' => '補佐員',
                'email' => 'assistant@skilljapan.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASSISTANT,
                'email_verified_at' => now(),
            ]);
        }
    }
}
