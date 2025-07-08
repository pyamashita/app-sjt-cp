@extends('layouts.admin')

@section('title', '選手登録 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '新規登録', 'url' => '']
    ];
    
    $pageTitle = '選手登録';
    $pageDescription = '新しい選手の基本情報を登録します';
@endphp

@section('content')
    <x-form-card 
        title="選手情報"
        :action="route('admin.players.store')"
        :cancel-url="route('admin.players.index')"
        submit-label="登録">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="name"
                label="選手名"
                type="text"
                :value="old('name')"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="prefecture"
                label="都道府県"
                type="select"
                :options="$prefectures"
                placeholder="選択してください"
                :value="old('prefecture')"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="affiliation"
                label="所属"
                type="text"
                placeholder="学校名、企業名など"
                :value="old('affiliation')"
                col-span="1" />

            <x-form-field 
                name="gender"
                label="性別"
                type="select"
                :options="$genders"
                placeholder="選択してください"
                :value="old('gender')"
                :required="true"
                col-span="1" />
        </div>
    </x-form-card>
@endsection