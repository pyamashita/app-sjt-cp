@extends('layouts.frontend')

@section('title', 'Welcome - SJT-CP Frontend')

@section('content')
<div class="space-y-8">
    <!-- ウェルカムセクション -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-12 text-white">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Welcome to SJT-CP Frontend
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
                    技能競技会運営サポートシステムへようこそ
                </p>
                <p class="text-lg text-blue-200 mt-4">
                    フロントエンドダッシュボードで効率的な大会運営を実現します
                </p>
            </div>
        </div>
    </div>

    <!-- 機能概要カード -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- ダッシュボード -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 ml-3">ダッシュボード</h3>
            </div>
            <p class="text-gray-600 mb-4">
                大会の進行状況や重要な情報を一目で確認できます。リアルタイムでの状況把握が可能です。
            </p>
            <a href="{{ route('frontend.home') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                詳細を見る
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- 管理機能 -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 ml-3">管理機能</h3>
            </div>
            <p class="text-gray-600 mb-4">
                詳細な設定や管理が必要な場合は、管理画面をご利用ください。高度な機能を提供します。
            </p>
            <a href="{{ route('admin.home') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
                管理画面へ
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- システム情報 -->
        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 ml-3">システム情報</h3>
            </div>
            <p class="text-gray-600 mb-4">
                現在のユーザー: <strong>{{ auth()->user()->name }}</strong><br>
                権限: <strong>{{ auth()->user()->role_display_name }}</strong>
            </p>
            <div class="text-sm text-gray-500">
                最終ログイン: {{ now()->format('Y年m月d日 H:i') }}
            </div>
        </div>
    </div>

    <!-- アクションセクション -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                さあ、始めましょう
            </h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                SJT-CP Frontend では、競技大会の運営に必要な情報を見やすく整理して表示します。
                管理画面とは異なる、表示に特化したインターフェースをお楽しみください。
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('frontend.home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    ダッシュボードを開く
                </a>
                <a href="{{ route('admin.home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    管理画面へ
                </a>
            </div>
        </div>
    </div>

    <!-- フィーチャー説明 -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                Frontend の特徴
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="flex items-start">
                <div class="bg-blue-100 rounded-lg p-2 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">表示に特化</h3>
                    <p class="text-gray-600">
                        情報の閲覧と監視に最適化されたインターフェースで、重要なデータを素早く把握できます。
                    </p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-green-100 rounded-lg p-2 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">高速レスポンス</h3>
                    <p class="text-gray-600">
                        軽量で高速なレスポンスを実現し、リアルタイムでの情報更新に対応しています。
                    </p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-purple-100 rounded-lg p-2 mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">レスポンシブデザイン</h3>
                    <p class="text-gray-600">
                        デスクトップ、タブレット、スマートフォンなど、あらゆるデバイスで最適な表示を提供します。
                    </p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="bg-yellow-100 rounded-lg p-2 mr-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">セキュア</h3>
                    <p class="text-gray-600">
                        管理画面と同じ認証システムを使用し、セキュリティを確保しながら必要な情報にアクセスできます。
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection