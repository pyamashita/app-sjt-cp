<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 基本権限
            [
                'name' => 'admin_access',
                'display_name' => '管理画面ログイン',
                'description' => '/sjt-cp-admin 以下のページにアクセスできるかどうか',
                'category' => '',
                'sort_order' => 1,
            ],

            // データ管理権限
            [
                'name' => 'competition_management',
                'display_name' => '大会管理',
                'description' => '大会の作成、編集、削除権限',
                'category' => 'management',
                'sort_order' => 10,
            ],
            [
                'name' => 'player_management',
                'display_name' => '選手情報管理',
                'description' => '選手情報の管理権限',
                'category' => 'management',
                'sort_order' => 20,
            ],
            [
                'name' => 'device_management',
                'display_name' => '端末管理',
                'description' => '端末の管理権限',
                'category' => 'management',
                'sort_order' => 30,
            ],
            [
                'name' => 'resource_management',
                'display_name' => 'リソース管理',
                'description' => 'リソースファイルの管理権限',
                'category' => 'management',
                'sort_order' => 40,
            ],

            // 運営操作権限
            [
                'name' => 'guide_management',
                'display_name' => 'ガイドページ管理',
                'description' => 'ガイドページの管理権限',
                'category' => 'operation',
                'sort_order' => 50,
            ],
            [
                'name' => 'message_management',
                'display_name' => 'メッセージ管理',
                'description' => 'メッセージ送信の管理権限',
                'category' => 'operation',
                'sort_order' => 60,
            ],
            [
                'name' => 'collection_management',
                'display_name' => 'コレクション管理',
                'description' => 'コレクションの管理権限',
                'category' => 'operation',
                'sort_order' => 70,
            ],

            // システム管理権限
            [
                'name' => 'api_management',
                'display_name' => 'API管理',
                'description' => 'APIトークンの管理権限',
                'category' => 'system',
                'sort_order' => 80,
            ],
            [
                'name' => 'system_management',
                'display_name' => 'システム管理',
                'description' => 'システム設定、権限管理等の権限',
                'category' => 'system',
                'sort_order' => 90,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}