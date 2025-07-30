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
use Ratchet\RFC6455\Messaging\MessageInterface;

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
     * svr-sjt-ws仕様でメッセージを送信（端末IDまたはIPアドレス指定）
     *
     * @param array $messageData svr-sjt-ws仕様のメッセージデータ
     * @param array|null $targetIds 送信対象の端末ID配列（nullの場合はbroadcast）
     * @param int $port WebSocketポート（デフォルト: 8080）
     * @return bool 送信成功フラグ
     */
    public function sendWebSocketMessage(array $messageData, ?array $targetIds = null, ?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $serverAddress = $this->getServerAddress();
            $wsProtocol = $this->config['protocol'] === 'wss' ? 'wss' : 'ws';
            $path = $this->config['path'] ?? '/ws';
            $websocketUrl = "{$wsProtocol}://{$serverAddress}:{$port}{$path}";

            // ターゲット設定を更新
            if ($targetIds && count($targetIds) > 0) {
                if (count($targetIds) === 1) {
                    $messageData['target'] = [
                        'type' => 'individual',
                        'ids' => $targetIds
                    ];
                } else {
                    $messageData['target'] = [
                        'type' => 'group',
                        'ids' => $targetIds
                    ];
                }
            } else {
                $messageData['target'] = [
                    'type' => 'broadcast'
                ];
            }

            Log::info("svr-sjt-ws WebSocketサーバーにメッセージ送信開始", [
                'port' => $port,
                'url' => $websocketUrl,
                'event_type' => $messageData['event_type'],
                'target' => $messageData['target'],
                'message_id' => $messageData['metadata']['message_id'],
                'full_message' => $messageData
            ]);

            // WebSocket接続を作成
            $success = false;
            $promise = $this->connector->__invoke($websocketUrl)
                ->then(function (\Ratchet\Client\WebSocket $conn) use ($messageData, &$success) {
                    // 接続成功、メッセージを送信
                    $conn->send(json_encode($messageData));
                    $success = true;
                    $conn->close();
                    
                    Log::info("svr-sjt-ws WebSocketサーバーへのメッセージ送信成功", [
                        'message_id' => $messageData['metadata']['message_id']
                    ]);
                    
                }, function (Exception $e) use ($messageData) {
                    Log::error("svr-sjt-ws WebSocket接続失敗", [
                        'error' => $e->getMessage(),
                        'message_id' => $messageData['metadata']['message_id']
                    ]);
                    throw $e;
                });

            // 接続を待機（最大タイムアウト時間）
            $this->runEventLoopWithTimeout((float)$this->config['timeout']);

            if (!$success) {
                throw new Exception("WebSocket接続またはメッセージ送信に失敗しました");
            }

            return true;

        } catch (Exception $e) {
            Log::error("svr-sjt-ws WebSocketサーバーへのメッセージ送信エラー", [
                'error' => $e->getMessage(),
                'message_id' => $messageData['metadata']['message_id'] ?? 'unknown'
            ]);
            
            return false;
        }
    }

    /**
     * 端末にWebSocketでメッセージを送信（後方互換性のため残す）
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
            $serverAddress = $this->getServerAddress();
            $httpProtocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            $sendUrl = "{$httpProtocol}://{$serverAddress}:{$port}/api/send-message";

            Log::info("HTTP APIでメッセージ送信開始", [
                'device_ip' => $deviceIp,
                'port' => $port,
                'url' => $sendUrl,
                'data' => $messageData
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $sendUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'ip' => $deviceIp,
                    'message' => $messageData
                ]),
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
                $responseData = json_decode($response, true);
                Log::info("メッセージ送信成功", [
                    'device_ip' => $deviceIp,
                    'http_code' => $httpCode,
                    'response' => $responseData
                ]);
                return true;
            } else {
                $responseData = json_decode($response, true);
                $errorMsg = $responseData['error'] ?? 'HTTPコード ' . $httpCode;
                throw new Exception("メッセージ送信失敗: {$errorMsg}");
            }

        } catch (Exception $e) {
            Log::error("メッセージ送信エラー", [
                'device_ip' => $deviceIp,
                'error' => $e->getMessage()
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
     * svr-sjt-ws仕様のnotificationメッセージデータを構築
     *
     * @param string $title タイトル
     * @param string $content 本文
     * @param string $level レベル（info, warning, error, success）
     * @param string|null $link リンク（アクションURL）
     * @param int $duration 表示時間（ミリ秒）
     * @return array
     */
    public function buildNotificationMessage(string $title, string $content, string $level = 'info', ?string $link = null, int $duration = 5000): array
    {
        $data = [
            'title' => $title,
            'message' => $content,
            'level' => $level,
            'duration' => $duration
        ];

        if ($link) {
            $data['action'] = [
                'type' => 'url',
                'target' => $link
            ];
        }

        return [
            'event_type' => 'notification',
            'target' => [
                'type' => 'broadcast'
            ],
            'data' => $data,
            'metadata' => [
                'timestamp' => now()->toISOString(),
                'sender' => 'control_panel',
                'sender_id' => 'sjt-cp-admin',
                'message_id' => $this->generateMessageId()
            ]
        ];
    }

    /**
     * svr-sjt-ws仕様のmacroメッセージデータを構築
     *
     * @param string $action execute|cancel|status
     * @param int|null $templateId テンプレートID
     * @param string|null $executionId 実行ID
     * @param array $variables 変数の値
     * @param array $options 実行オプション
     * @return array
     */
    public function buildMacroMessage(string $action, ?int $templateId = null, ?string $executionId = null, array $variables = [], array $options = []): array
    {
        $data = [
            'action' => $action
        ];

        if ($templateId !== null) {
            $data['template_id'] = $templateId;
        }

        if ($executionId !== null) {
            $data['execution_id'] = $executionId;
        }

        if (!empty($variables)) {
            $data['variables'] = $variables;
        }

        if (!empty($options)) {
            $data['options'] = $options;
        }

        return [
            'event_type' => 'macro',
            'target' => [
                'type' => 'broadcast'
            ],
            'data' => $data,
            'metadata' => [
                'timestamp' => now()->toISOString(),
                'sender' => 'control_panel',
                'sender_id' => 'sjt-cp-admin',
                'message_id' => $this->generateMessageId()
            ]
        ];
    }

    /**
     * svr-sjt-ws仕様のfileメッセージデータを構築
     *
     * @param string $taskId タスクID
     * @param string $action download|cancel
     * @param string|null $fileUrl ファイルURL
     * @param string $destination desktop|documents|custom
     * @param string|null $customPath カスタムパス
     * @param bool $extract ZIP展開するか
     * @param bool $overwrite 上書きするか
     * @return array
     */
    public function buildFileMessage(string $taskId, string $action, ?string $fileUrl = null, string $destination = 'desktop', ?string $customPath = null, bool $extract = false, bool $overwrite = false): array
    {
        $data = [
            'task_id' => $taskId,
            'action' => $action,
            'destination' => $destination,
            'extract' => $extract,
            'overwrite' => $overwrite
        ];

        if ($fileUrl) {
            $data['file_url'] = $fileUrl;
        }

        if ($customPath) {
            $data['custom_path'] = $customPath;
        }

        return [
            'event_type' => 'file',
            'target' => [
                'type' => 'broadcast'
            ],
            'data' => $data,
            'metadata' => [
                'timestamp' => now()->toISOString(),
                'sender' => 'control_panel',
                'sender_id' => 'sjt-cp-admin',
                'message_id' => $this->generateMessageId()
            ]
        ];
    }

    /**
     * svr-sjt-ws仕様のcallメッセージデータを構築
     *
     * @param string $callType general|technical
     * @param string $terminalId 呼び出し元端末ID
     * @param string $playerNumber 選手番号
     * @param string|null $message 詳細メッセージ
     * @param string $priority normal|high|urgent
     * @param string|null $location 座席位置
     * @return array
     */
    public function buildCallMessage(string $callType, string $terminalId, string $playerNumber, ?string $message = null, string $priority = 'normal', ?string $location = null): array
    {
        $data = [
            'call_type' => $callType,
            'terminal_id' => $terminalId,
            'player_number' => $playerNumber,
            'priority' => $priority
        ];

        if ($message) {
            $data['message'] = $message;
        }

        if ($location) {
            $data['location'] = $location;
        }

        return [
            'event_type' => 'call',
            'target' => [
                'type' => 'broadcast'
            ],
            'data' => $data,
            'metadata' => [
                'timestamp' => now()->toISOString(),
                'sender' => 'terminal',
                'sender_id' => $terminalId,
                'message_id' => $this->generateMessageId()
            ]
        ];
    }

    /**
     * メッセージIDを生成（UUID v4形式）
     *
     * @return string
     */
    private function generateMessageId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * メッセージデータを構築（後方互換性のため残す）
     *
     * @param string $title タイトル
     * @param string $content 本文
     * @param string|null $link リンク
     * @param string|null $imageUrl 画像URL
     * @return array
     */
    public function buildMessageData(string $title, string $content, ?string $link = null, ?string $imageUrl = null): array
    {
        // 新仕様のnotificationメッセージとして構築
        return $this->buildNotificationMessage($title, $content, 'info', $link);
    }

    /**
     * svr-sjt-wsサーバーの健康状態チェック
     *
     * @param int $port WebSocketポート
     * @return bool サーバーが稼働中かどうか
     */
    public function checkServerHealth(?int $port = null): bool
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $serverAddress = $this->getServerAddress();
            $httpProtocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            $healthUrl = "{$httpProtocol}://{$serverAddress}:{$port}/health";
            
            Log::info("svr-sjt-ws サーバー健康状態チェック開始", [
                'server_address' => $serverAddress,
                'port' => $port,
                'url' => $healthUrl
            ]);

            $ch = curl_init();
            $stderrHandle = fopen('php://temp', 'w+');
            curl_setopt_array($ch, [
                CURLOPT_URL => $healthUrl,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => $stderrHandle
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            
            // デバッグ情報を取得
            $verboseLog = null;
            if (is_resource($stderrHandle)) {
                rewind($stderrHandle);
                $verboseLog = stream_get_contents($stderrHandle);
                fclose($stderrHandle);
            }
            
            curl_close($ch);
            
            // 詳細なデバッグログを出力
            Log::info("cURL詳細情報", [
                'url' => $healthUrl,
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error,
                'curl_info' => $curlInfo,
                'verbose_log' => $verboseLog
            ]);

            if ($error) {
                Log::warning("svr-sjt-ws サーバー健康状態チェック失敗", [
                    'error' => $error
                ]);
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                Log::info("svr-sjt-ws サーバー健康状態チェック成功", [
                    'http_code' => $httpCode,
                    'response' => $responseData
                ]);
                return true;
            }

            Log::warning("svr-sjt-ws サーバー健康状態チェック失敗", [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return false;

        } catch (Exception $e) {
            Log::error("svr-sjt-ws サーバー健康状態チェックエラー", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * WebSocketサーバーから接続中のクライアント一覧を取得
     *
     * @return array クライアント情報
     */
    public function getConnectedClients(): array
    {
        try {
            $serverAddress = $this->getServerAddress();
            $port = $this->config['default_port'];
            $path = $this->config['path'] ?? '/ws';
            $wsProtocol = $this->config['protocol'] === 'wss' ? 'wss' : 'ws';
            $websocketUrl = "{$wsProtocol}://{$serverAddress}:{$port}{$path}";
            
            Log::info("WebSocketサーバーからクライアント一覧取得開始", [
                'server_address' => $serverAddress,
                'port' => $port,
                'url' => $websocketUrl
            ]);

            $messageId = 'clients-request-' . uniqid();
            $clientsMessage = [
                'event_type' => 'clients',
                'target' => ['type' => 'broadcast'],
                'data' => [],
                'metadata' => [
                    'timestamp' => now()->toJSON(),
                    'sender' => 'control_panel',
                    'sender_id' => 'sjt-cp-admin',
                    'message_id' => $messageId
                ]
            ];

            Log::info("WebSocketクライアント一覧リクエスト送信", [
                'message_id' => $messageId,
                'request' => $clientsMessage
            ]);

            // WebSocket接続を確立してメッセージを送信
            $response = null;
            $connectionError = null;
            $timeoutReached = false;
            
            // 新しいイベントループインスタンスを使用
            $loop = \React\EventLoop\Loop::get();
            $connector = new Connector($loop);
            
            $promise = $connector($websocketUrl)
                ->then(function (WebSocket $conn) use ($clientsMessage, &$response, $loop) {
                    Log::info("WebSocket接続成功、メッセージ送信");
                    
                    // クライアント一覧要求メッセージを送信
                    $conn->send(json_encode($clientsMessage));
                    
                    // レスポンスを待機
                    $conn->on('message', function (MessageInterface $msg) use (&$response, $conn, $loop) {
                        $data = json_decode($msg->getPayload(), true);
                        Log::info("WebSocketからメッセージ受信", ['data' => $data]);
                        
                        if ($data && isset($data['event_type']) && $data['event_type'] === 'clients') {
                            $response = $data;
                            $conn->close();
                            $loop->stop();
                        }
                    });
                    
                    // 接続エラー処理
                    $conn->on('close', function () use ($loop) {
                        Log::info("WebSocket接続が閉じられました");
                        $loop->stop();
                    });
                    
                })
                ->otherwise(function (\Exception $e) use (&$connectionError, $loop) {
                    Log::error("WebSocket接続エラー", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $connectionError = $e;
                    $loop->stop();
                });

            // タイムアウト設定（10秒）
            $timer = $loop->addTimer(10, function () use (&$timeoutReached, $loop) {
                Log::warning("WebSocket接続がタイムアウトしました");
                $timeoutReached = true;
                $loop->stop();
            });

            // イベントループを実行して結果を待機
            $loop->run();
            
            // タイマーをクリーンアップ
            $loop->cancelTimer($timer);
            
            if ($connectionError) {
                throw $connectionError;
            }
            
            if ($timeoutReached) {
                throw new Exception("WebSocket接続がタイムアウトしました");
            }
            
            if ($response && isset($response['data'])) {
                Log::info("WebSocketクライアント一覧取得成功", [
                    'total_count' => $response['data']['total_count'] ?? 0,
                    'connected_count' => $response['data']['connected_count'] ?? 0
                ]);
                return $response['data'];
            } else {
                throw new Exception("サーバーから有効な応答が返されませんでした");
            }

        } catch (Exception $e) {
            Log::error("WebSocketクライアント一覧取得エラー", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * svr-sjt-wsサーバーの接続状況取得（旧実装・互換性維持）
     *
     * @param int $port WebSocketポート
     * @return array 接続情報
     */
    public function getConnectionStatus(?int $port = null): array
    {
        try {
            $port = $port ?: $this->config['default_port'];
            $serverAddress = $this->getServerAddress();
            $httpProtocol = $this->config['protocol'] === 'wss' ? 'https' : 'http';
            $connectionsUrl = "{$httpProtocol}://{$serverAddress}:{$port}/connections";
            
            Log::info("svr-sjt-ws サーバー接続状況取得開始", [
                'server_address' => $serverAddress,
                'port' => $port,
                'url' => $connectionsUrl
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $connectionsUrl,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("接続状況取得エラー: {$error}");
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                Log::info("svr-sjt-ws サーバー接続状況取得成功", [
                    'http_code' => $httpCode,
                    'connections_count' => count($responseData['connections'] ?? [])
                ]);
                return $responseData;
            } else {
                throw new Exception("接続状況取得失敗: HTTPコード {$httpCode}");
            }

        } catch (Exception $e) {
            Log::error("svr-sjt-ws サーバー接続状況取得エラー", [
                'error' => $e->getMessage()
            ]);
            return [
                'connections' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 端末への接続テスト（後方互換性のため残す）
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
        // use_localhost が true の場合は127.0.0.1を使用
        if ($this->config['use_localhost'] === true || $this->config['use_localhost'] === "1" || $this->config['use_localhost'] === 1) {
            return '127.0.0.1';
        }
        
        $serverAddress = $this->config['server_address'] ?: 'localhost';
        
        // host.docker.internal を実際のIPアドレスに解決
        if ($serverAddress === 'host.docker.internal') {
            $resolvedIp = gethostbyname('host.docker.internal');
            if ($resolvedIp !== 'host.docker.internal') {
                Log::info("host.docker.internal を {$resolvedIp} に解決しました");
                return $resolvedIp;
            } else {
                Log::warning("host.docker.internal の解決に失敗しました。そのまま使用します。");
                return $serverAddress;
            }
        }
        
        return $serverAddress;
    }
}