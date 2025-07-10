@extends('layouts.admin')

@section('title', 'ガイドページ詳細')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">ガイドページ詳細</h1>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.guide-pages.edit', $guidePage) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            編集
                        </a>
                        <a href="{{ route('admin.guide-pages.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            戻る
                        </a>
                    </div>
                </div>

                <!-- 基本情報 -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">基本情報</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">タイトル</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">競技大会</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->competition->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                                    <dd class="text-sm">
                                        @if($guidePage->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                有効
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                無効
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">統計情報</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">セクション数</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->sections->count() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">グループ数</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->sections->sum(function($section) { return $section->groups->count(); }) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">アイテム数</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->sections->sum(function($section) { return $section->groups->sum(function($group) { return $group->items->count(); }); }) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">作成日時</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->created_at->format('Y年m月d日 H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">最終更新</dt>
                                    <dd class="text-sm text-gray-900">{{ $guidePage->updated_at->format('Y年m月d日 H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- プレビューリンク -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                プレビュー: 
                                <a href="{{ route('admin.guide-pages.preview', $guidePage) }}" 
                                   target="_blank" 
                                   class="font-medium underline hover:text-blue-500">
                                    {{ $guidePage->title }}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- コンテンツ構造 -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">コンテンツ構造</h3>
                    
                    @if($guidePage->sections->count() > 0)
                        <div class="space-y-4">
                            @foreach($guidePage->sections as $section)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">
                                        {{ $section->title }}
                                        <span class="text-sm text-gray-500 ml-2">
                                            ({{ $section->groups->count() }} グループ)
                                        </span>
                                    </h4>
                                    
                                    @if($section->groups->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($section->groups as $group)
                                                <div class="bg-gray-50 rounded p-3">
                                                    <h5 class="font-medium text-gray-800 mb-2">
                                                        {{ $group->title }}
                                                        <span class="text-xs text-gray-500 ml-1">
                                                            ({{ $group->items->count() }} アイテム)
                                                        </span>
                                                    </h5>
                                                    
                                                    @if($group->items->count() > 0)
                                                        <ul class="text-sm text-gray-600 space-y-1">
                                                            @foreach($group->items as $item)
                                                                <li class="flex items-center">
                                                                    <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 flex-shrink-0"></span>
                                                                    <span class="truncate">{{ $item->title }}</span>
                                                                    @if($item->type === 'resource')
                                                                        <span class="ml-1 text-xs text-green-600">[R]</span>
                                                                    @elseif($item->type === 'link')
                                                                        <span class="ml-1 text-xs text-blue-600">[L]</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-sm text-gray-500 italic">アイテムなし</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 italic">グループが登録されていません</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>セクションが登録されていません</p>
                            <p class="text-sm mt-1">編集画面からコンテンツを追加してください</p>
                        </div>
                    @endif
                </div>

                <!-- アクション -->
                <div class="flex justify-end space-x-2 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.guide-pages.edit', $guidePage) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        編集
                    </a>
                    <form method="POST" action="{{ route('admin.guide-pages.destroy', $guidePage) }}" 
                          onsubmit="return confirm('本当に削除しますか？')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            削除
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection