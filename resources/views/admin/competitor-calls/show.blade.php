@extends('layouts.admin')

@section('title', '選手呼び出し詳細 - SJT-CP')

@php
    $pageTitle = '選手呼び出し詳細';
    $pageDescription = '選手呼び出しの詳細情報を表示します';
    $breadcrumbs = [
        ['label' => '管理画面', 'url' => route('admin.home')],
        ['label' => '選手呼び出し一覧', 'url' => route('admin.competitor-calls.index')],
        ['label' => '詳細', 'url' => '']
    ];
    $pageActions = [
        [
            'type' => 'link',
            'label' => '一覧に戻る',
            'url' => route('admin.competitor-calls.index'),
            'icon' => '<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>'
        ]
    ];
@endphp

@section('content')
    <!-- 基本情報 -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">基本情報</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $competitorCall->id }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">端末ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $competitorCall->device_id }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">呼び出し種別</dt>
                    <dd class="mt-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $competitorCall->call_type === 'general' 
                               ? 'bg-green-100 text-green-800' 
                               : 'bg-orange-100 text-orange-800' }}">
                            {{ $competitorCall->call_type_name }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">呼び出し日時</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $competitorCall->called_at->setTimezone('Asia/Tokyo')->format('Y年m月d日 H:i:s') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">登録日時</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $competitorCall->created_at->setTimezone('Asia/Tokyo')->format('Y年m月d日 H:i:s') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">更新日時</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $competitorCall->updated_at->setTimezone('Asia/Tokyo')->format('Y年m月d日 H:i:s') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- 関連端末情報 -->
    @php
        $device = \App\Models\Device::where('device_id', $competitorCall->device_id)->first();
    @endphp
    
    @if($device)
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">関連端末情報</h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">端末名</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $device->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">IPアドレス</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $device->ip_address ?? 'なし' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">説明</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $device->description ?? 'なし' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $device->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $device->is_active ? 'アクティブ' : '非アクティブ' }}
                            </span>
                        </dd>
                    </div>
                </dl>
                
                <div class="mt-6">
                    <a href="{{ route('admin.devices.show', $device) }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        端末詳細を表示
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- 同一端末の最近の呼び出し履歴 -->
    @php
        $recentCalls = \App\Models\CompetitorCall::where('device_id', $competitorCall->device_id)
            ->where('id', '!=', $competitorCall->id)
            ->orderBy('called_at', 'desc')
            ->limit(10)
            ->get();
    @endphp

    @if($recentCalls->count() > 0)
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">同一端末の最近の呼び出し履歴</h3>
                <p class="text-sm text-gray-500">端末「{{ $competitorCall->device_id }}」の最近10件の呼び出し履歴</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                呼び出し種別
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                呼び出し日時
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">操作</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentCalls as $call)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $call->call_type === 'general' 
                                           ? 'bg-green-100 text-green-800' 
                                           : 'bg-orange-100 text-orange-800' }}">
                                        {{ $call->call_type_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $call->called_at->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.competitor-calls.show', $call) }}" 
                                       class="text-blue-600 hover:text-blue-900">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- 削除操作 -->
    <div class="mt-6 flex justify-end">
        <form method="POST" action="{{ route('admin.competitor-calls.destroy', $competitorCall) }}" 
              class="inline" onsubmit="return confirm('この呼び出し記録を削除しますか？この操作は取り消せません。')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                削除
            </button>
        </form>
    </div>
@endsection