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
            Log::info("予約メッセージID {$this->message->id} の送信ジョブを開始します。");

            // キャンセルされている場合は処理しない
            if ($this->message->status === 'cancelled') {
                Log::info("メッセージID {$this->message->id} はキャンセルされているため送信をスキップしました。");
                return;
            }

            // MessageControllerのsendMessageメソッドを使用
            $controller = new \App\Http\Controllers\Admin\MessageController(new WebSocketService());
            
            // sendMessageメソッドを呼び出し（リフレクションを使用してprivateメソッドを呼び出し）
            $reflection = new \ReflectionClass($controller);
            $sendMessageMethod = $reflection->getMethod('sendMessage');
            $sendMessageMethod->setAccessible(true);
            $sendMessageMethod->invoke($controller, $this->message);

            Log::info("予約メッセージID {$this->message->id} の送信ジョブが完了しました。");

        } catch (\Exception $e) {
            Log::error("予約メッセージID {$this->message->id} の送信中にエラーが発生しました: " . $e->getMessage());
            Log::error("スタックトレース: " . $e->getTraceAsString());
            
            $this->message->update([
                'status' => 'failed',
            ]);

            // ジョブを失敗させる
            $this->fail($e);
        }
    }
}
