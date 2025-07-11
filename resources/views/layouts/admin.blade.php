<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SJT-CP')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.home') }}" class="flex items-center">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 hidden sm:block">SJT-CP</h1>
                        <h1 class="text-lg font-bold text-gray-900 sm:hidden">SJT</h1>
                    </a>
                    
                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                        <span class="ml-4 text-gray-400">|</span>
                        <nav class="ml-4 flex items-center space-x-2" aria-label="Breadcrumb">
                            @foreach($breadcrumbs as $index => $breadcrumb)
                                @if($index > 0)
                                    <span class="text-gray-400">></span>
                                @endif
                                @if($loop->last)
                                    <span class="text-gray-600 font-medium">{{ $breadcrumb['label'] }}</span>
                                @else
                                    <a href="{{ $breadcrumb['url'] }}" class="text-gray-600 hover:text-gray-900">{{ $breadcrumb['label'] }}</a>
                                @endif
                            @endforeach
                        </nav>
                    @endif
                </div>

                <div class="flex items-center space-x-4">
                    <!-- ユーザー情報 -->
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500 capitalize">
                            {{ auth()->user()->role_display_name }}
                        </span>
                    </div>

                    <!-- モバイル用ユーザー情報 -->
                    <div class="md:hidden">
                        <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                    </div>

                    <!-- ログアウトボタン -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="hidden sm:inline">ログアウト</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- サブナビゲーション（必要な場合） -->
    @if(isset($subNavigation) && count($subNavigation) > 0)
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8 py-4">
                    @foreach($subNavigation as $item)
                        <a href="{{ $item['url'] }}" 
                           class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200
                                  {{ $item['active'] ?? false 
                                     ? 'text-blue-600 bg-blue-50 border-b-2 border-blue-600' 
                                     : 'text-gray-600 hover:text-blue-600 hover:bg-gray-50' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    <!-- メインコンテンツ -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <!-- ページヘッダー -->
            @if(isset($pageTitle) || isset($pageDescription) || isset($pageActions))
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            @if(isset($pageTitle))
                                <h2 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h2>
                            @endif
                            @if(isset($pageDescription))
                                <p class="text-gray-600 mt-2">{{ $pageDescription }}</p>
                            @endif
                        </div>
                        @if(isset($pageActions))
                            <div class="flex space-x-3">
                                @foreach($pageActions as $action)
                                    <a href="{{ $action['url'] }}" 
                                       class="inline-flex items-center px-4 py-2 border {{ $action['type'] === 'primary' ? 'border-transparent bg-blue-600 text-white hover:bg-blue-700' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }} rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                        @if(isset($action['icon']))
                                            {!! $action['icon'] !!}
                                        @endif
                                        {{ $action['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 成功・エラーメッセージ -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">入力エラーがあります</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ページコンテンツ -->
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>