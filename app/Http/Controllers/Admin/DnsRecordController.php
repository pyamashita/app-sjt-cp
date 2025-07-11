<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DnsRecord;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DnsRecordController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $serverId = $request->get('server_id');
        $server = Server::findOrFail($serverId);
        
        if (!$server->isDnsServer()) {
            abort(403, 'このサーバはDNSサーバではありません。');
        }
        
        return view('admin.dns-records.create', compact('server'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:A,AAAA,CNAME,MX,TXT,PTR,SRV,NS,SOA',
            'value' => 'required|string',
            'ttl' => 'required|integer|min:1',
            'priority' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'server_id.required' => 'サーバIDが必要です。',
            'server_id.exists' => '指定されたサーバが存在しません。',
            'name.required' => 'レコード名を入力してください。',
            'type.required' => 'レコードタイプを選択してください。',
            'type.in' => '有効なレコードタイプを選択してください。',
            'value.required' => 'レコード値を入力してください。',
            'ttl.required' => 'TTLを入力してください。',
            'ttl.integer' => 'TTLは数値で入力してください。',
            'ttl.min' => 'TTLは1以上の値を入力してください。',
        ]);

        $server = Server::findOrFail($validated['server_id']);
        
        if (!$server->isDnsServer()) {
            return back()->withErrors(['server_id' => 'このサーバはDNSサーバではありません。']);
        }

        $validated['is_active'] = $request->has('is_active');

        DnsRecord::create($validated);

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'DNSレコードが正常に追加されました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DnsRecord $dnsRecord): View
    {
        $server = $dnsRecord->server;
        return view('admin.dns-records.edit', compact('dnsRecord', 'server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DnsRecord $dnsRecord): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:A,AAAA,CNAME,MX,TXT,PTR,SRV,NS,SOA',
            'value' => 'required|string',
            'ttl' => 'required|integer|min:1',
            'priority' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'レコード名を入力してください。',
            'type.required' => 'レコードタイプを選択してください。',
            'type.in' => '有効なレコードタイプを選択してください。',
            'value.required' => 'レコード値を入力してください。',
            'ttl.required' => 'TTLを入力してください。',
            'ttl.integer' => 'TTLは数値で入力してください。',
            'ttl.min' => 'TTLは1以上の値を入力してください。',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $dnsRecord->update($validated);

        return redirect()
            ->route('admin.servers.show', $dnsRecord->server)
            ->with('success', 'DNSレコード情報が正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DnsRecord $dnsRecord): RedirectResponse
    {
        $server = $dnsRecord->server;
        $dnsRecord->delete();

        return redirect()
            ->route('admin.servers.show', $server)
            ->with('success', 'DNSレコードが正常に削除されました。');
    }
}
