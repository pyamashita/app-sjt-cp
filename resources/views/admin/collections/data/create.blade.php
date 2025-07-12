@extends('layouts.admin')

@section('title', 'データ入力 - ' . $collection->display_name . ' - SJT-CP')

@php
    $pageTitle = $collection->display_name . ' - データ入力';
    $pageDescription = 'コレクションデータの入力・編集';
    $breadcrumbs = [
        ['label' => 'コレクション一覧', 'url' => route('admin.collections.index')],
        ['label' => $collection->display_name, 'url' => route('admin.collections.show', $collection)],
        ['label' => 'データ管理', 'url' => route('admin.collections.data.index', $collection)],
        ['label' => 'データ入力', 'url' => '']
    ];
@endphp

@section('content')
    <form method="POST" action="{{ route('admin.collections.data.store', $collection) }}">
        @csrf
        
        <div class="space-y-6">
            <!-- コンテキスト選択 -->
            @if($collection->is_competition_managed || $collection->is_player_managed)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">コンテキスト選択</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($collection->is_competition_managed)
                            <div>
                                <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    大会 <span class="text-red-500">*</span>
                                </label>
                                <div class="flex">
                                    <select name="competition_id" id="competition_id" required onchange="onCompetitionChange()"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('competition_id') border-red-500 @enderror">
                                        <option value="">選択してください</option>
                                        @foreach($competitions as $comp)
                                            <option value="{{ $comp->id }}" {{ (old('competition_id', $competition?->id) == $comp->id) ? 'selected' : '' }}>
                                                {{ $comp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="showCompetitionModal()" 
                                            class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('competition_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        
                        @if($collection->is_player_managed)
                            <div>
                                <label for="player_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    選手 <span class="text-red-500">*</span>
                                </label>
                                <div class="flex">
                                    <select name="player_id" id="player_id" required
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('player_id') border-red-500 @enderror">
                                        <option value="">選択してください</option>
                                        @foreach($players as $p)
                                            <option value="{{ $p->id }}" {{ (old('player_id', $player?->id) == $p->id) ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="showPlayerModal()" 
                                            class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('player_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                    
                    @if($competition || $player)
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="text-sm text-blue-800">
                                <strong>選択中:</strong>
                                @if($competition)
                                    {{ $competition->name }}
                                @endif
                                @if($player)
                                    {{ $competition ? ' - ' : '' }}{{ $player->name }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            
            <!-- データ入力フォーム -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">データ入力</h3>
                
                @if($collection->contents->count() > 0)
                    <div class="space-y-6">
                        @foreach($collection->contents as $content)
                            @php
                                $fieldName = "content_{$content->id}";
                                $existingValue = $existingData->get($content->id)?->value ?? old($fieldName);
                            @endphp
                            
                            <div>
                                <label for="{{ $fieldName }}" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $content->name }}
                                    @if($content->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                    <span class="text-xs text-gray-500">({{ $content->content_type_display_name }})</span>
                                </label>
                                
                                @if($content->content_type === 'string')
                                    <input type="text" name="{{ $fieldName }}" id="{{ $fieldName }}" 
                                           value="{{ $existingValue }}"
                                           maxlength="{{ $content->max_length ?? 255 }}"
                                           {{ $content->is_required ? 'required' : '' }}
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror">
                                    @if($content->max_length)
                                        <p class="mt-1 text-sm text-gray-500">最大{{ $content->max_length }}文字</p>
                                    @endif
                                    
                                @elseif($content->content_type === 'text')
                                    <textarea name="{{ $fieldName }}" id="{{ $fieldName }}" rows="4"
                                              maxlength="{{ $content->max_length ?? 5000 }}"
                                              {{ $content->is_required ? 'required' : '' }}
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror">{{ $existingValue }}</textarea>
                                    @if($content->max_length)
                                        <p class="mt-1 text-sm text-gray-500">最大{{ $content->max_length }}文字</p>
                                    @endif
                                    
                                @elseif($content->content_type === 'boolean')
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" name="{{ $fieldName }}" value="1" 
                                                   {{ $existingValue === '1' ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <span class="ml-2 text-sm text-gray-700">はい</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="{{ $fieldName }}" value="0" 
                                                   {{ $existingValue === '0' ? 'checked' : '' }}
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <span class="ml-2 text-sm text-gray-700">いいえ</span>
                                        </label>
                                    </div>
                                    
                                @elseif($content->content_type === 'resource')
                                    <div class="flex">
                                        <select name="{{ $fieldName }}" id="{{ $fieldName }}"
                                                {{ $content->is_required ? 'required' : '' }}
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror">
                                            <option value="">選択してください</option>
                                            @foreach($resources as $resource)
                                                <option value="{{ $resource->id }}" {{ $existingValue == $resource->id ? 'selected' : '' }}>
                                                    {{ $resource->original_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="showResourceModal('{{ $fieldName }}')" 
                                                class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                @elseif($content->content_type === 'date')
                                    <input type="date" name="{{ $fieldName }}" id="{{ $fieldName }}" 
                                           value="{{ $existingValue }}"
                                           {{ $content->is_required ? 'required' : '' }}
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror">
                                           
                                @elseif($content->content_type === 'time')
                                    <input type="time" name="{{ $fieldName }}" id="{{ $fieldName }}" 
                                           value="{{ $existingValue }}"
                                           {{ $content->is_required ? 'required' : '' }}
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error($fieldName) border-red-500 @enderror">
                                @endif
                                
                                @error($fieldName)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">コンテンツが定義されていません</h3>
                        <p class="mt-1 text-sm text-gray-500">先にコレクションにコンテンツを追加してください。</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.collections.show', $collection) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                コンテンツを追加
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- アクション -->
            @if($collection->contents->count() > 0)
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.collections.data.index', $collection) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        キャンセル
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        保存
                    </button>
                </div>
            @endif
        </div>
    </form>

@push('scripts')
<script>
function onCompetitionChange() {
    const competitionId = document.getElementById('competition_id').value;
    // 大会が変更されたら選手選択をリセット（大会所属選手のみ表示する場合）
}

function showCompetitionModal() {
    alert('大会検索モーダル（今後実装）');
}

function showPlayerModal() {
    alert('選手検索モーダル（今後実装）');
}

function showResourceModal(fieldName) {
    alert('リソース検索モーダル（今後実装）');
}
</script>
@endpush
@endsection