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
            'label' => 'CSVエクスポート',
            'url' => route('admin.committee-members.export', request()->all()),
            'type' => 'secondary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
        ],
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

    <!-- 競技委員一覧テーブル -->
    <x-data-table 
        :headers="$headers" 
        :rows="$rows" 
        :actions="$tableActions"
        :pagination="$committeeMembers"
        empty-message="競技委員が登録されていません。" />
@endsection