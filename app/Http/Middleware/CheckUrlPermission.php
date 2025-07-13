<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUrlPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // 未認証の場合はログインページにリダイレクト
        if (!$user) {
            return redirect()->route('login');
        }
        
        // 現在のURL（ドメインを除く）を取得
        $currentUrl = $request->getPathInfo();
        
        // デバッグ用ログ（ローカル環境のみ）
        if (app()->environment('local')) {
            \Log::info('URL Permission Check', [
                'user_id' => $user->id,
                'user_role' => $user->role->name ?? 'no_role',
                'current_url' => $currentUrl,
                'method' => $request->method()
            ]);
        }
        
        // URL権限をチェック
        if (!$user->canAccessUrl($currentUrl)) {
            // 権限がない場合はエラーメッセージとともにリダイレクト
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'このページにアクセスする権限がありません。'
                ], 403);
            }
            
            // ダッシュボードページの場合は403エラーページを表示
            if (str_starts_with($currentUrl, '/dashboard')) {
                return response()->view('errors.403', [
                    'message' => 'このページにアクセスする権限がありません。'
                ], 403);
            }
            
            // 管理画面の場合は管理画面トップにリダイレクト
            if (str_starts_with($currentUrl, '/sjt-cp-admin')) {
                return redirect()->route('admin.home')
                    ->with('error', 'このページにアクセスする権限がありません。');
            }
            
            // その他の場合はホームページにリダイレクト
            return redirect()->route('home')
                ->with('error', 'このページにアクセスする権限がありません。');
        }
        
        return $next($request);
    }
}