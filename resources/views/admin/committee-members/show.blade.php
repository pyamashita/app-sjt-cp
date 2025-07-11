@extends('layouts.admin')

@section('title', '競技委員詳細 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '大会管理', 'url' => route('admin.competitions.index')],
        ['label' => '競技委員管理', 'url' => route('admin.committee-members.index')],
        ['label' => '詳細', 'url' => '']
    ];
    
    $pageTitle = '競技委員詳細';
    $pageDescription = $committeeMember->name . ' の詳細情報';
    
    $pageActions = [
        [
            'label' => '編集',
            'url' => route('admin.committee-members.edit', $committeeMember),
            'type' => 'primary',
            'icon' => '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>'
        ]
    ];
    
    // 基本情報
    $basicInfo = [
        ['label' => '名前', 'value' => $committeeMember->name, 'class' => 'font-semibold'],
        ['label' => '名前ふりがな', 'value' => $committeeMember->name_kana],
        ['label' => '所属', 'value' => $committeeMember->organization ?? '未設定'],
        [
            'label' => '状態', 
            'value' => $committeeMember->is_active ? 'アクティブ' : '非アクティブ',
            'badge' => true,
            'badgeClass' => $committeeMember->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
        ],
        ['label' => '登録日', 'value' => $committeeMember->created_at->format('Y年m月d日')],
        ['label' => '最終更新', 'value' => $committeeMember->updated_at->format('Y年m月d日 H:i')]
    ];
    
    // 備考情報
    $descriptionInfo = [];
    if ($committeeMember->description) {
        $descriptionInfo[] = ['label' => '備考', 'value' => $committeeMember->description, 'multiline' => true];
    }
@endphp

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- 基本情報 -->
        <div>
            <x-detail-card 
                title="基本情報"
                :data="$basicInfo" />
        </div>

        <!-- 備考情報 -->
        @if(!empty($descriptionInfo))
        <div>
            <x-detail-card 
                title="備考・説明"
                :data="$descriptionInfo" />
        </div>
        @endif
    </div>

    <!-- 操作ボタン -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.committee-members.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            一覧に戻る
        </a>
        
        <div class="flex space-x-3">
            <form method="POST" action="{{ route('admin.committee-members.destroy', $committeeMember) }}" class="inline" 
                  onsubmit="return confirm('この競技委員を削除しますか？削除すると元に戻せません。')">
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
        </div>
    </div>
@endsection