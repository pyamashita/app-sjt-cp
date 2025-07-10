@extends('layouts.admin')

@section('title', 'リソース新規登録 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">リソース新規登録</h1>
        <p class="mt-2 text-sm text-gray-600">新しいリソースファイルをアップロードします</p>
    </div>

    <form method="POST" action="{{ route('admin.resources.store') }}" enctype="multipart/form-data">
        @csrf
        
        <x-form-card title="リソース情報">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form-field
                    name="name"
                    label="リソース名"
                    type="text"
                    :value="old('name')"
                    placeholder="リソース名を入力"
                    required
                />
                
                <x-form-field
                    name="category"
                    label="カテゴリ"
                    type="select"
                    :value="old('category')"
                    :options="App\Models\Resource::getCategories()"
                />
                
                <x-form-field
                    name="description"
                    label="説明"
                    type="textarea"
                    :value="old('description')"
                    placeholder="リソースの説明を入力"
                    col-span="2"
                />
                
                <x-form-field
                    name="file"
                    label="ファイル"
                    type="file"
                    required
                    help-text="最大100MBまでのファイルをアップロードできます"
                />
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_public" 
                           id="is_public"
                           value="1"
                           {{ old('is_public') ? 'checked' : '' }}
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="is_public" class="ml-2 block text-sm text-gray-700">
                        公開する（認証なしでアクセス可能）
                    </label>
                </div>
            </div>
        </x-form-card>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.resources.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                登録
            </button>
        </div>
    </form>
@endsection