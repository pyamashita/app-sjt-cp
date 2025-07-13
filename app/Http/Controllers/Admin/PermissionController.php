<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

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
     * 権限の新規作成フォーム
     */
    public function create(): View
    {
        return view('admin.permissions.create');
    }

    /**
     * 権限を作成
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'url' => $request->url,
            'description' => $request->description,
            'remarks' => $request->remarks,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', '権限を作成しました。');
    }

    /**
     * 権限の編集フォーム
     */
    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * 権限を更新
     */
    public function updatePermission(Request $request, Permission $permission): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $permission->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'url' => $request->url,
            'description' => $request->description,
            'remarks' => $request->remarks,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', '権限を更新しました。');
    }

    /**
     * 権限を削除
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        // 権限が使用されているかチェック
        if ($permission->roles()->exists()) {
            return redirect()
                ->route('admin.permissions.index')
                ->with('error', 'この権限は使用中のため削除できません。');
        }

        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', '権限を削除しました。');
    }

    /**
     * デフォルト権限を設定
     */
    public function setDefaults(): JsonResponse
    {
        $adminRole = Role::findByName('admin');
        $committeeRole = Role::findByName('committee');
        $assistantRole = Role::findByName('assistant');

        // 管理者には全ての権限を付与
        if ($adminRole) {
            $allPermissions = Permission::pluck('id')->toArray();
            $adminRole->syncPermissions($allPermissions);
        }

        // 競技委員：ダッシュボードのみ、ガイド管理は可能
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

        return response()->json([
            'success' => true,
            'message' => 'デフォルト権限を設定しました。'
        ]);
    }
}