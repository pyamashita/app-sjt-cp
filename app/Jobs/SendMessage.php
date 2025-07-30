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

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageId;
    
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;
    
    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("SendMessageジョブ開始: messageId = {$this->messageId}");
            
            // メッセージを取得
            $message = Message::find($this->messageId);
            
            if (!$message) {
                Log::warning("メッセージID {$this->messageId} が見つかりません。削除された可能性があります。");
                return;
            }
            
            Log::info("メッセージID {$message->id} の送信ジョブを開始します。ステータス: {$message->status}");

            // キャンセルされているか既に完了している場合はスキップ
            if (in_array($message->status, ['completed', 'cancelled', 'failed'])) {
                Log::info("メッセージID {$message->id} は既に処理済みです。ステータス: {$message->status}");
                return;
            }
            
            // 送信中状態でも古い場合は再実行する（5分以上経過）
            if ($message->status === 'sending' && $message->sent_at && $message->sent_at->diffInMinutes(now()) > 5) {
                Log::warning("メッセージID {$message->id} は送信中で5分以上経過しています。再実行します。");
            } elseif ($message->status === 'sending') {
                Log::info("メッセージID {$message->id} は現在送信中です。スキップします。");
                return;
            }

            Log::info("MessageControllerの呼び出し開始");
            
            // MessageControllerのsendMessageメソッドを使用
            $controller = new \App\Http\Controllers\Admin\MessageController(new WebSocketService());
            
            Log::info("リフレクション開始");
            
            // sendMessageメソッドを呼び出し（リフレクションを使用してprivateメソッドを呼び出し）
            $reflection = new \ReflectionClass($controller);
            $sendMessageMethod = $reflection->getMethod('sendMessage');
            $sendMessageMethod->setAccessible(true);
            
            Log::info("sendMessageメソッド実行開始");
            $sendMessageMethod->invoke($controller, $message);
            Log::info("sendMessageメソッド実行完了");

            Log::info("メッセージID {$message->id} の送信ジョブが完了しました。");

        } catch (\Exception $e) {
            Log::error("メッセージID {$this->messageId} の送信中にエラーが発生しました: " . $e->getMessage());
            Log::error("スタックトレース: " . $e->getTraceAsString());
            
            // メッセージが存在する場合のみステータスを更新
            $message = Message::find($this->messageId);
            if ($message) {
                $message->update([
                    'status' => 'failed',
                ]);
            }

            // ジョブを失敗させる
            $this->fail($e);
        }
    }
}
