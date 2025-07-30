<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CompetitorCall;
use App\Models\ExternalConnection;
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;
use React\Socket\TimeoutConnector;
use React\Socket\TcpConnector;
use Carbon\Carbon;
use Exception;

class ListenWebSocketCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:listen-calls {--reconnect-interval=5 : 再接続間隔（秒）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'svr-sjt-wsから選手呼び出しメッセージを監視し、DBに保存します';

    private $loop;
    private $connector;
    private $connection;
    private $reconnectInterval;
    private $terminalId;
    private $pingTimer;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->reconnectInterval = (int) $this->option('reconnect-interval');
        
        $this->info('選手呼び出しWebSocket監視を開始します...');
        $this->info("再接続間隔: {$this->reconnectInterval}秒");

        // イベントループの作成
        $this->loop = Loop::get();
        
        // WebSocket設定を取得
        $config = ExternalConnection::getWebSocketConfig();
        
        // タイムアウト設定付きのコネクターを作成（WebSocketServiceと同じ方式）
        $tcpConnector = new TcpConnector($this->loop);
        $timeoutConnector = new TimeoutConnector($tcpConnector, (float)($config['timeout'] ?? 10), $this->loop);
        $this->connector = new Connector($this->loop, $timeoutConnector);

        // 初回接続試行
        $this->connectToWebSocket();

        // イベントループ開始
        $this->loop->run();
    }

    private function connectToWebSocket()
    {
        try {
            // WebSocket設定を取得
            $config = ExternalConnection::getWebSocketConfig();
            
            // WebSocketServiceと同じ方式でURL構築
            if ($config['use_localhost']) {
                $host = '127.0.0.1'; // localhost の代わりに IP アドレスを使用
            } else {
                $configHost = $config['server_address'] ?: '127.0.0.1';
                // host.docker.internal を実際のIPアドレスに解決
                if ($configHost === 'host.docker.internal') {
                    $resolvedIp = gethostbyname('host.docker.internal');
                    if ($resolvedIp !== 'host.docker.internal') {
                        $host = $resolvedIp;
                        $this->info("host.docker.internal を {$resolvedIp} に解決しました");
                    } else {
                        $host = $configHost;
                        $this->warn("host.docker.internal の解決に失敗しました。そのまま使用します。");
                    }
                } else {
                    $host = $configHost;
                }
            }
            $port = $config['default_port'] ?? 8080;
            $wsProtocol = $config['protocol'] === 'wss' ? 'wss' : 'ws';
            $path = $config['path'] ?? '/ws';
            
            $wsUrl = "{$wsProtocol}://{$host}:{$port}{$path}";

            $this->info("WebSocketサーバーに接続中: {$wsUrl}");
            $this->info("設定情報: " . json_encode($config, JSON_UNESCAPED_UNICODE));
            $this->info("デバッグ - Host: {$host}, Port: {$port}, Protocol: {$wsProtocol}, Path: {$path}");

            $this->connector->__invoke($wsUrl)
                ->then(function (WebSocket $conn) {
                    $this->connection = $conn;
                    $this->info('WebSocketサーバーに接続しました');

                    // 端末登録を実行
                    $this->registerTerminal($conn);

                    // 接続成功時の処理
                    $conn->on('message', function ($msg) {
                        $this->handleMessage($msg);
                    });

                    $conn->on('close', function ($code = null, $reason = null) {
                        $this->warn("WebSocket接続が閉じられました: {$code} - {$reason}");
                        $this->stopPingTimer();
                        $this->scheduleReconnect();
                    });

                    $conn->on('error', function (Exception $e) {
                        $this->error("WebSocketエラー: " . $e->getMessage());
                        $this->stopPingTimer();
                        $this->scheduleReconnect();
                    });

                }, function (Exception $e) {
                    $this->error("WebSocket接続エラー: " . $e->getMessage());
                    $this->scheduleReconnect();
                });

        } catch (Exception $e) {
            $this->error("接続処理でエラー: " . $e->getMessage());
            $this->scheduleReconnect();
        }
    }

    private function handleMessage($message)
    {
        try {
            $data = json_decode($message, true);
            
            if (!$data) {
                $this->warn("無効なJSONメッセージを受信: {$message}");
                return;
            }

            $this->info("メッセージ受信: " . json_encode($data, JSON_UNESCAPED_UNICODE));

            // メッセージタイプに応じた処理
            if (isset($data['type'])) {
                if ($data['type'] === 'registered') {
                    $this->info("端末登録完了: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                    $this->startPingTimer();
                } elseif ($data['type'] === 'pong') {
                    $this->info("Pong受信");
                }
            }

            // 選手呼び出しメッセージかチェック
            if ($this->isCompetitorCallMessage($data)) {
                $this->saveCompetitorCall($data);
            }

        } catch (Exception $e) {
            $this->error("メッセージ処理エラー: " . $e->getMessage());
        }
    }

    private function registerTerminal($conn)
    {
        // 端末IDを固定値に設定
        $this->terminalId = 'TML-ctrl';
        
        $registerMessage = [
            'type' => 'register',
            'terminal_id' => $this->terminalId,
            'player_number' => 'SYSTEM',
            'location' => 'LARAVEL-LISTENER'
        ];

        $conn->send(json_encode($registerMessage));
        $this->info("端末登録メッセージ送信: " . json_encode($registerMessage, JSON_UNESCAPED_UNICODE));
    }

    private function startPingTimer()
    {
        // 30秒ごとにping送信
        $this->pingTimer = $this->loop->addPeriodicTimer(30, function () {
            if ($this->connection) {
                $pingMessage = ['type' => 'ping'];
                $this->connection->send(json_encode($pingMessage));
                $this->info("Ping送信");
            }
        });
    }

    private function stopPingTimer()
    {
        if ($this->pingTimer) {
            $this->loop->cancelTimer($this->pingTimer);
            $this->pingTimer = null;
        }
    }

    private function isCompetitorCallMessage($data): bool
    {
        // 新しいcall仕様に対応
        return isset($data['event_type']) && $data['event_type'] === 'call' &&
               isset($data['data']['terminal_id']) && 
               isset($data['data']['call_type']) && 
               in_array($data['data']['call_type'], ['general', 'technical']);
    }

    private function saveCompetitorCall($data)
    {
        try {
            $callData = $data['data'];
            
            $competitorCall = CompetitorCall::create([
                'device_id' => $callData['terminal_id'],
                'call_type' => $callData['call_type'],
                'called_at' => isset($callData['call_datetime']) ? 
                    Carbon::parse($callData['call_datetime']) : 
                    (isset($data['metadata']['timestamp']) ? 
                        Carbon::parse($data['metadata']['timestamp']) : 
                        Carbon::now()),
            ]);

            $this->info("選手呼び出しを保存しました: ID={$competitorCall->id}, 端末={$callData['terminal_id']}, 種別={$callData['call_type']}");

        } catch (Exception $e) {
            $this->error("DB保存エラー: " . $e->getMessage());
        }
    }

    private function scheduleReconnect()
    {
        $this->warn("{$this->reconnectInterval}秒後に再接続を試行します...");
        
        $this->loop->addTimer($this->reconnectInterval, function () {
            $this->connectToWebSocket();
        });
    }
}
