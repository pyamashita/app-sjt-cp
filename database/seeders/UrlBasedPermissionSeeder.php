<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class UrlBasedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 既存の権限データを削除（外部キー制約を考慮）
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // URL ベースの権限を作成
        $permissions = [
            // 管理画面系
            [
                'name' => 'admin_access',
                'display_name' => '管理画面アクセス',
                'url' => '/sjt-cp-admin*',
                'description' => '管理画面全体へのアクセス権限',
                'remarks' => '管理画面のすべての機能にアクセス可能'
            ],
            [
                'name' => 'admin_home',
                'display_name' => '管理画面ホーム',
                'url' => '/sjt-cp-admin',
                'description' => '管理画面のホームページ',
                'remarks' => null
            ],
            [
                'name' => 'guide_management',
                'display_name' => 'ガイド管理',
                'url' => '/sjt-cp-admin/guides*',
                'description' => 'ガイドページの作成・編集・削除',
                'remarks' => 'ガイドページ、コレクション、リソースの管理'
            ],
            [
                'name' => 'system_settings',
                'display_name' => 'システム設定',
                'url' => '/sjt-cp-admin/system*',
                'description' => 'システム全般の設定管理',
                'remarks' => '権限管理、ユーザー管理、システム設定'
            ],
            [
                'name' => 'permission_management',
                'display_name' => '権限管理',
                'url' => '/sjt-cp-admin/system/permissions*',
                'description' => 'ユーザーの権限設定管理',
                'remarks' => 'ロール別の権限設定、権限の追加・編集'
            ],
            
            // フロントエンド系
            [
                'name' => 'dashboard_access',
                'display_name' => 'ダッシュボードアクセス',
                'url' => '/dashboard*',
                'description' => 'フロントエンドダッシュボードへのアクセス',
                'remarks' => 'フロントエンド機能全般へのアクセス'
            ],
            [
                'name' => 'dashboard_home',
                'display_name' => 'ダッシュボードホーム',
                'url' => '/dashboard',
                'description' => 'ダッシュボードのホームページ',
                'remarks' => null
            ],
            [
                'name' => 'dashboard_welcome',
                'display_name' => 'ダッシュボードWelcome',
                'url' => '/dashboard/welcome',
                'description' => 'ダッシュボードのWelcomeページ',
                'remarks' => null
            ],
            
            // 認証系
            [
                'name' => 'login_access',
                'display_name' => 'ログイン',
                'url' => '/login',
                'description' => 'ログインページへのアクセス',
                'remarks' => '認証が必要なページ'
            ],
            [
                'name' => 'logout_access',
                'display_name' => 'ログアウト',
                'url' => '/logout',
                'description' => 'ログアウト機能の利用',
                'remarks' => null
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // ロールに権限を割り当て
        $this->assignPermissionsToRoles();
    }

    /**
     * ロールに権限を割り当て
     */
    private function assignPermissionsToRoles(): void
    {
        // 管理者：全権限
        $adminRole = Role::findByName('admin');
        if ($adminRole) {
            $allPermissions = Permission::all()->pluck('id')->toArray();
            $adminRole->syncPermissions($allPermissions);
        }

        // 競技委員：ダッシュボードのみ、ガイド管理は可能
        $committeeRole = Role::findByName('committee');
        if ($committeeRole) {
            $committeePermissions = Permission::whereIn('name', [
                'dashboard_access',
                'dashboard_home',
                'dashboard_welcome',
                'guide_management',
                'login_access',
                'logout_access'
            ])->pluck('id')->toArray();
            $committeeRole->syncPermissions($committeePermissions);
        }

        // 補佐員：ダッシュボードのみ
        $assistantRole = Role::findByName('assistant');
        if ($assistantRole) {
            $assistantPermissions = Permission::whereIn('name', [
                'dashboard_access',
                'dashboard_home',
                'dashboard_welcome',
                'login_access',
                'logout_access'
            ])->pluck('id')->toArray();
            $assistantRole->syncPermissions($assistantPermissions);
        }
    }
}