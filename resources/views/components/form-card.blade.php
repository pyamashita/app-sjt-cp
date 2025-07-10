@props([
    'title' => '',
    'action' => '',
    'method' => 'POST',
    'enctype' => '',
    'cancelUrl' => '',
    'submitLabel' => '保存',
    'showCancel' => true
])

<div class="bg-white shadow-lg rounded-xl overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="px-6 py-8">
        <form method="POST" action="{{ $action }}" @if($enctype) enctype="{{ $enctype }}" @endif class="space-y-6">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            
            {{ $slot }}
            
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                @if($showCancel)
                    <a href="{{ $cancelUrl }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        キャンセル
                    </a>
                @else
                    <div></div>
                @endif
                
                <button type="submit"
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</div>