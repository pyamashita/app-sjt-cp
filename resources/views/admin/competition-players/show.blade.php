@extends('layouts.admin')

@section('title', '選手割り当て詳細 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '大会選手割当', 'url' => route('admin.competition-players.index')],
        ['label' => '詳細', 'url' => '']
    ];
    
    $pageTitle = '選手割り当て詳細';
    $pageDescription = $competitionPlayer->player->name . ' の大会参加情報';
    
    $pageActions = [
        [
            'label' => '編集',
            'url' => route('admin.competition-players.edit', $competitionPlayer),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
        ]
    ];
    
    // 大会情報
    $competitionInfo = [
        ['label' => '大会名', 'value' => $competitionPlayer->competition->name, 'class' => 'font-semibold'],
        ['label' => '開催期間', 'value' => $competitionPlayer->competition->period],
        ['label' => '開催場所', 'value' => $competitionPlayer->competition->venue],
        ['label' => '競技主査', 'value' => $competitionPlayer->competition->chief_judge ?? '未設定']
    ];
    
    // 選手情報
    $playerInfo = [
        ['label' => '選手名', 'value' => $competitionPlayer->player->name, 'class' => 'font-semibold'],
        ['label' => '都道府県', 'value' => $competitionPlayer->player->prefecture],
        ['label' => '所属', 'value' => $competitionPlayer->player->affiliation ?? '未設定'],
        [
            'label' => '性別', 
            'value' => $competitionPlayer->player->gender_label,
            'badge' => true,
            'badgeClass' => $competitionPlayer->player->gender === 'male' ? 'bg-blue-100 text-blue-800' : 
                           ($competitionPlayer->player->gender === 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800')
        ]
    ];
    
    // 割り当て情報
    $assignmentInfo = [
        [
            'label' => '選手番号', 
            'value' => $competitionPlayer->player_number,
            'badge' => true,
            'badgeClass' => 'bg-blue-100 text-blue-800',
            'highlight' => true
        ],
        ['label' => '割り当て日', 'value' => $competitionPlayer->created_at->format('Y年m月d日 H:i')],
        ['label' => '最終更新', 'value' => $competitionPlayer->updated_at->format('Y年m月d日 H:i')]
    ];
@endphp

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- 大会情報 -->
        <x-detail-card 
            title="大会情報"
            :data="$competitionInfo" />

        <!-- 選手情報 -->
        <x-detail-card 
            title="選手情報"
            :data="$playerInfo" />
    </div>

    <!-- 割り当て情報 -->
    <x-detail-card 
        title="割り当て情報"
        :data="$assignmentInfo" />

    <!-- 操作ボタン -->
    <div class="flex items-center justify-between mt-6">
        <a href="{{ route('admin.competition-players.index', ['competition_id' => $competitionPlayer->competition_id]) }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            一覧に戻る
        </a>
        
        <div class="flex space-x-3">
            <form method="POST" action="{{ route('admin.competition-players.destroy', $competitionPlayer) }}" class="inline" 
                  onsubmit="return confirm('この選手を大会から除外しますか？')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    大会から除外
                </button>
            </form>
        </div>
    </div>
@endsection