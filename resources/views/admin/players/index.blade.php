@extends('layouts.admin')

@section('title', '選手一覧 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')]
    ];
    
    $pageTitle = '選手一覧';
    $pageDescription = '競技選手の基本情報を管理します';
    
    // サブメニューの設定
    $subMenus = [
        [
            'label' => '選手一覧',
            'url' => route('admin.players.index'),
            'active' => true
        ],
        [
            'label' => '大会選手割当',
            'url' => route('admin.competition-players.index'),
            'active' => false
        ]
    ];
    
    $pageActions = [
        [
            'label' => 'CSVエクスポート',
            'url' => route('admin.players.export', request()->all()),
            'type' => 'secondary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
        ],
        [
            'label' => '新規選手登録',
            'url' => route('admin.players.create'),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>'
        ]
    ];
    
    // 検索フィルターフィールド
    $filterFields = [
        [
            'name' => 'search',
            'label' => '検索',
            'type' => 'text',
            'placeholder' => '選手名、都道府県、所属'
        ],
        [
            'name' => 'prefecture',
            'label' => '都道府県',
            'type' => 'select',
            'options' => $prefectures
        ],
        [
            'name' => 'gender',
            'label' => '性別',
            'type' => 'select',
            'options' => $genders
        ]
    ];
    
    // テーブルヘッダー
    $headers = ['選手名', '都道府県', '所属', '性別', '参加大会'];
    
    // テーブル行データ
    $rows = [];
    foreach($players as $player) {
        $rows[] = [
            'id' => $player->id,
            'data' => [
                '<div class="flex items-center">
                    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-900">' . e($player->name) . '</div>
                </div>',
                e($player->prefecture),
                e($player->affiliation ?? '未設定'),
                e($player->gender_label),
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                $player->competitionPlayers->count() . '大会</span>'
            ]
        ];
    }
    
    // テーブルアクション
    $tableActions = [
        [
            'type' => 'link',
            'label' => '詳細',
            'url' => route('admin.players.show', ':id'),
            'color' => 'blue'
        ],
        [
            'type' => 'link',
            'label' => '編集',
            'url' => route('admin.players.edit', ':id'),
            'color' => 'indigo'
        ],
        [
            'type' => 'form',
            'label' => '削除',
            'url' => route('admin.players.destroy', ':id'),
            'method' => 'DELETE',
            'color' => 'red',
            'confirm' => 'この選手を削除しますか？\n※大会に参加している選手は削除できません'
        ]
    ];
@endphp

@section('content')
    <!-- サブメニュー -->
    <div class="bg-white shadow-lg rounded-xl p-4 mb-6">
        <nav class="flex space-x-8">
            @foreach($subMenus as $menu)
                <a href="{{ $menu['url'] }}"
                   class="px-3 py-2 text-sm font-medium {{ $menu['active'] ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600 hover:border-b-2 hover:border-blue-600' }} transition-colors">
                    {{ $menu['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <!-- 検索・フィルター -->
    <x-search-filter 
        :action="route('admin.players.index')" 
        :fields="$filterFields" />

    <!-- CSVインポート -->
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">CSVインポート</h3>
        <form method="POST" action="{{ route('admin.players.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div class="col-span-1">
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-1">CSVファイル</label>
                <input type="file" 
                       id="csv_file"
                       name="csv_file" 
                       accept=".csv,.txt"
                       required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <div class="col-span-1">
                <label for="import_mode" class="block text-sm font-medium text-gray-700 mb-1">インポートモード</label>
                <select id="import_mode"
                        name="import_mode"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="append">追加（既存データを保持）</option>
                    <option value="replace">置換（既存データを削除）</option>
                </select>
            </div>
            <div class="col-span-1 flex items-end">
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200">
                    インポート
                </button>
            </div>
            <div class="col-span-1 flex items-end">
                <p class="text-xs text-gray-500">
                    形式: 選手名,都道府県,所属,性別
                </p>
            </div>
        </form>
    </div>

    <!-- 選手一覧テーブル -->
    <x-data-table 
        :headers="$headers" 
        :rows="$rows" 
        :actions="$tableActions"
        :pagination="$players"
        empty-message="選手が登録されていません。" />
@endsection