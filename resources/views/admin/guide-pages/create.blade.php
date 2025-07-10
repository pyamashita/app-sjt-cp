@extends('layouts.admin')

@section('title', '新規ガイドページ作成 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">新規ガイドページ作成</h1>
        <p class="mt-2 text-sm text-gray-600">競技参加者向けのガイドページを作成します</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('admin.guide-pages.store') }}" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label for="competition_id" class="block text-sm font-medium text-gray-700 mb-2">
                    大会 <span class="text-red-500">*</span>
                </label>
                <select name="competition_id" id="competition_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                    <option value="">大会を選択してください</option>
                    @foreach($competitions as $competition)
                        <option value="{{ $competition->id }}" {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
                            {{ $competition->name }}
                        </option>
                    @endforeach
                </select>
                @error('competition_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    ページタイトル <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" 
                       required>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.guide-pages.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    キャンセル
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm font-medium hover:bg-purple-700">
                    作成
                </button>
            </div>
        </form>
    </div>
@endsection