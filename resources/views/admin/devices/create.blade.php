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