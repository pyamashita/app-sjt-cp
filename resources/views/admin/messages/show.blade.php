@extends('layouts.admin')

@section('title', 'メッセージ詳細 - SJT-CP')

@php
    $pageTitle = 'メッセージ詳細';
    $pageDescription = 'メッセージの詳細情報と送信状況';
@endphp

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('admin.messages.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $pageDescription }}</p>
            </div>
        </div>
        
        <!-- アクションボタン -->
        <div class="flex flex-wrap gap-3">
            @if($message->canEdit())
                <a href="{{ route('admin.messages.edit', $message) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    編集
                </a>
            @endif
            
            @if($message->canSend())
                <form method="POST" action="{{ route('admin.messages.resend', $message) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('このメッセージを再送信しますか？')"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        再送信
                    </button>
                </form>
            @endif
            
            @if($message->canDelete())
                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('このメッセージを削除しますか？')"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        削除
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- メッセージ情報 -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 基本情報 -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">メッセージ内容</h2>
                <div class="space-y-4">
                    @if($message->title)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
                            <p class="text-gray-900">{{ $message->title }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">本文</label>
                        <div class="bg-gray-50 rounded p-3">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $message->content }}</p>
                        </div>
                    </div>
                    
                    @if($message->link)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">リンク</label>
                            <a href="{{ $message->link }}" 
                               target="_blank" 
                               class="text-blue-600 hover:text-blue-800 underline">
                                {{ $message->link }}
                            </a>
                        </div>
                    @endif
                    
                    @if($message->resource)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">添付画像</label>
                            <div class="mt-2">
                                @if($message->resource->is_image)
                                    <img src="{{ asset('storage/' . $message->resource->file_path) }}" 
                                         alt="{{ $message->resource->name }}"
                                         class="max-w-sm h-auto rounded border">
                                @endif
                                <p class="text-sm text-gray-600 mt-1">{{ $message->resource->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 送信状況 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">送信状況</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    端末名
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IPアドレス
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ステータス
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    送信日時
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($message->messageDevices as $messageDevice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $messageDevice->device->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $messageDevice->device->type }} - {{ $messageDevice->device->user_type }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $messageDevice->device->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($messageDevice->delivery_status)
                                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                                @case('sent') bg-blue-100 text-blue-800 @break
                                                @case('delivered') bg-green-100 text-green-800 @break
                                                @case('failed') bg-red-100 text-red-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ $messageDevice->delivery_status_display }}
                                        </span>
                                        @if($messageDevice->retry_count > 0)
                                            <span class="ml-2 text-xs text-gray-500">
                                                (再試行: {{ $messageDevice->retry_count }}回)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($messageDevice->sent_at)
                                            {{ $messageDevice->sent_at->format('Y/m/d H:i:s') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($messageDevice->canRetry())
                                            <form method="POST" 
                                                  action="{{ route('admin.messages.resend-device', [$message, $messageDevice->device]) }}" 
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('この端末に再送信しますか？')"
                                                        class="text-blue-600 hover:text-blue-900">
                                                    再送信
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($messageDevice->error_message)
                                            <button type="button" 
                                                    onclick="showErrorModal('{{ addslashes($messageDevice->error_message) }}')"
                                                    class="ml-2 text-red-600 hover:text-red-900">
                                                エラー詳細
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- サイドバー -->
        <div class="space-y-6">
            <!-- メッセージ情報 -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">メッセージ情報</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">送信方法</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $message->send_method === 'immediate' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $message->send_method_display }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @switch($message->status)
                                    @case('draft') bg-gray-100 text-gray-800 @break
                                    @case('pending') bg-yellow-100 text-yellow-800 @break
                                    @case('sending') bg-blue-100 text-blue-800 @break
                                    @case('completed') bg-green-100 text-green-800 @break
                                    @case('failed') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ $message->status_display }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">送信対象数</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $message->devices->count() }}台</dd>
                    </div>
                    
                    @if($message->scheduled_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">予約送信日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->scheduled_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                    @endif
                    
                    @if($message->sent_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">送信開始日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->sent_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                    @endif
                    
                    @if($message->completed_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">送信完了日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->completed_at->format('Y年m月d日 H:i') }}</dd>
                        </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成者</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $message->creator->name }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">作成日時</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $message->created_at->format('Y年m月d日 H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 送信統計 -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">送信統計</h2>
                @php
                    $total = $message->messageDevices->count();
                    $pending = $message->messageDevices->where('delivery_status', 'pending')->count();
                    $sent = $message->messageDevices->where('delivery_status', 'sent')->count();
                    $delivered = $message->messageDevices->where('delivery_status', 'delivered')->count();
                    $failed = $message->messageDevices->where('delivery_status', 'failed')->count();
                @endphp
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">総数</span>
                        <span class="text-sm font-medium text-gray-900">{{ $total }}台</span>
                    </div>
                    
                    @if($pending > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-yellow-600">送信待ち</span>
                            <span class="text-sm font-medium text-yellow-900">{{ $pending }}台</span>
                        </div>
                    @endif
                    
                    @if($sent > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-600">送信済み</span>
                            <span class="text-sm font-medium text-blue-900">{{ $sent }}台</span>
                        </div>
                    @endif
                    
                    @if($delivered > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-green-600">配信完了</span>
                            <span class="text-sm font-medium text-green-900">{{ $delivered }}台</span>
                        </div>
                    @endif
                    
                    @if($failed > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-red-600">送信失敗</span>
                            <span class="text-sm font-medium text-red-900">{{ $failed }}台</span>
                        </div>
                    @endif
                </div>
                
                @if($total > 0)
                    <div class="mt-4">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ ($sent + $delivered) / $total * 100 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            成功率: {{ round(($sent + $delivered) / $total * 100, 1) }}%
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- エラー詳細モーダル -->
    <div id="error-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeErrorModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">エラー詳細</h3>
                        <button type="button" onclick="closeErrorModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="bg-red-50 rounded p-3">
                        <p id="error-message" class="text-sm text-red-800"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showErrorModal(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-modal').classList.remove('hidden');
        }

        function closeErrorModal() {
            document.getElementById('error-modal').classList.add('hidden');
        }
    </script>
@endsection