@extends('layouts.admin')

@section('title', '端末詳細 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '端末管理', 'url' => route('admin.devices.index')],
        ['label' => '詳細', 'url' => '']
    ];
    
    $pageTitle = '端末詳細';
    $pageDescription = $device->name . ' の詳細情報です。';
    
    $pageActions = [
        [
            'label' => '編集',
            'url' => route('admin.devices.edit', $device),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
        ]
    ];
    
    // 基本情報
    $basicInfo = [
        ['label' => '端末ID', 'value' => $device->device_id ?? 'N/A', 'class' => 'font-mono font-semibold text-blue-600'],
        ['label' => '端末名', 'value' => $device->name, 'class' => 'font-semibold'],
        [
            'label' => '端末種別', 
            'value' => $device->type,
            'badge' => true,
            'badgeClass' => $device->type === 'PC' ? 'bg-blue-100 text-blue-800' : 
                           ($device->type === 'スマートフォン' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')
        ],
        [
            'label' => '利用者', 
            'value' => $device->user_type,
            'badge' => true,
            'badgeClass' => $device->user_type === '選手' ? 'bg-purple-100 text-purple-800' : 
                           ($device->user_type === '競技関係者' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')
        ],
        ['label' => '登録日', 'value' => $device->created_at->format('Y年m月d日')]
    ];
    
    // ネットワーク情報
    $networkInfo = [
        ['label' => 'IPアドレス', 'value' => $device->ip_address, 'class' => 'font-mono'],
        ['label' => 'MACアドレス', 'value' => $device->mac_address, 'class' => 'font-mono'],
        ['label' => '最終更新', 'value' => $device->updated_at->format('Y年m月d日 H:i')]
    ];
    
    // 大会割り当て履歴
    $assignmentHistory = [];
    foreach($device->competitionDevices as $assignment) {
        $assignmentHistory[] = [
            'id' => $assignment->id,
            'data' => [
                $assignment->competition->name,
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' . 
                $assignment->player_number . '</span>',
                $assignment->created_at->format('Y/m/d')
            ]
        ];
    }
@endphp

@section('content')
    <!-- 基本情報 -->
    <x-detail-card 
        title="基本情報"
        :data="$basicInfo" />

    <!-- ネットワーク情報 -->
    <x-detail-card 
        title="ネットワーク情報"
        :data="$networkInfo" />

    <!-- 大会割り当て履歴 -->
    @if($device->competitionDevices->count() > 0)
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">大会割り当て履歴</h3>
            </div>
            <x-data-table 
                :headers="['大会名', '選手番号', '割り当て日']"
                :rows="$assignmentHistory"
                :actions="[]"
                empty-message="大会への割り当て履歴がありません。" />
        </div>
    @endif

    <!-- 操作ボタン -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.devices.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            一覧に戻る
        </a>
        
        <div class="flex space-x-3">
            @if($device->competitionDevices()->exists())
                <span class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    削除不可（大会に割り当て済み）
                </span>
            @else
                <form method="POST" action="{{ route('admin.devices.destroy', $device) }}" class="inline" 
                      onsubmit="return confirm('この端末を削除しますか？削除すると元に戻せません。')">
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