@extends('layouts.admin')

@section('title', 'コンテンツ管理 - ' . $collection->display_name . ' - SJT-CP')

@php
    $pageTitle = $collection->display_name . ' - コンテンツ管理';
    $pageDescription = 'コレクションコンテンツの一覧・管理';
    $pageActions = [
        [
            'label' => 'CSVエクスポート',
            'url' => route('admin.collections.contents.export', array_merge(['collection' => $collection], request()->only(['competition_id', 'player_id']))),
            'type' => 'secondary',
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>'
        ],
        [
            'label' => 'コンテンツ入力',
            'url' => route('admin.collections.contents.create', $collection),
            'type' => 'primary',
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>'
        ]
    ];
    $breadcrumbs = [
        ['label' => 'コレクション一覧', 'url' => route('admin.collections.index')],
        ['label' => $collection->display_name, 'url' => route('admin.collections.show', $collection)],
        ['label' => 'コンテンツ管理', 'url' => '']
    ];
@endphp

@section('content')
    <!-- 管理設定の表示 -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">管理設定</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl mb-2">
                    @if($collection->is_competition_managed)
                        <span class="text-green-500">✓</span>
                    @else
                        <span class="text-gray-400">✗</span>
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-900">大会ごと管理</div>
            </div>
            
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl mb-2">
                    @if($collection->is_player_managed)
                        <span class="text-green-500">✓</span>
                    @else
                        <span class="text-gray-400">✗</span>
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-900">選手ごと管理</div>
            </div>
            
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="text-2xl mb-2">
                    <span class="text-blue-500">{{ isset($groupedContents) ? $groupedContents->count() : 0 }}</span>
                </div>
                <div class="text-sm font-medium text-gray-900">グループ数</div>
            </div>
        </div>
    </div>

    <!-- フィルターと検索 -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <form method="GET" action="{{ route('admin.collections.contents.index', $collection) }}" class="flex flex-wrap gap-4">
            @if($collection->is_competition_managed)
                <div class="w-64">
                    <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-1">大会</label>
                    <select name="competition_id" id="competition_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">すべての大会</option>
                        @foreach($competitions as $competition)
                            <option value="{{ $competition->id }}" {{ request('competition_id') == $competition->id ? 'selected' : '' }}>
                                {{ $competition->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            @if($collection->is_player_managed)
                <div class="w-64">
                    <label for="player_id" class="block text-sm font-medium text-gray-700 mb-1">選手</label>
                    <select name="player_id" id="player_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">すべての選手</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}" {{ request('player_id') == $player->id ? 'selected' : '' }}>
                                {{ $player->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    フィルタ
                </button>
                @if(request()->hasAny(['competition_id', 'player_id']))
                    <a href="{{ route('admin.collections.contents.index', $collection) }}" class="ml-2 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        クリア
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- コンテンツ一覧 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(isset($groupedContents) && $groupedContents->count() > 0)
            @foreach($groupedContents as $group)
                <!-- グループヘッダー -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">
                                @if($collection->is_player_managed)
                                    {{ $group['key'] }}
                                    @if($group['player'] && $group['competition'])
                                        <span class="text-sm text-gray-500 ml-2">
                                            @php
                                                $competitionPlayer = \App\Models\CompetitionPlayer::where('competition_id', $group['competition']->id)
                                                    ->where('player_id', $group['player']->id)
                                                    ->first();
                                            @endphp
                                            @if($competitionPlayer && $competitionPlayer->player_number)
                                                ({{ $competitionPlayer->player_number }})
                                            @endif
                                        </span>
                                    @endif
                                @else
                                    {{ $group['key'] }}
                                @endif
                            </h4>
                            @if($group['competition'])
                                <p class="text-sm text-gray-500">{{ $group['competition']->name }}</p>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.collections.contents.create', array_merge(['collection' => $collection], $group['competition'] ? ['competition_id' => $group['competition']->id] : [], $group['player'] ? ['player_id' => $group['player']->id] : [])) }}" 
                               class="text-green-600 hover:text-green-900 text-sm font-medium">編集</a>
                            <button type="button" onclick="deleteContent('{{ $group['competition'] ? $group['competition']->id : '' }}', '{{ $group['player'] ? $group['player']->id : '' }}')" 
                                    class="text-red-600 hover:text-red-900 text-sm font-medium">削除</button>
                        </div>
                    </div>
                </div>

                <!-- グループ内のコンテンツ -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($group['items'] as $item)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-2">
                                    <h5 class="text-sm font-medium text-gray-900">{{ $item->field->name }}</h5>
                                    <span class="text-xs text-gray-500">{{ $item->field->content_type_display_name }}</span>
                                </div>
                                <div class="text-sm text-gray-700 mb-2">
                                    <div class="break-words" title="{{ $item->value }}">
                                        {{ $item->formatted_value }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $item->updated_at->format('Y/m/d H:i') }}
                                </div>
                            </div>
                        @endforeach
                        
                        @if($group['items']->count() === 0)
                            <div class="col-span-full text-center py-4">
                                <p class="text-sm text-gray-500">このグループにはコンテンツがありません</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">コンテンツがありません</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['competition_id', 'player_id']))
                        フィルタ条件に一致するコンテンツが見つかりませんでした。
                    @else
                        最初のコンテンツを入力しましょう。
                    @endif
                </p>
                @if(!request()->hasAny(['competition_id', 'player_id']))
                    <div class="mt-6">
                        <a href="{{ route('admin.collections.contents.create', $collection) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            コンテンツ入力
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

@push('scripts')
<script>
const collectionId = {{ $collection->id }};

function deleteContent(competitionId, playerId) {
    if (!confirm('このコンテンツを削除しますか？')) {
        return;
    }
    
    const params = new URLSearchParams();
    if (competitionId && competitionId !== 'null') {
        params.append('competition_id', competitionId);
    }
    if (playerId && playerId !== 'null') {
        params.append('player_id', playerId);
    }
    
    fetch(`/sjt-cp-admin/collections/${collectionId}/contents?${params.toString()}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '削除に失敗しました。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('通信エラーが発生しました。');
    });
}
</script>
@endpush
@endsection