@extends('layouts.admin')

@section('title', '大会選手割当 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '大会選手割当', 'url' => '']
    ];
    
    $pageTitle = '大会選手割当';
    $pageDescription = '競技選手の情報と大会への割り当てを管理します';
    
    // サブメニューの設定
    $subMenus = [
        [
            'label' => '選手一覧',
            'url' => route('admin.players.index'),
            'active' => false
        ],
        [
            'label' => '大会選手割当',
            'url' => route('admin.competition-players.index'),
            'active' => true
        ]
    ];
    
    $pageActions = [];
    if($selectedCompetition) {
        $pageActions = [
            [
                'label' => 'CSVエクスポート',
                'url' => route('admin.competition-players.export', array_merge(request()->all(), ['competition_id' => $selectedCompetition->id])),
                'type' => 'secondary',
                'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
            ],
            [
                'label' => '選手番号生成',
                'url' => '#',
                'type' => 'secondary',
                'onclick' => 'openGenerateModal()',
                'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4zM9 6v10h6V6H9z"></path></svg>'
            ],
            [
                'label' => '選手を追加',
                'url' => route('admin.competition-players.create', ['competition_id' => $selectedCompetition->id]),
                'type' => 'primary',
                'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>'
            ]
        ];
    }
    
    // 検索フィルターフィールド
    $filterFields = [
        [
            'name' => 'competition_id',
            'label' => '大会選択',
            'type' => 'select',
            'options' => $competitions->pluck('name', 'id')->toArray(),
            'onchange' => 'this.form.submit()',
            'placeholder' => '大会を選択してください'
        ]
    ];
    
    if($selectedCompetition) {
        $filterFields[] = [
            'name' => 'search',
            'label' => '検索',
            'type' => 'text',
            'placeholder' => '選手名、都道府県、所属、選手番号'
        ];
    }
    
    // テーブルヘッダー
    $headers = ['選手番号', '選手名', '都道府県', '所属', '性別'];
    
    // テーブル行データ
    $rows = [];
    if($selectedCompetition && $competitionPlayers) {
        foreach($competitionPlayers as $competitionPlayer) {
            $rows[] = [
                'id' => $competitionPlayer->id,
                'data' => [
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                    $competitionPlayer->player_number . '</span>',
                    '<div class="flex items-center">
                        <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="text-sm font-medium text-gray-900">' . e($competitionPlayer->player->name) . '</div>
                    </div>',
                    e($competitionPlayer->player->prefecture),
                    e($competitionPlayer->player->affiliation ?? '未設定'),
                    e($competitionPlayer->player->gender_label)
                ]
            ];
        }
    }
    
    // テーブルアクション
    $tableActions = [
        [
            'type' => 'link',
            'label' => '詳細',
            'url' => route('admin.competition-players.show', ':id'),
            'color' => 'blue'
        ],
        [
            'type' => 'link',
            'label' => '編集',
            'url' => route('admin.competition-players.edit', ':id'),
            'color' => 'indigo'
        ],
        [
            'type' => 'form',
            'label' => '削除',
            'url' => route('admin.competition-players.destroy', ':id'),
            'method' => 'DELETE',
            'color' => 'red',
            'confirm' => 'この選手を大会から除外しますか？'
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
        :action="route('admin.competition-players.index')" 
        :fields="$filterFields" />

    @if($selectedCompetition)
        <!-- CSVインポート -->
        <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">CSVインポート</h3>
            <form method="POST" action="{{ route('admin.competition-players.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
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
                        形式: 選手名,都道府県,所属,性別,選手番号
                    </p>
                </div>
            </form>
        </div>

        <!-- 選手一覧テーブル -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $selectedCompetition->name }} - 参加選手一覧
                    @if($competitionPlayers)
                        <span class="text-sm text-gray-500 ml-2">({{ $competitionPlayers->total() }}人)</span>
                    @endif
                </h3>
            </div>
            <x-data-table 
                :headers="$headers" 
                :rows="$rows" 
                :actions="$tableActions"
                :pagination="$competitionPlayers"
                empty-message="参加選手が登録されていません。" />
        </div>
    @else
        <div class="bg-white shadow-lg rounded-xl p-8 text-center">
            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">大会を選択してください</h3>
            <p class="text-gray-600">上記のフィルターから管理する大会を選択してください。</p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
function openGenerateModal() {
    // 選手番号生成モーダルの処理
    alert('選手番号生成機能は実装予定です。');
}
</script>
@endpush