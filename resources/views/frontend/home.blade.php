@extends('layouts.frontend')

@section('title', 'ダッシュボード - SJT-CP Frontend')

@section('content')
<div class="space-y-8">
    <!-- ウェルカムセクション -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    おかえりなさい、{{ auth()->user()->name }}さん
                </h1>
                <p class="text-gray-600 mt-1">
                    今日も技能競技会の運営をサポートします
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">現在時刻</div>
                <div class="text-lg font-semibold text-gray-900" id="current-time">
                    {{ now()->format('Y年m月d日 H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <!-- ステータスカード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- アクティブな大会 -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">アクティブな大会</p>
                    <p class="text-2xl font-bold text-gray-900">3</p>
                </div>
            </div>
        </div>

        <!-- 参加選手数 -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">参加選手数</p>
                    <p class="text-2xl font-bold text-gray-900">124</p>
                </div>
            </div>
        </div>

        <!-- 稼働中端末 -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">稼働中端末</p>
                    <p class="text-2xl font-bold text-gray-900">45</p>
                </div>
            </div>
        </div>

        <!-- システム状態 -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">システム状態</p>
                    <p class="text-lg font-bold text-green-600">正常</p>
                </div>
            </div>
        </div>
    </div>

    <!-- メインコンテンツエリア -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- 今日の予定 -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">今日の予定</h2>
                    <span class="text-sm text-gray-500">{{ now()->format('Y年m月d日') }}</span>
                </div>
                <div class="space-y-4">
                    <!-- スケジュール項目 -->
                    <div class="flex items-center p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-blue-600">09:00</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">競技開始準備</h4>
                            <p class="text-sm text-gray-600">端末の最終チェックと選手受付開始</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-green-600">10:00</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">第1競技開始</h4>
                            <p class="text-sm text-gray-600">ウェブデザイン職種 課題A</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-yellow-600">15:00</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-sm font-semibold text-gray-900">競技終了・データ回収</h4>
                            <p class="text-sm text-gray-600">作品提出と端末データバックアップ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- クイックアクション -->
        <div class="space-y-6">
            <!-- クイックアクション -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">クイックアクション</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.home') }}" 
                       class="block w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">管理画面を開く</span>
                        </div>
                    </a>
                    
                    <button class="block w-full text-left px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">データを更新</span>
                        </div>
                    </button>
                    
                    <button class="block w-full text-left px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">レポート生成</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- 最新通知 -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">最新通知</h2>
                <div class="space-y-3">
                    <div class="p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-gray-900">競技開始まで30分</p>
                        <p class="text-xs text-gray-600 mt-1">09:30 | システム確認完了</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                        <p class="text-sm font-medium text-gray-900">端末3台で軽微な問題</p>
                        <p class="text-xs text-gray-600 mt-1">09:15 | 対応中...</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                        <p class="text-sm font-medium text-gray-900">選手受付開始</p>
                        <p class="text-xs text-gray-600 mt-1">09:00 | 進行中</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 進行状況チャート -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">今日の進行状況</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-blue-600">75%</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-900 mt-2">準備完了率</p>
                <p class="text-xs text-gray-600">端末・選手・運営</p>
            </div>
            <div class="text-center">
                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-green-600">92%</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-900 mt-2">出席率</p>
                <p class="text-xs text-gray-600">124名中114名</p>
            </div>
            <div class="text-center">
                <div class="relative">
                    <div class="w-24 h-24 mx-auto bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-purple-600">100%</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-900 mt-2">システム稼働率</p>
                <p class="text-xs text-gray-600">全システム正常</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 現在時刻の更新
function updateTime() {
    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    };
    document.getElementById('current-time').textContent = now.toLocaleDateString('ja-JP', options);
}

// 1秒ごとに時刻を更新
setInterval(updateTime, 1000);
</script>
@endpush
@endsection