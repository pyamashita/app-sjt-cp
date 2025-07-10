<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * ログインフォームを表示
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // デバッグログ
        \Log::info('Login attempt', [
            'email' => $credentials['email'],
            'password_length' => strlen($credentials['password']),
            'remember' => $request->boolean('remember'),
        ]);
        
        // ユーザーの存在確認
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if ($user) {
            \Log::info('User found', [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'password_hash_exists' => !empty($user->password),
                'password_check' => \Hash::check($credentials['password'], $user->password),
            ]);
        } else {
            \Log::warning('User not found', ['email' => $credentials['email']]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            \Log::info('Login successful', ['user_id' => Auth::id()]);
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        \Log::warning('Login failed', [
            'email' => $credentials['email'],
            'auth_check' => Auth::check(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'ログイン情報が正しくありません。',
        ]);
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
