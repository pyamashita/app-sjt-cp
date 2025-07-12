<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Device;
use App\Models\Resource;
use App\Models\MessageDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WebSocketService;
use App\Jobs\SendScheduledMessage;

class MessageController extends Controller
{
    protected $webSocketService;

    public function __construct(WebSocketService $webSocketService)
    {
        $this->webSocketService = $webSocketService;
    }
    /**
     * メッセージ一覧
     */
    public function index(Request $request)
    {
        $query = Message::with(['creator', 'resource', 'devices'])
            ->latest();

        // 検索
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // ステータスフィルタ
        if ($status = $request->get('status')) {
            $query->status($status);
        }

        // 送信方法フィルタ
        if ($sendMethod = $request->get('send_method')) {
            $query->sendMethod($sendMethod);
        }

        $messages = $query->paginate(20);

        return view('admin.messages.index', compact('messages'));
    }

    /**
     * メッセージ詳細
     */
    public function show(Message $message)
    {
        $message->load(['creator', 'resource', 'messageDevices.device']);
        
        return view('admin.messages.show', compact('message'));
    }

    /**
     * メッセージ作成フォーム
     */
    public function create()
    {
        $devices = Device::orderBy('name')->get();
        $resources = Resource::where('is_public', true)
            ->where('category', 'image')
            ->orderBy('name')
            ->get();

        return view('admin.messages.create', compact('devices', 'resources'));
    }

    /**
     * メッセージ保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'send_method' => 'required|in:immediate,scheduled',
            'title' => 'nullable|max:50',
            'content' => 'required|max:1000',
            'link' => 'nullable|url|max:2000',
            'resource_id' => 'nullable|exists:resources,id',
            'scheduled_at' => 'nullable|required_if:send_method,scheduled|date|after:now',
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'exists:devices,id',
        ], [
            'send_method.required' => '送信方法を選択してください',
            'content.required' => '本文を入力してください',
            'content.max' => '本文は1000文字以内で入力してください',
            'title.max' => 'タイトルは50文字以内で入力してください',
            'scheduled_at.required_if' => '予約送信の場合は送信日時を指定してください',
            'scheduled_at.after' => '送信日時は現在時刻より後を指定してください',
            'device_ids.required' => '送信対象を選択してください',
            'device_ids.min' => '送信対象を最低1つ選択してください',
        ]);

        DB::transaction(function () use ($validated) {
            // メッセージ作成
            $message = Message::create([
                'send_method' => $validated['send_method'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'link' => $validated['link'],
                'resource_id' => $validated['resource_id'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => $validated['send_method'] === 'immediate' ? 'pending' : 'draft',
                'created_by' => Auth::id(),
            ]);

            // 送信対象端末を関連付け
            foreach ($validated['device_ids'] as $deviceId) {
                MessageDevice::create([
                    'message_id' => $message->id,
                    'device_id' => $deviceId,
                    'delivery_status' => 'pending',
                ]);
            }

            // 送信処理
            if ($validated['send_method'] === 'immediate') {
                // 即時送信
                $this->sendMessage($message);
            } else {
                // 予約送信 - キューに登録
                SendScheduledMessage::dispatch($message)
                    ->delay($validated['scheduled_at']);
                
                $message->update(['status' => 'scheduled']);
            }
        });

        return redirect()->route('admin.messages.index')
            ->with('success', 'メッセージを作成しました。');
    }

    /**
     * メッセージ編集フォーム
     */
    public function edit(Message $message)
    {
        if (!$message->canEdit()) {
            return redirect()->route('admin.messages.index')
                ->with('error', 'このメッセージは編集できません。');
        }

        $devices = Device::orderBy('name')->get();
        $resources = Resource::where('is_public', true)
            ->where('category', 'image')
            ->orderBy('name')
            ->get();
        
        $selectedDeviceIds = $message->devices->pluck('id')->toArray();

        return view('admin.messages.edit', compact('message', 'devices', 'resources', 'selectedDeviceIds'));
    }

    /**
     * メッセージ更新
     */
    public function update(Request $request, Message $message)
    {
        if (!$message->canEdit()) {
            return redirect()->route('admin.messages.index')
                ->with('error', 'このメッセージは編集できません。');
        }

        $validated = $request->validate([
            'send_method' => 'required|in:immediate,scheduled',
            'title' => 'nullable|max:50',
            'content' => 'required|max:1000',
            'link' => 'nullable|url|max:2000',
            'resource_id' => 'nullable|exists:resources,id',
            'scheduled_at' => 'nullable|required_if:send_method,scheduled|date|after:now',
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'exists:devices,id',
        ]);

        DB::transaction(function () use ($message, $validated) {
            // メッセージ更新
            $message->update([
                'send_method' => $validated['send_method'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'link' => $validated['link'],
                'resource_id' => $validated['resource_id'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => $validated['send_method'] === 'immediate' ? 'pending' : 'draft',
            ]);

            // 既存の送信対象をクリア
            $message->messageDevices()->delete();

            // 新しい送信対象を設定
            foreach ($validated['device_ids'] as $deviceId) {
                MessageDevice::create([
                    'message_id' => $message->id,
                    'device_id' => $deviceId,
                    'delivery_status' => 'pending',
                ]);
            }

            // 送信処理
            if ($validated['send_method'] === 'immediate') {
                // 即時送信
                $this->sendMessage($message);
            } else {
                // 予約送信 - キューに登録
                SendScheduledMessage::dispatch($message)
                    ->delay($validated['scheduled_at']);
                
                $message->update(['status' => 'scheduled']);
            }
        });

        return redirect()->route('admin.messages.index')
            ->with('success', 'メッセージを更新しました。');
    }

    /**
     * メッセージ削除
     */
    public function destroy(Message $message)
    {
        if (!$message->canDelete()) {
            return redirect()->route('admin.messages.index')
                ->with('error', 'このメッセージは削除できません。');
        }

        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'メッセージを削除しました。');
    }

    /**
     * メッセージ再送信
     */
    public function resend(Message $message)
    {
        if (!$message->canSend()) {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'このメッセージは再送信できません。');
        }

        $this->sendMessage($message);

        return redirect()->route('admin.messages.show', $message)
            ->with('success', 'メッセージの再送信を開始しました。');
    }

    /**
     * 個別端末への再送信
     */
    public function resendToDevice(Message $message, Device $device)
    {
        $messageDevice = MessageDevice::where('message_id', $message->id)
            ->where('device_id', $device->id)
            ->first();

        if (!$messageDevice || !$messageDevice->canRetry()) {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'この端末への再送信はできません。');
        }

        $this->sendMessageToDevice($message, $device);

        return redirect()->route('admin.messages.show', $message)
            ->with('success', "端末「{$device->name}」への再送信を開始しました。");
    }

    /**
     * 端末への接続テスト
     */
    public function testConnection(Device $device)
    {
        Log::info("接続テスト開始", [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'device_ip' => $device->ip_address
        ]);

        try {
            // まずWebSocketサーバーの動作確認
            $serverAlive = $this->webSocketService->testConnection($device->ip_address);
            
            if (!$serverAlive) {
                return response()->json([
                    'success' => false,
                    'message' => "WebSocketサーバーが起動していません。"
                ], 400);
            }

            // 特定IPアドレスのクライアント接続チェック
            $clientConnected = $this->webSocketService->checkClientConnection($device->ip_address);
            
            Log::info("接続テスト結果", [
                'device_id' => $device->id,
                'server_alive' => $serverAlive,
                'client_connected' => $clientConnected
            ]);
            
            if ($clientConnected) {
                return response()->json([
                    'success' => true,
                    'message' => "端末「{$device->name}」({$device->ip_address})がWebSocketサーバーに接続されています。"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "端末「{$device->name}」({$device->ip_address})はWebSocketサーバーに接続されていません。",
                    'detail' => 'WebSocketサーバーは動作していますが、該当IPアドレスからの接続が確認できません。'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("接続テストでエラーが発生", [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "接続テストでエラーが発生しました: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * メッセージ送信処理
     */
    private function sendMessage(Message $message)
    {
        $message->update([
            'status' => 'sending',
            'sent_at' => now(),
        ]);

        foreach ($message->devices as $device) {
            $this->sendMessageToDevice($message, $device);
        }

        // 全て送信完了したら状態を更新
        $this->updateMessageStatus($message);
    }

    /**
     * 個別端末への送信処理
     */
    private function sendMessageToDevice(Message $message, Device $device)
    {
        $messageDevice = MessageDevice::where('message_id', $message->id)
            ->where('device_id', $device->id)
            ->first();

        if (!$messageDevice) {
            return;
        }

        try {
            // WebSocket送信処理（仮実装）
            // TODO: 実際のWebSocket送信処理を実装
            $this->sendWebSocketMessage($device, $message);

            $messageDevice->update([
                'delivery_status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            $messageDevice->update([
                'delivery_status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $messageDevice->retry_count + 1,
            ]);
        }
    }

    /**
     * WebSocket送信（実装）
     */
    private function sendWebSocketMessage(Device $device, Message $message)
    {
        // メッセージデータを構築
        $imageUrl = $message->resource ? asset('storage/' . $message->resource->file_path) : null;
        $messageData = $this->webSocketService->buildMessageData(
            $message->title ?? '',
            $message->content,
            $message->link,
            $imageUrl
        );

        try {
            // まずWebSocketで送信を試行
            $success = $this->webSocketService->sendMessageToDevice($device->ip_address, $messageData);
            
            if (!$success) {
                // WebSocketが失敗した場合、HTTPでフォールバック送信
                Log::info("WebSocket送信失敗、HTTPでフォールバック送信を試行", [
                    'device_ip' => $device->ip_address
                ]);
                
                $success = $this->webSocketService->sendMessageViaHttp($device->ip_address, $messageData);
            }

            if (!$success) {
                throw new \Exception("WebSocketとHTTPの両方で送信に失敗しました");
            }

            Log::info("メッセージ送信成功", [
                'device_ip' => $device->ip_address,
                'message_id' => $message->id
            ]);

        } catch (\Exception $e) {
            Log::error("メッセージ送信エラー", [
                'device_ip' => $device->ip_address,
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * 予約メッセージのキャンセル
     */
    public function cancel(Message $message)
    {
        // 予約済みメッセージのみキャンセル可能
        if ($message->status !== 'scheduled') {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'このメッセージはキャンセルできません。');
        }

        $message->update(['status' => 'cancelled']);

        return redirect()->route('admin.messages.show', $message)
            ->with('success', '予約メッセージをキャンセルしました。');
    }

    /**
     * メッセージステータス更新
     */
    private function updateMessageStatus(Message $message)
    {
        $messageDevices = $message->messageDevices;
        $totalCount = $messageDevices->count();
        $sentCount = $messageDevices->where('delivery_status', 'sent')->count();
        $failedCount = $messageDevices->where('delivery_status', 'failed')->count();

        if ($sentCount === $totalCount) {
            $message->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } elseif ($failedCount === $totalCount) {
            $message->update(['status' => 'failed']);
        }
    }
}