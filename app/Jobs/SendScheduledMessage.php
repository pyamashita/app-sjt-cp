<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Device;
use App\Services\WebSocketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendScheduledMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // キャンセルされている場合は処理しない
            if ($this->message->status === 'cancelled') {
                Log::info("メッセージID {$this->message->id} はキャンセルされているため送信をスキップしました。");
                return;
            }

            // メッセージステータスを送信中に更新
            $this->message->update([
                'status' => 'sending',
                'sent_at' => now(),
            ]);

            // WebSocketサービスのインスタンスを作成
            $webSocketService = new WebSocketService();

            // メッセージデータを準備
            $messageData = [
                'title' => $this->message->title,
                'content' => $this->message->content,
                'link' => $this->message->link,
                'image_url' => $this->message->resource ? asset('storage/' . $this->message->resource->file_path) : null,
                'sender' => 'SJT-CP',
            ];

            // 送信対象デバイスを取得
            $deviceIds = $this->message->messageDevices()->pluck('device_id')->toArray();
            $devices = Device::whereIn('id', $deviceIds)->get();

            $successCount = 0;
            $failureCount = 0;

            // 各デバイスに送信
            foreach ($devices as $device) {
                $messageDevice = $this->message->messageDevices()
                    ->where('device_id', $device->id)
                    ->first();

                // 送信開始
                $messageDevice->update([
                    'status' => 'sending',
                    'sent_at' => now(),
                ]);

                // WebSocketで送信
                $sent = $webSocketService->sendMessageToDevice(
                    $device->ip_address,
                    $messageData,
                    $device->port ?? 8080
                );

                if ($sent) {
                    // 送信成功
                    $messageDevice->update([
                        'status' => 'sent',
                        'completed_at' => now(),
                    ]);
                    $successCount++;
                    Log::info("予約メッセージをデバイス {$device->name} ({$device->ip_address}) に送信しました。");
                } else {
                    // 送信失敗
                    $messageDevice->update([
                        'status' => 'failed',
                        'error_message' => '送信に失敗しました',
                    ]);
                    $failureCount++;
                    Log::error("予約メッセージのデバイス {$device->name} ({$device->ip_address}) への送信に失敗しました。");
                }
            }

            // メッセージのステータスを更新
            if ($failureCount === 0) {
                $this->message->update([
                    'status' => 'sent',
                    'completed_at' => now(),
                ]);
                Log::info("予約メッセージID {$this->message->id} の送信が完了しました。");
            } else if ($successCount === 0) {
                $this->message->update([
                    'status' => 'failed',
                ]);
                Log::error("予約メッセージID {$this->message->id} の送信が全て失敗しました。");
            } else {
                $this->message->update([
                    'status' => 'partially_sent',
                    'completed_at' => now(),
                ]);
                Log::warning("予約メッセージID {$this->message->id} の送信が一部失敗しました。成功: {$successCount}、失敗: {$failureCount}");
            }

        } catch (\Exception $e) {
            Log::error("予約メッセージID {$this->message->id} の送信中にエラーが発生しました: " . $e->getMessage());
            
            $this->message->update([
                'status' => 'failed',
            ]);

            // ジョブを失敗させる
            $this->fail($e);
        }
    }
}
