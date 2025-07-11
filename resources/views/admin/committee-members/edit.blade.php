@extends('layouts.admin')

@section('title', '競技委員編集 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '大会管理', 'url' => route('admin.competitions.index')],
        ['label' => '競技委員管理', 'url' => route('admin.committee-members.index')],
        ['label' => '編集', 'url' => '']
    ];
    
    $pageTitle = '競技委員編集';
    $pageDescription = $committeeMember->name . ' の情報を編集します';
@endphp

@section('content')
    <x-form-card 
        title="競技委員情報"
        :action="route('admin.committee-members.update', $committeeMember)"
        :cancel-url="route('admin.committee-members.index')"
        method="PUT"
        submit-label="更新">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="name"
                label="名前"
                type="text"
                :value="old('name', $committeeMember->name)"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="name_kana"
                label="名前ふりがな"
                type="text"
                placeholder="やまだ たろう"
                :value="old('name_kana', $committeeMember->name_kana)"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="organization"
                label="所属"
                type="text"
                placeholder="企業名、団体名など"
                :value="old('organization', $committeeMember->organization)"
                col-span="1" />

            <div class="flex items-center col-span-1">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $committeeMember->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    アクティブ（使用可能状態にする）
                </label>
            </div>

            <x-form-field 
                name="description"
                label="備考"
                type="textarea"
                placeholder="特記事項や説明があれば入力してください"
                :value="old('description', $committeeMember->description)"
                col-span="2" />
        </div>
    </x-form-card>
@endsection