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
use App\Jobs\SendMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

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
            ->get()
            ->map(function ($resource) {
                $resource->url = route('admin.resources.serve', $resource);
                return $resource;
            });

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
            'target_type' => 'required|in:broadcast,individual,group',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:devices,id',
        ], [
            'send_method.required' => '送信方法を選択してください',
            'content.required' => '本文を入力してください',
            'content.max' => '本文は1000文字以内で入力してください',
            'title.max' => 'タイトルは50文字以内で入力してください',
            'scheduled_at.required_if' => '予約送信の場合は送信日時を指定してください',
            'scheduled_at.after' => '送信日時は現在時刻より後を指定してください',
            'target_type.required' => '送信対象タイプを選択してください',
        ]);

        // 送信対象別のバリデーション
        if ($validated['target_type'] === 'individual') {
            $request->validate([
                'device_ids' => 'required|array|size:1',
            ], [
                'device_ids.required' => '個別送信の場合は端末を1つ選択してください',
                'device_ids.size' => '個別送信の場合は端末を1つだけ選択してください',
            ]);
        } elseif ($validated['target_type'] === 'group') {
            $request->validate([
                'device_ids' => 'required|array|min:2',
            ], [
                'device_ids.required' => 'グループ送信の場合は端末を選択してください',
                'device_ids.min' => 'グループ送信の場合は端末を2つ以上選択してください',
            ]);
        }

        DB::transaction(function () use ($validated) {
            // メッセージ作成
            $message = Message::create([
                'send_method' => $validated['send_method'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'link' => $validated['link'],
                'resource_id' => $validated['resource_id'],
                'target_type' => $validated['target_type'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => $validated['send_method'] === 'immediate' ? 'pending' : 'draft',
                'created_by' => Auth::id(),
            ]);

            // 送信対象端末を関連付け（ブロードキャスト以外の場合）
            if ($validated['target_type'] !== 'broadcast' && !empty($validated['device_ids'])) {
                foreach ($validated['device_ids'] as $deviceId) {
                    MessageDevice::create([
                        'message_id' => $message->id,
                        'device_id' => $deviceId,
                        'delivery_status' => 'pending',
                    ]);
                }
            }

            // 送信処理
            if ($validated['send_method'] === 'immediate') {
                // 即時送信 - キューに登録（遅延なし）
                SendMessage::dispatch($message->id);
                $message->update(['status' => 'pending']);
            } else {
                // 予約送信 - キューに登録
                $scheduledAt = Carbon::parse($validated['scheduled_at']);
                SendMessage::dispatch($message->id)
                    ->delay($scheduledAt);
                
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
            'target_type' => 'required|in:broadcast,individual,group',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        // 送信対象別のバリデーション（更新時も同じ）
        if ($validated['target_type'] === 'individual') {
            $request->validate([
                'device_ids' => 'required|array|size:1',
            ], [
                'device_ids.required' => '個別送信の場合は端末を1つ選択してください',
                'device_ids.size' => '個別送信の場合は端末を1つだけ選択してください',
            ]);
        } elseif ($validated['target_type'] === 'group') {
            $request->validate([
                'device_ids' => 'required|array|min:2',
            ], [
                'device_ids.required' => 'グループ送信の場合は端末を選択してください',
                'device_ids.min' => 'グループ送信の場合は端末を2つ以上選択してください',
            ]);
        }

        DB::transaction(function () use ($message, $validated) {
            // メッセージ更新
            $message->update([
                'send_method' => $validated['send_method'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'link' => $validated['link'],
                'resource_id' => $validated['resource_id'],
                'target_type' => $validated['target_type'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => $validated['send_method'] === 'immediate' ? 'pending' : 'draft',
            ]);

            // 既存の送信対象をクリア
            $message->messageDevices()->delete();

            // 新しい送信対象を設定（ブロードキャスト以外の場合）
            if ($validated['target_type'] !== 'broadcast' && !empty($validated['device_ids'])) {
                foreach ($validated['device_ids'] as $deviceId) {
                    MessageDevice::create([
                        'message_id' => $message->id,
                        'device_id' => $deviceId,
                        'delivery_status' => 'pending',
                    ]);
                }
            }

            // 送信処理
            if ($validated['send_method'] === 'immediate') {
                // 即時送信 - キューに登録（遅延なし）
                SendMessage::dispatch($message->id);
                $message->update(['status' => 'pending']);
            } else {
                // 予約送信 - キューに登録
                $scheduledAt = Carbon::parse($validated['scheduled_at']);
                SendMessage::dispatch($message->id)
                    ->delay($scheduledAt);
                
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

        // 予約メッセージまたは送信待ちメッセージの場合、キューからジョブを削除
        if (in_array($message->status, ['scheduled', 'pending'])) {
            $this->removeJobFromQueue($message->id);
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
            // svr-sjt-wsサーバーの健康状態チェック
            $serverHealthy = $this->webSocketService->checkServerHealth();
            
            if (!$serverHealthy) {
                return response()->json([
                    'success' => false,
                    'message' => "svr-sjt-ws WebSocketサーバーが起動していません。"
                ], 400);
            }

            // WebSocketサーバーから接続中のクライアント一覧を取得
            Log::info("WebSocketサーバーからクライアント一覧取得開始", [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'device_websocket_id' => $device->device_id,
                'device_ip' => $device->ip_address
            ]);
            
            $clientsData = $this->webSocketService->getConnectedClients();
            
            // 端末IDまたはIPアドレスで該当端末の接続を確認
            $deviceConnected = false;
            $connectionInfo = null;
            
            if (isset($clientsData['clients']) && is_array($clientsData['clients'])) {
                foreach ($clientsData['clients'] as $client) {
                    // 端末IDまたはIPアドレスでマッチング
                    if (($device->device_id && isset($client['id']) && $client['id'] === $device->device_id) ||
                        ($device->ip_address && isset($client['ip']) && $client['ip'] === $device->ip_address)) {
                        $deviceConnected = true;
                        $connectionInfo = $client;
                        break;
                    }
                }
            }
            
            Log::info("svr-sjt-ws接続テスト結果", [
                'device_id' => $device->id,
                'device_websocket_id' => $device->device_id,
                'device_ip' => $device->ip_address,
                'server_healthy' => $serverHealthy,
                'total_clients' => $clientsData['total_count'] ?? 0,
                'connected_clients' => $clientsData['connected_count'] ?? 0,
                'device_connected' => $deviceConnected,
                'connection_info' => $connectionInfo
            ]);
            
            if ($deviceConnected && $connectionInfo) {
                return response()->json([
                    'success' => true,
                    'message' => "端末「{$device->name}」({$device->device_id})がsvr-sjt-ws WebSocketサーバーに接続されています。",
                    'connection_info' => $connectionInfo,
                    'server_info' => [
                        'total_clients' => $clientsData['total_count'] ?? 0,
                        'connected_clients' => $clientsData['connected_count'] ?? 0
                    ]
                ]);
            } else {
                $deviceIdentifier = $device->device_id ?: $device->ip_address;
                return response()->json([
                    'success' => false,
                    'message' => "端末「{$device->name}」({$deviceIdentifier})はsvr-sjt-ws WebSocketサーバーに接続されていません。",
                    'detail' => 'svr-sjt-ws WebSocketサーバーは動作していますが、該当端末からの接続が確認できません。',
                    'server_status' => 'healthy',
                    'server_info' => [
                        'total_clients' => $clientsData['total_count'] ?? 0,
                        'connected_clients' => $clientsData['connected_count'] ?? 0
                    ]
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

        if ($message->target_type === 'broadcast') {
            // ブロードキャスト：一度だけ送信
            try {
                $this->sendWebSocketMessage(null, $message);
                $message->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            } catch (\Exception $e) {
                $message->update(['status' => 'failed']);
            }
        } else {
            // 個別・グループ：各端末に送信
            foreach ($message->devices as $device) {
                $this->sendMessageToDevice($message, $device);
            }
            // 全て送信完了したら状態を更新
            $this->updateMessageStatus($message);
        }
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
            // WebSocket送信処理
            if ($message->target_type === 'group') {
                // グループ送信の場合は一度だけ送信（最初の端末でのみ実行）
                $firstDevice = $message->devices->first();
                if ($device->id === $firstDevice->id) {
                    $this->sendWebSocketMessage($device, $message);
                    // 全ての端末の配信ステータスを更新
                    foreach ($message->messageDevices as $messageDeviceItem) {
                        $messageDeviceItem->update([
                            'delivery_status' => 'sent',
                            'sent_at' => now(),
                        ]);
                    }
                }
            } else {
                // 個別送信の場合
                $this->sendWebSocketMessage($device, $message);
                $messageDevice->update([
                    'delivery_status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            if ($message->target_type === 'group') {
                // グループ送信でエラーの場合、全ての端末を失敗扱いにする
                foreach ($message->messageDevices as $messageDeviceItem) {
                    $messageDeviceItem->update([
                        'delivery_status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'retry_count' => $messageDeviceItem->retry_count + 1,
                    ]);
                }
            } else {
                $messageDevice->update([
                    'delivery_status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => $messageDevice->retry_count + 1,
                ]);
            }
        }
    }

    /**
     * WebSocket送信（svr-sjt-ws仕様対応）
     */
    private function sendWebSocketMessage(?Device $device, Message $message)
    {
        try {
            // svr-sjt-ws仕様のnotificationメッセージを構築
            $level = 'info'; // デフォルトレベル
            
            // タイトルまたは内容からレベルを推測
            $content = strtolower($message->title . ' ' . $message->content);
            if (strpos($content, 'エラー') !== false || strpos($content, '失敗') !== false) {
                $level = 'error';
            } elseif (strpos($content, '警告') !== false || strpos($content, '注意') !== false) {
                $level = 'warning';
            } elseif (strpos($content, '完了') !== false || strpos($content, '成功') !== false) {
                $level = 'success';
            }

            // 画像がある場合は画像を優先、なければリンクを使用
            $actionTarget = null;
            $actionType = null;
            
            if ($message->resource && $message->resource->is_image) {
                $actionTarget = route('admin.resources.serve', $message->resource);
                $actionType = 'image';
            } elseif ($message->link) {
                $actionTarget = $message->link;
                $actionType = 'url';
            }

            $messageData = $this->webSocketService->buildNotificationMessage(
                $message->title ?? 'お知らせ',
                $message->content,
                $level,
                $actionTarget,
                5000
            );

            // actionのtypeを正しく設定
            if ($actionTarget && $actionType) {
                $messageData['data']['action']['type'] = $actionType;
            }

            // 送信対象タイプに応じてターゲットIDを設定
            $targetIds = null;
            
            if ($message->target_type === 'broadcast') {
                // ブロードキャスト：targetIds を null にする
                $targetIds = null;
            } elseif ($message->target_type === 'individual' && $device) {
                // 個別送信：単一端末のID
                if (!empty($device->device_id)) {
                    $targetIds = [$device->device_id];
                }
            } elseif ($message->target_type === 'group') {
                // グループ送信：複数端末のID
                $deviceIds = $message->devices->pluck('device_id')->filter()->toArray();
                if (!empty($deviceIds)) {
                    $targetIds = $deviceIds;
                }
            }

            // WebSocketで送信（新しいsvr-sjt-ws仕様）
            $success = $this->webSocketService->sendWebSocketMessage($messageData, $targetIds);
            
            if (!$success) {
                throw new \Exception("svr-sjt-ws WebSocketサーバーへのメッセージ送信に失敗しました");
            }

            Log::info("svr-sjt-ws仕様でのメッセージ送信成功", [
                'target_type' => $message->target_type,
                'device_id' => $device->device_id ?? 'broadcast',
                'device_ip' => $device->ip_address ?? 'broadcast',
                'message_id' => $message->id,
                'websocket_message_id' => $messageData['metadata']['message_id'],
                'event_type' => $messageData['event_type'],
                'target_ids' => $targetIds
            ]);

        } catch (\Exception $e) {
            Log::error("svr-sjt-ws仕様でのメッセージ送信エラー", [
                'target_type' => $message->target_type,
                'device_id' => $device->device_id ?? 'broadcast',
                'device_ip' => $device->ip_address ?? 'broadcast',
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
        // 予約済みまたは送信待ちメッセージのみキャンセル可能
        if (!in_array($message->status, ['scheduled', 'pending'])) {
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'このメッセージはキャンセルできません。');
        }

        // キューからジョブを削除
        $this->removeJobFromQueue($message->id);

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

    /**
     * メッセージステータスを取得（Ajax用）
     */
    public function status(Message $message)
    {
        return response()->json([
            'id' => $message->id,
            'status' => $message->status,
            'status_display' => $message->status_display,
            'sent_at' => $message->sent_at?->format('Y-m-d H:i:s'),
            'completed_at' => $message->completed_at?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * メッセージに関連するキューを削除
     */
    private function removeJobFromQueue($messageId)
    {
        try {
            // データベースキューから該当ジョブを削除
            // SendMessageでかつ該当messageIdを含むジョブを削除
            $deletedCount = DB::table('jobs')
                ->where('payload', 'LIKE', "%SendMessage%")
                ->where('payload', 'LIKE', "%{$messageId}%")
                ->delete();
            
            if ($deletedCount > 0) {
                Log::info("メッセージID {$messageId} に関連する {$deletedCount} 件のキュージョブを削除しました。");
            } else {
                Log::info("メッセージID {$messageId} に関連するキュージョブは見つかりませんでした（既に実行済みまたは存在しない）。");
            }
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error("メッセージID {$messageId} のキュージョブ削除中にエラーが発生しました: " . $e->getMessage());
            return 0;
        }
    }
}