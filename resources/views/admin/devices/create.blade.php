@extends('layouts.admin')

@section('title', '端末登録 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '端末管理', 'url' => route('admin.devices.index')],
        ['label' => '新規登録', 'url' => '']
    ];
    
    $pageTitle = '端末登録';
    $pageDescription = '新しい端末を登録してください。';
@endphp

@section('content')
    <x-form-card 
        title="端末情報"
        :action="route('admin.devices.store')"
        :cancel-url="route('admin.devices.index')"
        submit-label="登録">
        
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        端末IDは登録時に自動で「TML-xxxx」形式で設定されます。
                    </p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="name"
                label="端末名"
                type="text"
                placeholder="例：PC-001、タブレット-A"
                :required="true"
                col-span="2" />

            <x-form-field 
                name="type"
                label="端末種別"
                type="select"
                :options="['PC' => 'PC', 'スマートフォン' => 'スマートフォン', 'その他' => 'その他']"
                placeholder="選択してください"
                :required="true" />

            <x-form-field 
                name="user_type"
                label="利用者"
                type="select"
                :options="['選手' => '選手', '競技関係者' => '競技関係者', 'ネットワーク' => 'ネットワーク']"
                placeholder="選択してください"
                :required="true" />

            <x-form-field 
                name="ip_address"
                label="IPアドレス"
                type="text"
                placeholder="例：192.168.1.100"
                help-text="IPv4形式で入力してください（任意）" />

            <x-form-field 
                name="mac_address"
                label="MACアドレス"
                type="text"
                placeholder="例：AA:BB:CC:DD:EE:FF"
                help-text="コロン区切りで入力してください（任意）" />
        </div>
    </x-form-card>
@endsection