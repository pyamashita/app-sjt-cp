@extends('layouts.admin')

@section('title', '端末割り当て編集 - SJT-CP')

@php
    $breadcrumbs = [
        ['label' => '端末管理', 'url' => route('admin.devices.index')],
        ['label' => '競技端末割り当て', 'url' => route('admin.competition-devices.index')],
        ['label' => '編集', 'url' => '']
    ];
    
    $pageTitle = '端末割り当て編集';
    $pageDescription = '選手番号を変更してください。';
@endphp

@section('content')
    <!-- 現在の割り当て情報 -->
    <x-detail-card 
        title="現在の割り当て情報"
        :data="[
            ['label' => '大会名', 'value' => $competitionDevice->competition->name, 'class' => 'font-semibold'],
            ['label' => '開催期間', 'value' => $competitionDevice->competition->period],
            ['label' => '端末名', 'value' => $competitionDevice->device->name, 'class' => 'font-semibold'],
            [
                'label' => '端末種別', 
                'value' => $competitionDevice->device->type,
                'badge' => true,
                'badgeClass' => $competitionDevice->device->type === 'PC' ? 'bg-blue-100 text-blue-800' : 
                               ($competitionDevice->device->type === 'スマートフォン' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')
            ],
            [
                'label' => '現在の選手番号', 
                'value' => $competitionDevice->player_number,
                'badge' => true,
                'badgeClass' => 'bg-yellow-100 text-yellow-800'
            ]
        ]" />

    <!-- 編集フォーム -->
    <x-form-card 
        title="選手番号編集"
        :action="route('admin.competition-devices.update', $competitionDevice)"
        :cancel-url="route('admin.competition-devices.index')"
        method="PUT"
        submit-label="更新">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-field 
                name="player_number"
                label="選手番号"
                type="text"
                placeholder="例：001、A-01"
                :value="old('player_number', $competitionDevice->player_number)"
                :required="true"
                col-span="1" />

            @if($availablePlayers->count() > 0)
                <div class="col-span-1">
                    <label for="player_select" class="block text-sm font-semibold text-gray-700 mb-2">
                        参加選手から選択
                    </label>
                    <select id="player_select" 
                            onchange="selectPlayer()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">参加選手から選択</option>
                        @foreach($availablePlayers as $player)
                            <option value="{{ $player->player_number }}" data-name="{{ $player->player->name }}">
                                {{ $player->player_number }} - {{ $player->player->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        この大会に登録済みの選手から選択できます。
                    </p>
                </div>
            @endif
        </div>
    </x-form-card>
@endsection

@push('scripts')
<script>
function selectPlayer() {
    const select = document.getElementById('player_select');
    const input = document.getElementById('player_number');
    if (select.value) {
        input.value = select.value;
    }
}
</script>
@endpush