<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * 権限管理画面を表示
     */
    public function index(): View
    {
        $roles = Role::active()->with('permissions')->get();
        $permissions = Permission::active()->ordered()->get();
        
        // 権限をカテゴリごとにグループ化
        $permissionsByCategory = $permissions->groupBy('category');
        
        // ロールごとの権限マトリックスを作成
        $permissionMatrix = [];
        foreach ($roles as $role) {
            $permissionMatrix[$role->id] = $role->permissions->pluck('id')->toArray();
        }

        return view('admin.permissions.index', compact(
            'roles',
            'permissions',
            'permissionsByCategory',
            'permissionMatrix'
        ));
    }

    /**
     * 権限設定を更新
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'exists:permissions,id',
        ]);

        $permissions = $request->input('permissions', []);
        
        foreach ($permissions as $roleId => $permissionIds) {
            $role = Role::findOrFail($roleId);
            $role->syncPermissions($permissionIds);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', '権限設定を更新しました。');
    }

    /**
     * 権限一覧をJSON形式で取得（API用）
     */
    public function getPermissions(): \Illuminate\Http\JsonResponse
    {
        $permissions = Permission::active()->ordered()->get();
        $roles = Role::active()->with('permissions')->get();
        
        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role->id] = $role->permissions->pluck('id')->toArray();
        }

        return response()->json([
            'permissions' => $permissions,
            'roles' => $roles,
            'matrix' => $matrix
        ]);
    }

    /**
     * 特定のロールの権限をリセット
     */
    public function resetRole(Role $role): RedirectResponse
    {
        $role->permissions()->detach();
        
        return redirect()
            ->route('admin.permissions.index')
            ->with('success', "{$role->display_name}の権限をリセットしました。");
    }

    /**
     * デフォルト権限を設定
     */
    public function setDefaults(): RedirectResponse
    {
        $adminRole = Role::findByName('admin');
        $committeeRole = Role::findByName('committee');
        $assistantRole = Role::findByName('assistant');

        if ($adminRole) {
            $allPermissions = Permission::active()->pluck('id')->toArray();
            $adminRole->syncPermissions($allPermissions);
        }

        if ($committeeRole) {
            $committeePermissions = Permission::active()
                ->whereIn('name', [
                    'admin_access',
                    'competition_management',
                    'player_management',
                    'device_management',
                    'resource_management',
                    'guide_management',
                    'message_management'
                ])
                ->pluck('id')
                ->toArray();
            $committeeRole->syncPermissions($committeePermissions);
        }

        if ($assistantRole) {
            $assistantPermissions = Permission::active()
                ->whereIn('name', [
                    'admin_access',
                    'player_management',
                    'device_management'
                ])
                ->pluck('id')
                ->toArray();
            $assistantRole->syncPermissions($assistantPermissions);
        }

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'デフォルト権限を設定しました。');
    }
}