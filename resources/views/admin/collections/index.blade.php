@extends('layouts.admin')

@section('title', 'コレクション一覧 - SJT-CP')

@php
    $pageTitle = 'コレクション一覧';
    $pageDescription = 'データコレクションの管理';
    $pageActions = [
        [
            'label' => '新規作成',
            'url' => route('admin.collections.create'),
            'type' => 'primary',
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>'
        ]
    ];
@endphp

@section('content')
    <!-- フィルターと検索 -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <form method="GET" action="{{ route('admin.collections.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">検索</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="コレクション名、表示名、備考で検索"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    検索
                </button>
                @if(request()->hasAny(['search']))
                    <a href="{{ route('admin.collections.index') }}" class="ml-2 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        クリア
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- コレクション一覧 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($collections->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                コレクション情報
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                管理設定
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                アクセス制限
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                登録日
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                操作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($collections as $collection)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $collection->display_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ $collection->name }}
                                        </div>
                                        @if($collection->description)
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ Str::limit($collection->description, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        @if($collection->is_competition_managed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                大会ごと管理
                                            </span>
                                        @endif
                                        @if($collection->is_player_managed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                選手ごと管理
                                            </span>
                                        @endif
                                        @if(!$collection->is_competition_managed && !$collection->is_player_managed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                統一管理
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($collection->accessControls->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $collection->accessControls->count() }}件の制限
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            制限なし
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $collection->created_at->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.collections.show', $collection) }}" 
                                           class="text-blue-600 hover:text-blue-900">詳細</a>
                                        <a href="{{ route('admin.collections.edit', $collection) }}" 
                                           class="text-green-600 hover:text-green-900">編集</a>
                                        <form method="POST" action="{{ route('admin.collections.destroy', $collection) }}" 
                                              class="inline" onsubmit="return confirm('このコレクションを削除しますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $collections->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">コレクションがありません</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'year']))
                        検索条件に一致するコレクションが見つかりませんでした。
                    @else
                        最初のコレクションを作成しましょう。
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'year']))
                    <div class="mt-6">
                        <a href="{{ route('admin.collections.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            新規作成
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection