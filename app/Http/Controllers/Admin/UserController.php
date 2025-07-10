<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->orderBy('created_at', 'desc')->paginate(20);
        $pendingRegistrations = UserRegistration::where('status', 'pending')->count();
        
        return view('admin.users.index', compact('users', 'pendingRegistrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::active()->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザーを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::active()->get();
        $user->load('role');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザー情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', '自分自身のアカウントは削除できません。');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ユーザーを削除しました。');
    }

    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'パスワードを変更しました。');
    }

    public function registrations()
    {
        $registrations = UserRegistration::with('approver')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.users.registrations', compact('registrations'));
    }

    public function approveRegistration(UserRegistration $registration)
    {
        if ($registration->status !== 'pending') {
            return redirect()->route('admin.users.registrations')
                ->with('error', 'この申請は既に処理されています。');
        }

        // 役割名から役割IDを取得
        $role = Role::where('display_name', $registration->role)->first();
        if (!$role) {
            return redirect()->route('admin.users.registrations')
                ->with('error', '指定された役割が見つかりません。');
        }

        // ユーザーを作成
        User::create([
            'name' => $registration->name,
            'email' => $registration->email,
            'password' => $registration->password,
            'role_id' => $role->id,
        ]);

        // 申請を承認済みに更新
        $registration->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.users.registrations')
            ->with('success', '登録申請を承認しました。');
    }

    public function rejectRegistration(UserRegistration $registration)
    {
        if ($registration->status !== 'pending') {
            return redirect()->route('admin.users.registrations')
                ->with('error', 'この申請は既に処理されています。');
        }

        $registration->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.users.registrations')
            ->with('success', '登録申請を却下しました。');
    }
}
