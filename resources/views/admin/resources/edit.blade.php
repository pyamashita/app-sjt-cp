@extends('layouts.admin')

@section('title', $resource->name . ' - リソース編集 - SJT-CP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $resource->name }} - 編集</h1>
        <p class="mt-2 text-sm text-gray-600">リソース情報を編集します</p>
    </div>

    <form method="POST" action="{{ route('admin.resources.update', $resource) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">エラーがあります</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">リソース情報</h3>
            </div>
            <div class="px-6 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-field
                        name="name"
                        label="リソース名"
                        type="text"
                        :value="old('name', $resource->name)"
                        placeholder="リソース名を入力"
                        required
                    />
                    
                    <x-form-field
                        name="category"
                        label="カテゴリ"
                        type="select"
                        :value="old('category', $resource->category)"
                        :options="App\Models\Resource::getCategories()"
                    />
                    
                    <x-form-field
                        name="description"
                        label="説明"
                        type="textarea"
                        :value="old('description', $resource->description)"
                        placeholder="リソースの説明を入力"
                        col-span="2"
                    />
                    
                    <div class="col-span-2">
                        <x-form-field
                            name="file"
                            label="ファイル"
                            type="file"
                            help-text="新しいファイルを選択すると現在のファイルが置き換えられます"
                        />
                        
                        <div class="mt-2 p-4 bg-gray-50 rounded-md">
                            <p class="text-sm text-gray-600">現在のファイル: {{ $resource->original_name }}</p>
                            <p class="text-sm text-gray-600">サイズ: {{ $resource->formatted_size }}</p>
                            <p class="text-sm text-gray-600">MIMEタイプ: {{ $resource->mime_type }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center col-span-2">
                        <input type="checkbox" 
                               name="is_public" 
                               id="is_public"
                               value="1"
                               {{ old('is_public', $resource->is_public) ? 'checked' : '' }}
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-700">
                            公開する（認証なしでアクセス可能）
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.resources.show', $resource) }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                キャンセル
            </a>
            <button type="submit" 
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                更新
            </button>
        </div>
    </form>
@endsection