@extends('layouts.admin')

@section('title', '選手番号編集 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '大会選手割当', 'url' => route('admin.competition-players.index')],
        ['label' => '編集', 'url' => '']
    ];
    
    $pageTitle = '選手番号編集';
    $pageDescription = $competitionPlayer->player->name . ' の選手番号を編集します';
@endphp

@section('content')
    <x-form-card 
        title="選手番号編集"
        :action="route('admin.competition-players.update', $competitionPlayer)"
        :cancel-url="route('admin.competition-players.index', ['competition_id' => $competitionPlayer->competition_id])"
        method="PUT"
        submit-label="更新">
        
        <!-- 大会・選手情報表示 -->
        <div class="col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h4 class="text-lg font-medium text-blue-900 mb-2">割り当て情報</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-700">大会名:</span>
                    <span class="text-blue-900">{{ $competitionPlayer->competition->name }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">開催期間:</span>
                    <span class="text-blue-900">{{ $competitionPlayer->competition->period }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">選手名:</span>
                    <span class="text-blue-900">{{ $competitionPlayer->player->name }}</span>
                </div>
                <div>
                    <span class="font-medium text-blue-700">都道府県:</span>
                    <span class="text-blue-900">{{ $competitionPlayer->player->prefecture }}</span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="player_number"
                label="選手番号"
                type="text"
                placeholder="例: 001, A01"
                help-text="大会内でユニークな番号を設定してください"
                :value="old('player_number', $competitionPlayer->player_number)"
                :required="true"
                col-span="1" />

            <!-- 現在の選手番号表示 -->
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">現在の選手番号</label>
                <div class="bg-gray-50 border border-gray-300 rounded-md px-3 py-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $competitionPlayer->player_number }}
                    </span>
                </div>
            </div>
        </div>
    </x-form-card>
@endsection