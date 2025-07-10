<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:user_registrations'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:競技委員,補佐員'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        UserRegistration::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('login')
            ->with('success', '登録申請を受け付けました。管理者の承認をお待ちください。');
    }
}
