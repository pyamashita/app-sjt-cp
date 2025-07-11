<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseUser;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DatabaseUserController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $serverId = $request->get('server_id');
        $server = Server::findOrFail($serverId);
        
        if (!$server->isDatabaseServer()) {
            abort(403, 'このサーバはデータベースサーバではありません。');
        }
        
        return view('admin.database-users.create', compact('server'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'allowed_hosts' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'server_id.required' => 'サーバIDが必要です。',
            'server_id.exists' => '指定されたサーバが存在しません。',
            'username.required' => 'ユーザ名を入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'allowed_hosts.required' => '接続許可ホストを入力してください。',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        
        if (!$server->isDatabaseServer()) {
            return back()->withErrors(['server_id' => 'このサーバはデータベースサーバではありません。']);
        }

        $validated['is_active'] = $request->has('is_active');

        DatabaseUser::create($validated);

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'DBユーザーが正常に追加されました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DatabaseUser $databaseUser): View
    {
        $server = $databaseUser->server;
        return view('admin.database-users.edit', compact('databaseUser', 'server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DatabaseUser $databaseUser): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'allowed_hosts' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'username.required' => 'ユーザ名を入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'allowed_hosts.required' => '接続許可ホストを入力してください。',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $databaseUser->update($validated);

        return redirect()
            ->route('admin.servers.show', $databaseUser->server)
            ->with('success', 'DBユーザー情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DatabaseUser $databaseUser): RedirectResponse
    {
        $server = $databaseUser->server;
        $databaseUser->delete();

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'DBユーザーが正常に削除されました。');
    }
}
