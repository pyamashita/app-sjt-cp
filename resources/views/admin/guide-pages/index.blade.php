@extends('layouts.admin')

@section('title', 'ガイドページ管理 - SJT-CP')

@section('content')
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">ガイドページ管理</h1>
                <p class="mt-2 text-sm text-gray-600">競技参加者向けのガイドページを管理します</p>
            </div>
            <a href="{{ route('admin.guide-pages.create') }}" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                新規作成
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                タイトル
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                大会
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ステータス
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                セクション数
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                作成日
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                操作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($guidePages as $guidePage)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $guidePage->title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $guidePage->competition->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($guidePage->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            有効
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            無効
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $guidePage->sections->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $guidePage->created_at->format('Y/m/d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.guide-pages.show', $guidePage) }}" 
                                           class="text-purple-600 hover:text-purple-900">詳細</a>
                                        <a href="{{ route('admin.guide-pages.edit', $guidePage) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">編集</a>
                                        <a href="{{ route('admin.guide-pages.preview', $guidePage) }}" 
                                           class="text-blue-600 hover:text-blue-900" target="_blank">プレビュー</a>
                                        @if(!$guidePage->is_active)
                                            <form method="POST" action="{{ route('admin.guide-pages.activate', $guidePage) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-900"
                                                        onclick="return confirm('このページを有効化しますか？同じ大会の他のページは無効になります。')">
                                                    有効化
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.guide-pages.destroy', $guidePage) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('このページを削除してもよろしいですか？')">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    ガイドページが登録されていません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($guidePages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $guidePages->links() }}
            </div>
        @endif
    </div>
@endsection