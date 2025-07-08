@props([
    'title' => '',
    'data' => [],
    'columns' => 2
])

<div class="bg-white shadow-lg rounded-xl overflow-hidden mb-6">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="px-6 py-8">
        @if(count($data) > 0)
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-{{ $columns }}">
                @foreach($data as $item)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ $item['label'] }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 {{ $item['class'] ?? '' }}">
                            @if(isset($item['badge']) && $item['badge'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item['badgeClass'] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $item['value'] }}
                                </span>
                            @else
                                {!! $item['value'] ?? '未設定' !!}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
        
        {{ $slot }}
    </div>
</div>