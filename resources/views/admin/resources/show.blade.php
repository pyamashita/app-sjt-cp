@extends('layouts.admin')

@section('title', $resource->name . ' - リソース詳細 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $resource->name }}</h1>
        <p class="mt-2 text-sm text-gray-600">リソース詳細情報</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 基本情報 -->
        <x-detail-card 
            title="基本情報"
            :data="[
                ['label' => 'リソース名', 'value' => $resource->name],
                ['label' => 'ファイル名', 'value' => $resource->original_name],
                ['label' => 'MIMEタイプ', 'value' => $resource->mime_type],
                ['label' => 'ファイルサイズ', 'value' => $resource->getFormattedSize()],
                ['label' => 'カテゴリ', 'value' => $resource->category ? App\Models\Resource::getCategories()[$resource->category] : '-'],
                ['label' => '公開状態', 'value' => $resource->is_public ? '公開' : '非公開', 'badge' => true, 'badgeClass' => $resource->is_public ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'],
                ['label' => '登録日', 'value' => $resource->created_at->format('Y年m月d日 H:i')]
            ]"
        />

        <!-- 説明 -->
        @if($resource->description)
        <x-detail-card 
            title="説明"
            :data="[
                ['label' => '説明', 'value' => $resource->description, 'full_width' => true]
            ]"
        />
        @endif

        <!-- アクション -->
        <x-detail-card 
            title="操作"
            :data="[]"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('admin.resources.download', $resource) }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    ダウンロード
                </a>
                
                <a href="{{ route('admin.resources.edit', $resource) }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    編集
                </a>
            </div>
        </x-detail-card>

        <!-- API情報 -->
        <x-detail-card 
            title="API情報"
            :data="[
                ['label' => 'API URL', 'value' => route('api.resources.show', $resource)],
                ['label' => 'ダウンロード URL', 'value' => route('api.resources.download', $resource)],
                ['label' => 'ストリーミング URL', 'value' => route('api.resources.stream', $resource)]
            ]"
        />
    </div>

    <!-- アクセス制御 -->
    @if(!$resource->is_public)
    <div class="mt-6">
        <x-detail-card 
            title="アクセス制御"
            :data="[]"
        >
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">現在の設定</h4>
                @if($resource->accessControls->count() > 0)
                    <div class="space-y-2">
                        @foreach($resource->accessControls as $control)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                <div>
                                    <span class="text-sm font-medium">
                                        {{ $control->type === 'ip_whitelist' ? 'IP許可' : 'トークン必須' }}
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2">{{ $control->value }}</span>
                                </div>
                                <form method="POST" action="{{ route('admin.resources.access-control.remove', [$resource, $control]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('このアクセス制御を削除してもよろしいですか？')">
                                        削除
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-600">アクセス制御が設定されていません。</p>
                @endif
            </div>

            <div class="border-t pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">新しいアクセス制御を追加</h4>
                <form method="POST" action="{{ route('admin.resources.access-control.add', $resource) }}" class="flex items-end space-x-3">
                    @csrf
                    <div class="flex-1">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">タイプ</label>
                        <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="ip_whitelist">IP許可</option>
                            <option value="token_required">トークン必須</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-1">値</label>
                        <input type="text" 
                               name="value" 
                               id="value"
                               placeholder="IPアドレスまたは設定値"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <button type="submit" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        追加
                    </button>
                </form>
            </div>
        </x-detail-card>
    </div>
    @endif

    <!-- 最近のアクセスログ -->
    @if($resource->accessLogs->count() > 0)
    <div class="mt-6">
        <x-detail-card 
            title="最近のアクセスログ"
            :data="[]"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">アクセス日時</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IPアドレス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">アクセス種別</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ユーザーエージェント</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($resource->accessLogs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->accessed_at->format('Y/m/d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @switch($log->access_type)
                                        @case('download')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">ダウンロード</span>
                                            @break
                                        @case('api_download')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">API ダウンロード</span>
                                            @break
                                        @case('api_stream')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">API ストリーミング</span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $log->access_type }}</span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-detail-card>
    </div>
    @endif
@endsection