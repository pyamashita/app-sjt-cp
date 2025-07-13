@extends('layouts.admin')

@section('title', '管理画面ホーム - SJT-CP')

@php
    $pageTitle = '管理画面ホーム';
    $pageDescription = 'SJT-CP 競技大会管理システムへようこそ';
@endphp

@section('content')
    <!-- ヘルコメパネル -->
    <div class="border-4 border-dashed border-gray-200 rounded-lg p-8 text-center bg-white mb-8">
        <div class="mx-auto h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $pageTitle }}</h2>
        <p class="text-lg text-gray-600 mb-6">{{ $pageDescription }}</p>

        <!-- ステータス表示 -->
        <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-full">
            <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            システム稼働中
        </div>
    </div>

    <!-- 機能カード -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- 大会管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">大会管理</h3>
                        <p class="text-sm text-gray-600">競技大会と競技委員を管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.competitions.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 rounded-md transition-colors duration-200">
                        大会・スケジュール
                    </a>
                    <a href="{{ route('admin.committee-members.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 rounded-md transition-colors duration-200">
                        競技委員管理
                    </a>
                </div>
            </div>
        </div>

        <!-- 選手情報管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">選手情報管理</h3>
                        <p class="text-sm text-gray-600">選手の情報を管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.players.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 rounded-md transition-colors duration-200">
                        選手一覧
                    </a>
                    <a href="{{ route('admin.competition-players.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 rounded-md transition-colors duration-200">
                        大会選手管理
                    </a>
                </div>
            </div>
        </div>

        <!-- 端末管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">端末管理</h3>
                        <p class="text-sm text-gray-600">端末情報と大会割り当てを管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.devices.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-800 rounded-md transition-colors duration-200">
                        端末一覧
                    </a>
                    <a href="{{ route('admin.competition-devices.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-800 rounded-md transition-colors duration-200">
                        競技端末割り当て
                    </a>
                </div>
            </div>
        </div>

        <!-- リソース管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">リソース管理</h3>
                        <p class="text-sm text-gray-600">ファイル・リソースの管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.resources.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-800 rounded-md transition-colors duration-200">
                        リソース一覧
                    </a>
                </div>
            </div>
        </div>

        <!-- ガイドページ管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">ガイドページ管理</h3>
                        <p class="text-sm text-gray-600">競技ガイドページの作成・管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.guide-pages.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-800 rounded-md transition-colors duration-200">
                        ガイドページ一覧
                    </a>
                    <a href="{{ route('admin.guide-pages.create') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-800 rounded-md transition-colors duration-200">
                        新規作成
                    </a>
                </div>
            </div>
        </div>

        <!-- メッセージ管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">メッセージ管理</h3>
                        <p class="text-sm text-gray-600">端末へのメッセージ送信と管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.messages.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-800 rounded-md transition-colors duration-200">
                        メッセージ一覧
                    </a>
                    <a href="{{ route('admin.messages.create') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-800 rounded-md transition-colors duration-200">
                        新規メッセージ
                    </a>
                </div>
            </div>
        </div>

        <!-- API管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">API管理</h3>
                        <p class="text-sm text-gray-600">APIアクセス制御とトークン管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.api-tokens.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-800 rounded-md transition-colors duration-200">
                        APIトークン管理
                    </a>
                </div>
            </div>
        </div>

        <!-- コレクション管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">コレクション管理</h3>
                        <p class="text-sm text-gray-600">データコレクションとコンテンツ管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.collections.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-800 rounded-md transition-colors duration-200">
                        コレクション一覧
                    </a>
                    <a href="{{ route('admin.collections.create') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-800 rounded-md transition-colors duration-200">
                        新規作成
                    </a>
                </div>
            </div>
        </div>

        <!-- システム管理 -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-all duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">システム管理</h3>
                        <p class="text-sm text-gray-600">ユーザーやシステム設定を管理</p>
                    </div>
                </div>
                
                <!-- サブメニュー -->
                <div class="space-y-2">
                    <a href="{{ route('admin.users.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 rounded-md transition-colors duration-200">
                        ユーザー管理
                    </a>
                    @if(auth()->user()->hasPermission('system_management'))
                        <a href="{{ route('admin.permissions.index') }}" 
                           class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 rounded-md transition-colors duration-200">
                            権限管理
                        </a>
                    @endif
                    <a href="{{ route('admin.external-connections.index') }}" 
                       class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 rounded-md transition-colors duration-200">
                        外部接続設定
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- システム情報 -->
    <div class="mt-8">
        <x-detail-card 
            title="システム情報"
            :data="[
                ['label' => 'バージョン', 'value' => 'v1.0.0 (開発版)'],
                ['label' => '最終更新', 'value' => now()->format('Y年m月d日 H:i')],
                ['label' => '接続ユーザー', 'value' => auth()->user()->name . ' (' . auth()->user()->role_display_name . ')'],
                ['label' => '機能状態', 'value' => '基本機能 稼働中', 'badge' => true, 'badgeClass' => 'bg-green-100 text-green-800']
            ]" />
    </div>
@endsection