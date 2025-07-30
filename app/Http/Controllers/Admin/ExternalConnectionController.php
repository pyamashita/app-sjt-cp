<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalConnection;
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
        if (in_array($externalConnection->service_type, [
            ExternalConnection::SERVICE_WEBSOCKET_MESSAGE,
            ExternalConnection::SERVICE_WEBSOCKET_TIME
        ])) {
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
        if (in_array($externalConnection->service_type, [
            ExternalConnection::SERVICE_WEBSOCKET_MESSAGE,
            ExternalConnection::SERVICE_WEBSOCKET_TIME
        ])) {
            Log::info("外部接続設定の接続テスト開始", [
                'connection_id' => $externalConnection->id,
                'connection_name' => $externalConnection->name,
                'config' => $externalConnection->config
            ]);

            try {
                // 直接cURLテストを実行
                $config = $externalConnection->config;
                
                // use_localhostの値をデバッグ
                $useLocalhost = $config['use_localhost'];
                Log::info("use_localhost設定値のデバッグ", [
                    'raw_value' => $useLocalhost,
                    'type' => gettype($useLocalhost),
                    'is_truthy' => !!$useLocalhost,
                    'string_compare' => $useLocalhost === "1" || $useLocalhost === 1 || $useLocalhost === true
                ]);
                
                // 複数のアドレスを試行
                $addresses = [];
                if ($useLocalhost === "1" || $useLocalhost === 1 || $useLocalhost === true) {
                    $addresses = ['127.0.0.1', 'localhost'];
                } else {
                    $serverAddr = $config['server_address'] ?: 'localhost';
                    if ($serverAddr === 'localhost') {
                        $addresses = ['127.0.0.1', 'localhost', 'host.docker.internal'];
                    } else {
                        $addresses = [$serverAddr];
                    }
                }
                
                $port = $config['default_port'];
                
                // 複数のアドレスを順番に試行
                foreach ($addresses as $addr) {
                    // サービスタイプに応じてテスト方法を変える
                    if ($externalConnection->service_type === ExternalConnection::SERVICE_WEBSOCKET_TIME) {
                        // 時刻同期サーバーの場合：WebSocketエンドポイントをテスト
                        $wsPath = $config['path'] ?? '/time-now';
                        $testUrl = "http://{$addr}:{$port}{$wsPath}";
                        
                        Log::info("時刻同期WebSocket接続テスト試行", [
                            'service_type' => $externalConnection->service_type,
                            'address' => $addr,
                            'url' => $testUrl
                        ]);
                        
                        // WebSocketエンドポイントにHTTPリクエストを送信（WebSocketアップグレードを試行）
                        $ch = curl_init();
                        curl_setopt_array($ch, [
                            CURLOPT_URL => $testUrl,
                            CURLOPT_TIMEOUT => 2, // 短いタイムアウト
                            CURLOPT_CONNECTTIMEOUT => 2,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_HTTPHEADER => [
                                'Connection: Upgrade',
                                'Upgrade: websocket',
                                'Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==',
                                'Sec-WebSocket-Version: 13'
                            ],
                            CURLOPT_USERAGENT => 'SJT-CP WebSocket Test Client',
                        ]);

                        $directResponse = curl_exec($ch);
                        $directHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $directError = curl_error($ch);
                        curl_close($ch);
                        
                        Log::info("時刻同期WebSocket接続テスト結果", [
                            'service_type' => $externalConnection->service_type,
                            'address' => $addr,
                            'response' => $directResponse,
                            'http_code' => $directHttpCode,
                            'error' => $directError
                        ]);

                        // WebSocketの場合、101 Switching Protocols が成功、404 Not Found が失敗
                        // タイムアウトエラーでも101が返されていれば成功
                        if ($directHttpCode === 101 || (!$directError && $directHttpCode !== 404)) {
                            return response()->json([
                                'success' => true,
                                'message' => "時刻同期WebSocketサーバーへの接続テストに成功しました（{$addr}）。",
                                'debug' => [
                                    'service_type' => $externalConnection->service_type,
                                    'successful_address' => $addr,
                                    'url' => $testUrl,
                                    'http_code' => $directHttpCode,
                                    'note' => 'WebSocketエンドポイントが応答しました'
                                ]
                            ]);
                        }
                    } else {
                        // メッセージサーバーの場合：従来のヘルスチェック
                        $healthUrl = "http://{$addr}:{$port}/health";
                        
                        Log::info("WebSocketメッセージサーバー接続テスト試行", [
                            'service_type' => $externalConnection->service_type,
                            'address' => $addr,
                            'url' => $healthUrl
                        ]);
                        
                        $ch = curl_init();
                        curl_setopt_array($ch, [
                            CURLOPT_URL => $healthUrl,
                            CURLOPT_TIMEOUT => 5,
                            CURLOPT_CONNECTTIMEOUT => 5,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_USERAGENT => 'SJT-CP Test Client',
                        ]);

                        $directResponse = curl_exec($ch);
                        $directHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $directError = curl_error($ch);
                        curl_close($ch);
                        
                        Log::info("WebSocketメッセージサーバー接続テスト結果", [
                            'service_type' => $externalConnection->service_type,
                            'address' => $addr,
                            'response' => $directResponse,
                            'http_code' => $directHttpCode,
                            'error' => $directError
                        ]);

                        if (!$directError && $directHttpCode >= 200 && $directHttpCode < 300) {
                            $responseData = json_decode($directResponse, true);
                            
                            return response()->json([
                                'success' => true,
                                'message' => "svr-sjt-ws WebSocketサーバーへの接続テストに成功しました（{$addr}）。",
                                'response' => $responseData,
                                'debug' => [
                                    'service_type' => $externalConnection->service_type,
                                    'successful_address' => $addr,
                                    'url' => $healthUrl,
                                    'http_code' => $directHttpCode
                                ]
                            ]);
                        }
                    }
                }
                
                // すべてのアドレスで失敗した場合
                return response()->json([
                    'success' => false,
                    'message' => 'すべてのアドレスでの接続テストに失敗しました。svr-sjt-wsサーバーが起動していない可能性があります。',
                    'debug' => [
                        'tried_addresses' => $addresses,
                        'port' => $port
                    ]
                ], 400);
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
