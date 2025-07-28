@extends('layouts.admin')

@section('title', 'APIトークン管理 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">APIトークン管理</h1>
        <p class="mt-2 text-sm text-gray-600">APIアクセス用のトークンを管理します</p>
    </div>

    <!-- 検索・フィルタ -->
    <div class="mb-6 bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('admin.api-tokens.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">検索</label>
                <input type="text" 
                       name="search" 
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="トークン名、説明で検索"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            
            <div>
                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">状態</label>
                <select name="is_active" id="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">すべて</option>
                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>有効</option>
                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>無効</option>
                </select>
            </div>
            
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">有効期限</label>
                <select name="expires_at" id="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">すべて</option>
                    <option value="active" {{ request('expires_at') == 'active' ? 'selected' : '' }}>有効期限内</option>
                    <option value="expired" {{ request('expires_at') == 'expired' ? 'selected' : '' }}>期限切れ</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2 md:col-span-3">
                <button type="submit" 
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    検索
                </button>
                <a href="{{ route('admin.api-tokens.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    リセット
                </a>
            </div>
        </form>
    </div>

    <!-- 操作ボタン -->
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.api-tokens.create') }}" 
           class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            新規作成
        </a>
        
        <a href="{{ route('admin.api-tokens.export', request()->query()) }}" 
           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            </svg>
            CSV出力
        </a>
    </div>

    <!-- データテーブル -->
    <x-data-table
        :headers="['トークン名', '権限', '状態', '有効期限', '最終使用', '作成日']"
        :rows="$tableRows"
        :actions="[
            ['type' => 'link', 'label' => '詳細', 'url' => '/sjt-cp-admin/api-tokens/:id', 'color' => 'blue'],
            ['type' => 'link', 'label' => '編集', 'url' => '/sjt-cp-admin/api-tokens/:id/edit', 'color' => 'green'],
            ['type' => 'form', 'label' => '削除', 'url' => '/sjt-cp-admin/api-tokens/:id', 'method' => 'DELETE', 'color' => 'red', 'confirm' => 'このAPIトークンを削除してもよろしいですか？']
        ]"
        :pagination="$tokens"
        empty-message="APIトークンが見つかりません。"
    />
@endsection