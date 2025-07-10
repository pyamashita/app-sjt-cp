@extends('layouts.guest')

@section('title', 'ユーザー登録申請 - SJT-CP')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <!-- ヘッダー部分 -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center mb-6">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">ユーザー登録申請</h1>
            <p class="text-gray-600">競技委員・補佐員として登録申請を行います</p>
        </div>

        <!-- 登録フォーム -->
        <div class="bg-white py-8 px-6 sm:px-10 shadow-xl rounded-xl border border-gray-100">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- 氏名 -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        氏名 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        placeholder="山田 太郎"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- メールアドレス -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        メールアドレス <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="your@email.com"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        パスワード <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="8文字以上のパスワード"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- パスワード確認 -->
                <div>
                    <label for="password-confirm" class="block text-sm font-semibold text-gray-700 mb-2">
                        パスワード（確認） <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password-confirm"
                        name="password_confirmation"
                        required
                        placeholder="パスワードを再入力"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >
                </div>

                <!-- 役割 -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                        役割 <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >
                        <option value="">選択してください</option>
                        <option value="競技委員" {{ old('role') == '競技委員' ? 'selected' : '' }}>競技委員</option>
                        <option value="補佐員" {{ old('role') == '補佐員' ? 'selected' : '' }}>補佐員</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 申請理由 -->
                <div>
                    <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                        申請理由 <span class="text-gray-400 text-xs">（任意）</span>
                    </label>
                    <textarea
                        id="reason"
                        name="reason"
                        rows="3"
                        placeholder="登録を希望する理由や担当業務などをご記入ください"
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 送信ボタン -->
                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 transform hover:scale-[1.02] active:scale-[0.98]"
                    >
                        登録申請を送信
                    </button>
                </div>

                <!-- ログインリンク -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        すでにアカウントをお持ちの方は
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition duration-200">
                            こちらからログイン
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection