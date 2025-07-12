<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalConnection;
use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExternalConnectionController extends Controller
{
    /**
     * 外部接続設定一覧
     */
    public function index()
    {
        $connections = ExternalConnection::with('updater')
            ->orderBy('service_type')
            ->get();

        return view('admin.external-connections.index', compact('connections'));
    }

    /**
     * 設定編集フォーム
     */
    public function edit(ExternalConnection $externalConnection)
    {
        return view('admin.external-connections.edit', [
            'connection' => $externalConnection
        ]);
    }

    /**
     * 設定更新
     */
    public function update(Request $request, ExternalConnection $externalConnection)
    {
        // 基本バリデーション（設定値のみ）
        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'config' => 'required|array',
        ]);

        // WebSocket設定の詳細バリデーション
        if ($externalConnection->service_type === ExternalConnection::SERVICE_WEBSOCKET_MESSAGE) {
            $request->validate([
                'config.use_localhost' => 'required|boolean',
                'config.server_address' => 'nullable|string|max:255',
                'config.default_port' => 'required|integer|min:1|max:65535',
                'config.timeout' => 'required|integer|min:1|max:300',
                'config.retry_count' => 'required|integer|min:0|max:10',
                'config.retry_delay' => 'required|integer|min:0|max:60000',
                'config.protocol' => 'required|in:ws,wss',
                'config.path' => 'required|string|max:255',
            ], [
                'config.default_port.required' => 'デフォルトポートを入力してください',
                'config.default_port.integer' => 'デフォルトポートは数値で入力してください',
                'config.default_port.min' => 'デフォルトポートは1以上で入力してください',
                'config.default_port.max' => 'デフォルトポートは65535以下で入力してください',
                'config.timeout.required' => 'タイムアウトを入力してください',
                'config.timeout.integer' => 'タイムアウトは数値で入力してください',
                'config.timeout.min' => 'タイムアウトは1秒以上で入力してください',
                'config.timeout.max' => 'タイムアウトは300秒以下で入力してください',
                'config.retry_count.required' => 'リトライ回数を入力してください',
                'config.retry_count.integer' => 'リトライ回数は数値で入力してください',
                'config.retry_delay.required' => 'リトライ間隔を入力してください',
                'config.retry_delay.integer' => 'リトライ間隔は数値で入力してください',
                'config.protocol.required' => 'プロトコルを選択してください',
                'config.path.required' => 'パスを入力してください',
            ]);
        }

        // 設定値と有効状態のみを更新（name、description、service_typeは固定）
        $externalConnection->update([
            'is_active' => $validated['is_active'],
            'config' => $validated['config'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.external-connections.index')
            ->with('success', '外部接続設定を更新しました。');
    }

    /**
     * 接続テスト
     */
    public function test(ExternalConnection $externalConnection)
    {
        if ($externalConnection->service_type === ExternalConnection::SERVICE_WEBSOCKET_MESSAGE) {
            Log::info("外部接続設定の接続テスト開始", [
                'connection_id' => $externalConnection->id,
                'connection_name' => $externalConnection->name,
                'config' => $externalConnection->config
            ]);

            try {
                $webSocketService = new WebSocketService();
                // ダミーIPアドレスでテスト（実際にはサーバー設定のアドレスを使用）
                $connected = $webSocketService->testConnection('test');
                
                Log::info("外部接続設定の接続テスト結果", [
                    'connection_id' => $externalConnection->id,
                    'connected' => $connected
                ]);
                
                if ($connected) {
                    return response()->json([
                        'success' => true,
                        'message' => 'WebSocketサーバーへの接続テストに成功しました。'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'WebSocketサーバーへの接続テストに失敗しました。WebSocketサーバーが起動していない可能性があります。'
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error("外部接続設定の接続テストでエラー", [
                    'connection_id' => $externalConnection->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => '接続テストでエラーが発生しました: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'このサービスタイプの接続テストは実装されていません。'
        ], 400);
    }
}
