@extends('layouts.admin')

@section('title', 'メッセージ一覧 - SJT-CP')

@php
    $pageTitle = 'メッセージ一覧';
    $pageDescription = '送信したメッセージと予約されているメッセージの管理';
@endphp

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $pageDescription }}</p>
        </div>
        <a href="{{ route('admin.messages.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            新規メッセージ
        </a>
    </div>

    <!-- 検索・フィルター -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">検索</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="タイトル・本文で検索"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">すべて</option>
                    @foreach(\App\Models\Message::getStatuses() as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="send_method" class="block text-sm font-medium text-gray-700 mb-1">送信方法</label>
                <select name="send_method" id="send_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">すべて</option>
                    @foreach(\App\Models\Message::getSendMethods() as $value => $label)
                        <option value="{{ $value }}" {{ request('send_method') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    検索
                </button>
                <a href="{{ route('admin.messages.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    クリア
                </a>
            </div>
        </form>
    </div>

    <!-- メッセージ一覧 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($messages->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                タイトル・本文
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                送信方法
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ステータス
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                送信対象
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
                        @foreach($messages as $message)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="max-w-xs">
                                        @if($message->title)
                                            <div class="text-sm font-medium text-gray-900 truncate">
                                                {{ $message->title }}
                                            </div>
                                        @endif
                                        <div class="text-sm text-gray-600 truncate">
                                            {{ $message->content_preview }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $message->send_method === 'immediate' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $message->send_method_display }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $message->devices->count() }}台
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($message->send_method === 'scheduled' && $message->scheduled_at)
                                        {{ $message->scheduled_at->format('Y/m/d H:i') }}
                                    @elseif($message->sent_at)
                                        {{ $message->sent_at->format('Y/m/d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.messages.show', $message) }}" 
                                           class="text-blue-600 hover:text-blue-900">詳細</a>
                                        
                                        @if($message->canEdit())
                                            <a href="{{ route('admin.messages.edit', $message) }}" 
                                               class="text-green-600 hover:text-green-900">編集</a>
                                        @endif
                                        
                                        @if($message->canDelete())
                                            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('このメッセージを削除しますか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    削除
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- ページネーション -->
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $messages->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">メッセージがありません</h3>
                <p class="mt-1 text-sm text-gray-500">新しいメッセージを作成して端末にメッセージを送信しましょう。</p>
                <div class="mt-6">
                    <a href="{{ route('admin.messages.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        新規メッセージ作成
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection