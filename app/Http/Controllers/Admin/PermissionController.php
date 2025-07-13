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
        $categories = Permission::getCategories();
        return view('admin.permissions.create', compact('categories'));
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
            'category' => 'required|string|in:' . implode(',', array_keys(Permission::getCategories())),
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'url' => $request->url,
            'category' => $request->category,
            'sort_order' => $request->input('sort_order', 0),
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
        $categories = Permission::getCategories();
        return view('admin.permissions.edit', compact('permission', 'categories'));
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
            'category' => 'required|string|in:' . implode(',', array_keys(Permission::getCategories())),
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $permission->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'url' => $request->url,
            'category' => $request->category,
            'sort_order' => $request->input('sort_order', 0),
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
                ->with('error', 'この権限は使用中のため削除できません。先にロールから権限を削除してください。');
        }

        $permissionName = $permission->display_name;
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', "権限「{$permissionName}」を削除しました。");
    }

    /**
     * デフォルト権限を設定
     */
    public function setDefaults(): JsonResponse
    {
        $adminRole = Role::findByName('admin');
        $committeeRole = Role::findByName('committee');
        $assistantRole = Role::findByName('assistant');

        $assignedPermissions = [];

        // 管理者には全ての権限を付与
        if ($adminRole) {
            $allPermissions = Permission::active()->pluck('id')->toArray();
            $adminRole->syncPermissions($allPermissions);
            $assignedPermissions['管理者'] = count($allPermissions);
        }

        // 競技委員：ダッシュボード + ガイド管理 + 認証
        if ($committeeRole) {
            $committeePermissions = Permission::active()
                ->where(function($query) {
                    $query->where('url', 'like', '/dashboard%')
                          ->orWhere('url', 'like', '/sjt-cp-admin/guides%')
                          ->orWhereIn('name', ['login_access', 'logout_access']);
                })
                ->pluck('id')->toArray();
            $committeeRole->syncPermissions($committeePermissions);
            $assignedPermissions['競技委員'] = count($committeePermissions);
        }

        // 補佐員：ダッシュボードのみ + 認証
        if ($assistantRole) {
            $assistantPermissions = Permission::active()
                ->where(function($query) {
                    $query->where('url', 'like', '/dashboard%')
                          ->orWhereIn('name', ['login_access', 'logout_access']);
                })
                ->pluck('id')->toArray();
            $assistantRole->syncPermissions($assistantPermissions);
            $assignedPermissions['補佐員'] = count($assistantPermissions);
        }

        // 結果の詳細を作成
        $details = [];
        foreach ($assignedPermissions as $roleName => $count) {
            $details[] = "{$roleName}: {$count}個の権限";
        }

        return response()->json([
            'success' => true,
            'message' => 'デフォルト権限を設定しました。',
            'details' => $details
        ]);
    }

    /**
     * 特定のロールのデフォルト権限を設定
     */
    public function setRoleDefaults(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'preset' => 'required|in:admin,committee,assistant,custom',
            'permissions' => 'array|exists:permissions,id'
        ]);

        $preset = $request->input('preset');
        
        switch ($preset) {
            case 'admin':
                // 管理者：全権限
                $permissions = Permission::active()->pluck('id')->toArray();
                break;
                
            case 'committee':
                // 競技委員：ダッシュボード + ガイド管理
                $permissions = Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhere('url', 'like', '/sjt-cp-admin/guides%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })
                    ->pluck('id')->toArray();
                break;
                
            case 'assistant':
                // 補佐員：ダッシュボードのみ
                $permissions = Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })
                    ->pluck('id')->toArray();
                break;
                
            case 'custom':
                // カスタム：指定された権限
                $permissions = $request->input('permissions', []);
                break;
                
            default:
                $permissions = [];
        }

        $role->syncPermissions($permissions);

        return response()->json([
            'success' => true,
            'message' => "{$role->display_name}のデフォルト権限を設定しました（{$preset}プリセット、{count($permissions)}個の権限）。"
        ]);
    }

    /**
     * 権限のプリセット一覧を取得
     */
    public function getPresets(): JsonResponse
    {
        $presets = [
            'admin' => [
                'name' => '管理者',
                'description' => '全ての機能にアクセス可能',
                'permissions_count' => Permission::active()->count(),
                'permissions' => Permission::active()->get(['id', 'display_name', 'url'])
            ],
            'committee' => [
                'name' => '競技委員',
                'description' => 'ダッシュボードとガイド管理にアクセス可能',
                'permissions_count' => Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhere('url', 'like', '/sjt-cp-admin/guides%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })->count(),
                'permissions' => Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhere('url', 'like', '/sjt-cp-admin/guides%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })->get(['id', 'display_name', 'url'])
            ],
            'assistant' => [
                'name' => '補佐員',
                'description' => 'ダッシュボードのみアクセス可能',
                'permissions_count' => Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })->count(),
                'permissions' => Permission::active()
                    ->where(function($query) {
                        $query->where('url', 'like', '/dashboard%')
                              ->orWhereIn('name', ['login_access', 'logout_access']);
                    })->get(['id', 'display_name', 'url'])
            ]
        ];

        return response()->json($presets);
    }

    /**
     * 利用可能なルート一覧を取得（URL入力補完用）
     */
    public function getRoutes(): JsonResponse
    {
        $routes = collect(\Route::getRoutes())->map(function ($route) {
            return [
                'uri' => '/' . $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        })
        ->filter(function ($route) {
            // APIルートや不要なルートを除外
            return !str_starts_with($route['uri'], '/api/') 
                && !str_starts_with($route['uri'], '/_') 
                && !str_contains($route['uri'], '{')  // パラメータ付きルートを除外
                && !in_array($route['uri'], ['/up', '/login', '/logout', '/register']);
        })
        ->unique('uri')
        ->sortBy('uri')
        ->values();

        return response()->json($routes);
    }

    /**
     * ルートパターンの提案を取得
     */
    public function getRoutePatterns(): JsonResponse
    {
        $routes = collect(\Route::getRoutes())->map(function ($route) {
            $uri = '/' . $route->uri();
            return [
                'original' => $uri,
                'pattern' => $this->generatePatternFromRoute($uri),
                'methods' => $route->methods(),
                'name' => $route->getName(),
            ];
        })
        ->filter(function ($route) {
            return !str_starts_with($route['original'], '/api/') 
                && !str_starts_with($route['original'], '/_');
        })
        ->unique('pattern')
        ->sortBy('pattern')
        ->values();

        return response()->json($routes);
    }

    /**
     * ルートURIからパターンを生成
     */
    private function generatePatternFromRoute(string $uri): string
    {
        // パラメータを含むルートからパターンを生成
        $pattern = preg_replace('/\{[^}]+\}/', '*', $uri);
        
        // 末尾にワイルドカードを追加（サブルートをカバー）
        if (!str_ends_with($pattern, '*') && !str_ends_with($pattern, '/')) {
            $pattern .= '*';
        }
        
        return $pattern;
    }
}