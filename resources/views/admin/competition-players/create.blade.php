@extends('layouts.admin')

@section('title', '選手割り当て - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '選手情報管理', 'url' => route('admin.players.index')],
        ['label' => '大会選手割当', 'url' => route('admin.competition-players.index')],
        ['label' => '新規割当', 'url' => '']
    ];
    
    $pageTitle = '選手割り当て';
    $pageDescription = '大会に選手を割り当てます';
@endphp

@section('content')
    @if(!$selectedCompetition)
        <!-- 大会選択 -->
        <x-form-card 
            title="大会選択"
            :action="route('admin.competition-players.create')"
            method="GET"
            submit-label="選択"
            :cancel-url="route('admin.competition-players.index')">
            
            <x-form-field 
                name="competition_id"
                label="大会"
                type="select"
                :options="$competitions->pluck('name', 'id')->toArray()"
                placeholder="大会を選択してください"
                :value="request('competition_id')"
                :required="true"
                col-span="1" />
        </x-form-card>
    @else
        <!-- 選手割り当てフォーム -->
        <x-form-card 
            title="選手割り当て"
            :action="route('admin.competition-players.store')"
            :cancel-url="route('admin.competition-players.index', ['competition_id' => $selectedCompetition->id])"
            submit-label="割り当て">
            
            <input type="hidden" name="competition_id" value="{{ $selectedCompetition->id }}">
            
            <!-- 大会情報表示 -->
            <div class="col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-medium text-blue-900 mb-2">割り当て先大会</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-700">大会名:</span>
                        <span class="text-blue-900">{{ $selectedCompetition->name }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-700">開催期間:</span>
                        <span class="text-blue-900">{{ $selectedCompetition->period }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-700">開催場所:</span>
                        <span class="text-blue-900">{{ $selectedCompetition->venue }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-700">現在の参加者数:</span>
                        <span class="text-blue-900">{{ $selectedCompetition->competitionPlayers->count() }}人</span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form-field 
                    name="player_id"
                    label="選手"
                    type="select"
                    :options="$availablePlayers->pluck('name', 'id')->toArray()"
                    placeholder="選手を選択してください"
                    :value="old('player_id')"
                    :required="true"
                    col-span="1" />

                <x-form-field 
                    name="player_number"
                    label="選手番号"
                    type="text"
                    placeholder="例: 001, A01"
                    help-text="大会内でユニークな番号を設定してください"
                    :value="old('player_number')"
                    :required="true"
                    col-span="1" />
            </div>
        </x-form-card>
    @endif
@endsection