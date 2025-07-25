@extends('layouts.admin')

@section('title', '競技委員一覧 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '大会管理', 'url' => route('admin.competitions.index')],
        ['label' => '競技委員管理', 'url' => route('admin.committee-members.index')]
    ];
    
    $pageTitle = '競技委員一覧';
    $pageDescription = '競技主査・競技委員の基本情報を管理します';
    
    $pageActions = [
        [
            'label' => '新規競技委員登録',
            'url' => route('admin.committee-members.create'),
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
            'placeholder' => '名前、名前ふりがな、所属'
        ]
    ];
    
    // テーブルヘッダー
    $headers = ['名前', '名前ふりがな', '所属', '状態'];
    
    // テーブル行データ
    $rows = [];
    foreach($committeeMembers as $member) {
        $rows[] = [
            'id' => $member->id,
            'data' => [
                '<div class="flex items-center">
                    <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-900">' . e($member->name) . '</div>
                </div>',
                '<div class="text-sm text-gray-600">' . e($member->name_kana) . '</div>',
                '<div class="text-sm text-gray-900">' . e($member->organization ?? '未設定') . '</div>',
                $member->is_active 
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">アクティブ</span>'
                    : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">非アクティブ</span>'
            ]
        ];
    }
    
    // テーブルアクション
    $tableActions = [
        [
            'type' => 'link',
            'label' => '詳細',
            'url' => route('admin.committee-members.show', ':id'),
            'color' => 'blue'
        ],
        [
            'type' => 'link',
            'label' => '編集',
            'url' => route('admin.committee-members.edit', ':id'),
            'color' => 'indigo'
        ],
        [
            'type' => 'form',
            'label' => '削除',
            'url' => route('admin.committee-members.destroy', ':id'),
            'method' => 'DELETE',
            'color' => 'red',
            'confirm' => 'この競技委員を削除しますか？'
        ]
    ];
@endphp

@section('content')
    <!-- 検索・フィルター -->
    <x-search-filter 
        :action="route('admin.committee-members.index')" 
        :fields="$filterFields" />

    <!-- CSV操作ボタン -->
    <div class="mb-4 flex justify-end space-x-2">
        <a href="{{ route('admin.committee-members.export', request()->all()) }}"
           class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition duration-200">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            CSV出力
        </a>
        <button onclick="showImportModal()"
                class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition duration-200">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            CSV取込
        </button>
    </div>

    <!-- 競技委員一覧テーブル -->
    <x-data-table 
        :headers="$headers" 
        :rows="$rows" 
        :actions="$tableActions"
        :pagination="$committeeMembers"
        empty-message="競技委員が登録されていません。" />

    <!-- CSVインポートモーダル -->
    <x-csv-import
        :action="route('admin.committee-members.import')"
        modal-id="importModal"
        title="競技委員CSVインポート"
        :template-columns="['ID', '名前', '名前ふりがな', '所属', '備考', '状態']"
        :template-sample="[
            ['', '山田太郎', 'やまだたろう', '○○工業大学', '競技主査', 'アクティブ'],
            ['', '鈴木花子', 'すずきはなこ', '××技術専門学校', '', 'アクティブ'],
            ['', '田中一郎', 'たなかいちろう', '△△高等学校', '競技委員', '非アクティブ']
        ]"
        note="※ID列は空にしてください。システムが自動的に割り当てます。<br>※状態は「アクティブ」または「非アクティブ」を指定してください。" />

    <script>
        function showImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }
    </script>
@endsection