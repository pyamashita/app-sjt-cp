@extends('layouts.admin')

@section('title', $apiToken->name . ' - APIトークン詳細 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $apiToken->name }}</h1>
        <p class="mt-2 text-sm text-gray-600">APIトークン詳細情報</p>
    </div>

    @if(session('token'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">生成されたトークン</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>以下のトークンは一度だけ表示されます。必ず安全な場所に保存してください。</p>
                        <div class="mt-2 p-3 bg-white rounded border font-mono text-sm">
                            {{ session('token') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 基本情報 -->
        <x-detail-card 
            title="基本情報"
            :data="[
                ['label' => 'トークン名', 'value' => $apiToken->name],
                ['label' => '状態', 'value' => $apiToken->is_active ? '有効' : '無効', 'badge' => true, 'badgeClass' => $apiToken->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'],
                ['label' => '有効期限', 'value' => $apiToken->expires_at ? $apiToken->expires_at->format('Y年m月d日 H:i') : '無期限'],
                ['label' => '最終使用', 'value' => $apiToken->last_used_at ? $apiToken->last_used_at->format('Y年m月d日 H:i') : '未使用'],
                ['label' => '作成日', 'value' => $apiToken->created_at->format('Y年m月d日 H:i')]
            ]"
        />

        <!-- 説明 -->
        @if($apiToken->description)
        <x-detail-card 
            title="説明"
            :data="[
                ['label' => '説明', 'value' => $apiToken->description, 'full_width' => true]
            ]"
        />
        @endif

        <!-- 権限 -->
        <x-detail-card 
            title="権限"
            :data="[]"
        >
            @if($apiToken->permissions && count($apiToken->permissions) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($apiToken->permissions as $permission)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ App\Models\ApiToken::getPermissions()[$permission] ?? $permission }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600">権限が設定されていません。</p>
            @endif
        </x-detail-card>

        <!-- アクセス制御 -->
        <x-detail-card 
            title="アクセス制御"
            :data="[]"
        >
            @if($apiToken->allowed_ips && count($apiToken->allowed_ips) > 0)
                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-700">許可IPアドレス</h4>
                    <div class="space-y-1">
                        @foreach($apiToken->allowed_ips as $ip)
                            <div class="flex items-center p-2 bg-gray-50 rounded">
                                <span class="text-sm font-mono">{{ $ip }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-600">すべてのIPアドレスからのアクセスが許可されています。</p>
            @endif
        </x-detail-card>

        <!-- アクション -->
        <x-detail-card 
            title="操作"
            :data="[]"
        >
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <form method="POST" action="{{ route('admin.api-tokens.regenerate', $apiToken) }}">
                    @csrf
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            onclick="return confirm('トークンを再生成してもよろしいですか？現在のトークンは無効になります。')">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        再生成
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.api-tokens.toggle', $apiToken) }}">
                    @csrf
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white {{ $apiToken->is_active ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }} focus:outline-none focus:ring-2">
                        @if($apiToken->is_active)
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                            </svg>
                            無効化
                        @else
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            有効化
                        @endif
                    </button>
                </form>
                
                <a href="{{ route('admin.api-tokens.edit', $apiToken) }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    編集
                </a>
                
                <form method="POST" action="{{ route('admin.api-tokens.destroy', $apiToken) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                            onclick="return confirm('このAPIトークンを削除してもよろしいですか？')">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        削除
                    </button>
                </form>
            </div>
        </x-detail-card>
    </div>
@endsection