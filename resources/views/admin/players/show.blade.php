@extends('layouts.admin')

@section('title', '選手詳細 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '詳細', 'url' => '']
    ];
    
    $pageTitle = '選手詳細';
    $pageDescription = $player->name . ' の詳細情報';
    
    $pageActions = [
        [
            'label' => '編集',
            'url' => route('admin.players.edit', $player),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
        ]
    ];
    
    // 基本情報
    $basicInfo = [
        ['label' => '選手名', 'value' => $player->name, 'class' => 'font-semibold'],
        ['label' => '都道府県', 'value' => $player->prefecture],
        ['label' => '所属', 'value' => $player->affiliation ?? '未設定'],
        [
            'label' => '性別', 
            'value' => $player->gender_label,
            'badge' => true,
            'badgeClass' => $player->gender === 'male' ? 'bg-blue-100 text-blue-800' : 
                           ($player->gender === 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800')
        ],
        ['label' => '登録日', 'value' => $player->created_at->format('Y年m月d日')],
        ['label' => '最終更新', 'value' => $player->updated_at->format('Y年m月d日 H:i')]
    ];
    
    // 統計情報
    $statsInfo = [
        ['label' => '参加大会数', 'value' => $player->competitionPlayers->count() . '大会', 'highlight' => true]
    ];
    
    // 参加大会履歴
    $competitionHistory = [];
    foreach($player->competitionPlayers as $competitionPlayer) {
        $competitionHistory[] = [
            'id' => $competitionPlayer->id,
            'data' => [
                $competitionPlayer->competition->name,
                $competitionPlayer->competition->period,
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                $competitionPlayer->player_number . '</span>',
                $competitionPlayer->competition->venue
            ]
        ];
    }
@endphp

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- 基本情報 -->
        <div class="lg:col-span-2">
            <x-detail-card 
                title="基本情報"
                :data="$basicInfo" />
        </div>

        <!-- 統計情報 -->
        <div>
            <x-detail-card 
                title="統計情報"
                :data="$statsInfo" />
        </div>
    </div>

    <!-- 参加大会一覧 -->
    @if($player->competitionPlayers->count() > 0)
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">参加大会一覧</h3>
            </div>
            <x-data-table 
                :headers="['大会名', '開催期間', '選手番号', '開催場所']"
                :rows="$competitionHistory"
                :actions="[]"
                empty-message="参加大会がありません。" />
        </div>
    @endif

    <!-- 操作ボタン -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.players.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            一覧に戻る
        </a>
        
        <div class="flex space-x-3">
            @if($player->competitionPlayers()->exists())
                <span class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    削除不可（大会に参加済み）
                </span>
            @else
                <form method="POST" action="{{ route('admin.players.destroy', $player) }}" class="inline" 
                      onsubmit="return confirm('この選手を削除しますか？削除すると元に戻せません。')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        削除
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection