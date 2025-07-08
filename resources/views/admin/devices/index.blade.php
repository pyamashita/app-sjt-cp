@extends('layouts.admin')

@section('title', '端末一覧 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '端末管理', 'url' => route('admin.devices.index')]
    ];
    
    $pageTitle = '端末一覧';
    $pageDescription = 'システムで管理している端末の一覧です。';
    
    $pageActions = [
        [
            'label' => 'CSVエクスポート',
            'url' => route('admin.devices.export'),
            'type' => 'secondary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
        ],
        [
            'label' => '新規登録',
            'url' => route('admin.devices.create'),
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
            'placeholder' => '端末名、IPアドレス、MACアドレス'
        ],
        [
            'name' => 'type',
            'label' => '端末種別',
            'type' => 'select',
            'options' => \App\Models\Device::getTypes()
        ],
        [
            'name' => 'user_type',
            'label' => '利用者',
            'type' => 'select',
            'options' => \App\Models\Device::getUserTypes()
        ]
    ];
    
    // テーブルヘッダー
    $headers = ['端末名', '端末種別', '利用者', 'IPアドレス', 'MACアドレス', '登録日'];
    
    // テーブル行データ
    $rows = [];
    foreach($devices as $device) {
        $rows[] = [
            'id' => $device->id,
            'data' => [
                '<div class="flex items-center">
                    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-sm font-medium text-gray-900">' . e($device->name) . '</div>
                </div>',
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                ($device->type === 'PC' ? 'bg-blue-100 text-blue-800' : 
                 ($device->type === 'スマートフォン' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) . '">' . 
                e($device->type) . '</span>',
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                ($device->user_type === '選手' ? 'bg-purple-100 text-purple-800' : 
                 ($device->user_type === '競技関係者' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) . '">' . 
                e($device->user_type) . '</span>',
                $device->ip_address ?? '-',
                '<span class="font-mono">' . ($device->mac_address ?? '-') . '</span>',
                $device->created_at->format('Y/m/d')
            ]
        ];
    }
    
    // テーブルアクション
    $tableActions = [
        [
            'type' => 'link',
            'label' => '詳細',
            'url' => route('admin.devices.show', ':id'),
            'color' => 'blue'
        ],
        [
            'type' => 'link',
            'label' => '編集',
            'url' => route('admin.devices.edit', ':id'),
            'color' => 'indigo'
        ],
        [
            'type' => 'form',
            'label' => '削除',
            'url' => route('admin.devices.destroy', ':id'),
            'method' => 'DELETE',
            'color' => 'red',
            'confirm' => 'この端末を削除しますか？'
        ]
    ];
@endphp

@section('content')
    <!-- 検索・フィルター -->
    <x-search-filter 
        :action="route('admin.devices.index')" 
        :fields="$filterFields" />

    <!-- CSVインポート -->
    <x-csv-import 
        :action="route('admin.devices.import')" 
        format="端末名,端末種別,利用者,IPアドレス,MACアドレス" />

    <!-- 端末一覧テーブル -->
    <x-data-table 
        :headers="$headers" 
        :rows="$rows" 
        :actions="$tableActions"
        :pagination="$devices"
        empty-message="端末が登録されていません。" />
@endsection