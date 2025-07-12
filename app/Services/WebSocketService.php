<?php

namespace App\Services;

use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;
use React\Socket\TimeoutConnector;
use React\Socket\TcpConnector;
use Illuminate\Support\Facades\Log;
use App\Models\ExternalConnection;
use Exception;

class WebSocketService
{
    private $loop;
    private $connector;
    private $config;
    
    public function __construct()
    {
        $this->loop = Loop::get();
        $this->config = ExternalConnection::getWebSocketConfig();
        
        // タイムアウト設定付きのコネクターを作成
        $tcpConnector = new TcpConnector($this->loop);
        $timeoutConnector = new TimeoutConnector($tcpConnector, (float)$this->config['timeout'], $this->loop);
        $this->connector = new Connector($this->loop, $timeoutConnector);
    }

    /**
     * 端末にWebSocketでメッセージを送信
     *
     * @param string $deviceIp 端末のIPアドレス
     * @param array $messageData 送信するメッセージデータ
     * @param int $port WebSocketポート（デフォルト: 8080）
     * @return bool 送信成功フラグ
     */
    public function sendMessageToDevice(string $deviceIp, array $messageData, ?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $protocol = $this->config['protocol'];
            $path = $this->config['path'];
            
            // サーバーアドレス設定を確認
            $serverAddress = $this->getServerAddress();
            $url = "{$protocol}://{$serverAddress}:{$port}{$path}";
            $success = false;
            $error = null;

            Log::info("WebSocket送信開始", [
                'device_ip' => $deviceIp,
                'port' => $port,
                'url' => $url,
                'data' => $messageData
            ]);

            // WebSocket接続とメッセージ送信
            $this->connector->__invoke($url)
                ->then(function (WebSocket $conn) use ($messageData, &$success) {
                    // 接続成功、メッセージを送信
                    $conn->send(json_encode($messageData));
                    
                    Log::info("WebSocketメッセージ送信完了", [
                        'data' => $messageData
                    ]);
                    
                    $success = true;
                    
                    // 送信後すぐに接続を閉じる
                    $conn->close();
                    
                }, function (Exception $e) use (&$error) {
                    // 接続失敗
                    $error = $e;
                    Log::error("WebSocket接続失敗", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                });

            // イベントループを短時間実行（最大5秒）
            $this->runEventLoopWithTimeout(5.0);

            if ($error) {
                throw $error;
            }

            return $success;

        } catch (Exception $e) {
            Log::error("WebSocket送信エラー", [
                'device_ip' => $deviceIp,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * 複数の端末に並列でメッセージを送信
     *
     * @param array $devices 端末リスト（IPアドレスの配列）
     * @param array $messageData 送信するメッセージデータ
     * @param int $port WebSocketポート
     * @return array 送信結果 ['success' => [], 'failed' => []]
     */
    public function sendMessageToMultipleDevices(array $devices, array $messageData, int $port = 8080): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        $promises = [];

        foreach ($devices as $deviceIp) {
            $url = "ws://{$deviceIp}:{$port}/message";
            
            $promise = $this->connector->__invoke($url)
                ->then(function (WebSocket $conn) use ($messageData, $deviceIp, &$results) {
                    // 接続成功、メッセージを送信
                    $conn->send(json_encode($messageData));
                    $results['success'][] = $deviceIp;
                    $conn->close();
                    
                    Log::info("WebSocket送信成功", ['device_ip' => $deviceIp]);
                    
                }, function (Exception $e) use ($deviceIp, &$results) {
                    // 接続失敗
                    $results['failed'][] = $deviceIp;
                    
                    Log::error("WebSocket送信失敗", [
                        'device_ip' => $deviceIp,
                        'error' => $e->getMessage()
                    ]);
                });

            $promises[] = $promise;
        }

        // 全ての接続を待機（最大10秒）
        $this->runEventLoopWithTimeout(10.0);

        return $results;
    }

    /**
     * メッセージデータを構築
     *
     * @param string $title タイトル
     * @param string $content 本文
     * @param string|null $link リンク
     * @param string|null $imageUrl 画像URL
     * @return array
     */
    public function buildMessageData(string $title, string $content, ?string $link = null, ?string $imageUrl = null): array
    {
        return [
            'type' => 'message',
            'timestamp' => now()->toISOString(),
            'data' => [
                'title' => $title,
                'content' => $content,
                'link' => $link,
                'image_url' => $imageUrl,
                'sender' => 'SJT-CP'
            ]
        ];
    }

    /**
     * 端末への接続テスト
     *
     * @param string $deviceIp 端末のIPアドレス
     * @param int $port WebSocketポート
     * @return bool 接続可能フラグ
     */
    public function testConnection(string $deviceIp, ?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $serverAddress = $this->getServerAddress();
            
            Log::info("WebSocket接続テスト開始", [
                'device_ip' => $deviceIp,
                'server_address' => $serverAddress,
                'port' => $port
            ]);

            // まずHTTPで接続テスト（WebSocketサーバーのステータス確認）
            $httpProtocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            $statusUrl = "{$httpProtocol}://{$serverAddress}:{$port}/status";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $statusUrl,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::warning("HTTP接続テスト失敗", [
                    'device_ip' => $deviceIp,
                    'error' => $error
                ]);
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                if (isset($responseData['status']) && $responseData['status'] === 'running') {
                    Log::info("WebSocket接続テスト成功 (HTTP経由)", [
                        'device_ip' => $deviceIp,
                        'response' => $responseData
                    ]);
                    return true;
                }
            }

            Log::warning("WebSocket接続テスト失敗", [
                'device_ip' => $deviceIp,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return false;

        } catch (Exception $e) {
            Log::error("WebSocket接続テストエラー", [
                'device_ip' => $deviceIp,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * イベントループをタイムアウト付きで実行
     *
     * @param float $timeout タイムアウト秒数
     */
    private function runEventLoopWithTimeout(float $timeout): void
    {
        $timer = $this->loop->addTimer($timeout, function () {
            $this->loop->stop();
        });

        $this->loop->run();
        
        $this->loop->cancelTimer($timer);
    }

    /**
     * 特定IPアドレスのクライアントが接続されているかチェック
     *
     * @param string $deviceIp 端末のIPアドレス
     * @param int $port WebSocketポート
     * @return bool 接続されているかどうか
     */
    public function checkClientConnection(string $deviceIp, ?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $serverAddress = $this->getServerAddress();
            $httpProtocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            $checkUrl = "{$httpProtocol}://{$serverAddress}:{$port}/api/client-check";
            
            Log::info("クライアント接続チェック開始", [
                'device_ip' => $deviceIp,
                'server_address' => $serverAddress,
                'port' => $port,
                'check_url' => $checkUrl
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $checkUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['ip' => $deviceIp]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::warning("クライアント接続チェック失敗", [
                    'device_ip' => $deviceIp,
                    'error' => $error
                ]);
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                $connected = $responseData['connected'] ?? false;
                
                Log::info("クライアント接続チェック結果", [
                    'device_ip' => $deviceIp,
                    'connected' => $connected,
                    'client_info' => $responseData['clientInfo'] ?? null
                ]);
                
                return $connected;
            }

            Log::warning("クライアント接続チェック失敗", [
                'device_ip' => $deviceIp,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return false;

        } catch (Exception $e) {
            Log::error("クライアント接続チェックエラー", [
                'device_ip' => $deviceIp,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * HTTPでのフォールバック送信（WebSocketが利用できない場合）
     *
     * @param string $deviceIp 端末のIPアドレス
     * @param array $messageData 送信するメッセージデータ
     * @param int $port HTTPポート
     * @return bool 送信成功フラグ
     */
    public function sendMessageViaHttp(string $deviceIp, array $messageData, ?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $protocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            
            // サーバーアドレス設定を確認
            $serverAddress = $this->getServerAddress();
            $url = "{$protocol}://{$serverAddress}:{$port}/api/message";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($messageData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => $this->config['timeout'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("HTTP送信エラー: {$error}");
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info("HTTP送信成功", [
                    'device_ip' => $deviceIp,
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return true;
            } else {
                throw new Exception("HTTP送信失敗: HTTPコード {$httpCode}");
            }

        } catch (Exception $e) {
            Log::error("HTTP送信エラー", [
                'device_ip' => $deviceIp,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * 使用するサーバーアドレスを取得
     *
     * @return string 使用するサーバーアドレス
     */
    private function getServerAddress(): string
    {
        // use_localhost が true の場合はlocalhost使用
        if ($this->config['use_localhost']) {
            return 'localhost';
        }
        
        // カスタムサーバーアドレスを使用
        return $this->config['server_address'] ?: 'localhost';
    }
}