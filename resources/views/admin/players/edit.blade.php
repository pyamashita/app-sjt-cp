@extends('layouts.admin')

@section('title', '選手編集 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '編集', 'url' => '']
    ];
    
    $pageTitle = '選手編集';
    $pageDescription = $player->name . ' の情報を編集します';
@endphp

@section('content')
    <x-form-card 
        title="選手情報"
        :action="route('admin.players.update', $player)"
        :cancel-url="route('admin.players.index')"
        method="PUT"
        submit-label="更新">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="name"
                label="選手名"
                type="text"
                :value="old('name', $player->name)"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="prefecture"
                label="都道府県"
                type="select"
                :options="$prefectures"
                placeholder="選択してください"
                :value="old('prefecture', $player->prefecture)"
                :required="true"
                col-span="1" />

            <x-form-field 
                name="affiliation"
                label="所属"
                type="text"
                placeholder="学校名、企業名など"
                :value="old('affiliation', $player->affiliation)"
                col-span="1" />

            <x-form-field 
                name="gender"
                label="性別"
                type="select"
                :options="$genders"
                placeholder="選択してください"
                :value="old('gender', $player->gender)"
                :required="true"
                col-span="1" />
        </div>
    </x-form-card>
@endsection