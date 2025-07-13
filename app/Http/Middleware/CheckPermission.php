<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // 未認証の場合はログインページにリダイレクト
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 権限が指定されていない場合はそのまま通す
        if (empty($permissions)) {
            return $next($request);
        }

        // 単一の権限をチェック
        if (count($permissions) === 1) {
            if (!$user->hasPermission($permissions[0])) {
                return $this->handleUnauthorized($request, $permissions[0]);
            }
        } else {
            // 複数の権限のいずれかを持っているかチェック
            if (!$user->hasAnyPermission($permissions)) {
                return $this->handleUnauthorized($request, implode(', ', $permissions));
            }
        }

        return $next($request);
    }

    /**
     * 権限不足時の処理
     */
    private function handleUnauthorized(Request $request, string $requiredPermissions): Response
    {
        // 管理画面へのアクセス権限がない場合はフロントページにリダイレクト
        if (str_contains($requiredPermissions, 'admin_access')) {
            return redirect()->route('frontend.home')
                ->with('error', '管理画面へのアクセス権限がありません。');
        }

        // AJAX リクエストの場合は JSON レスポンス
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'この機能を利用する権限がありません。',
                'required_permissions' => $requiredPermissions
            ], 403);
        }

        // 通常のリクエストの場合は管理画面ホームにリダイレクト
        return redirect()->route('admin.home')
            ->with('error', "この機能を利用する権限がありません。必要な権限: {$requiredPermissions}");
    }
}