@extends('layouts.admin')

@section('title', '大会実施管理 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => 'スケジュール管理', 'url' => route('admin.competitions.index')]
    ];
    
    $pageTitle = '大会実施管理';
    $pageDescription = '競技大会の基本情報を管理します';
    
    $pageActions = [
        [
            'label' => '新規大会作成',
            'url' => route('admin.competitions.create'),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>'
        ]
    ];
    
    // テーブルヘッダー
    $headers = ['大会名称', '開催期間', '開催場所', '競技主査', '競技日程'];
    
    // テーブル行データ
    $rows = [];
    foreach($competitions as $competition) {
        $rows[] = [
            'id' => $competition->id,
            'data' => [
                '<div class="flex items-center">
                    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-900">' . e($competition->name) . '</div>
                </div>',
                e($competition->period),
                e($competition->venue),
                e($competition->chief_judge ?? '未設定'),
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                $competition->competitionDays->count() . '日程</span>'
            ]
        ];
    }
    
    // テーブルアクション
    $tableActions = [
        [
            'type' => 'link',
            'label' => '詳細',
            'url' => route('admin.competitions.show', ':id'),
            'color' => 'blue'
        ],
        [
            'type' => 'link',
            'label' => '日程管理',
            'url' => route('admin.competitions.competition-days.index', ':id'),
            'color' => 'green'
        ],
        [
            'type' => 'link',
            'label' => '編集',
            'url' => route('admin.competitions.edit', ':id'),
            'color' => 'indigo'
        ],
        [
            'type' => 'form',
            'label' => '削除',
            'url' => route('admin.competitions.destroy', ':id'),
            'method' => 'DELETE',
            'color' => 'red',
            'confirm' => 'この大会を削除しますか？関連する日程や選手情報も削除されます。'
        ]
    ];
@endphp

@section('content')
    <!-- 大会一覧テーブル -->
    <x-data-table 
        :headers="$headers" 
        :rows="$rows" 
        :actions="$tableActions"
        :pagination="$competitions"
        empty-message="大会が登録されていません。" />
@endsection