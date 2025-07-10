@props([
    'action' => '',
    'method' => 'GET',
    'fields' => [],
    'showTitle' => true
])

<div class="bg-white shadow-lg rounded-xl p-6 mb-6">
    @if($showTitle)
        <h3 class="text-lg font-semibold text-gray-900 mb-4">検索・フィルター</h3>
    @endif
    
    <form method="{{ $method }}" action="{{ $action }}" class="space-y-4">
        @if($method !== 'GET')
            @csrf
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-{{ count($fields) > 3 ? '4' : count($fields) + 1 }} gap-4">
            @foreach($fields as $field)
                <div>
                    <label for="{{ $field['name'] }}" class="block text-sm font-medium text-gray-700">
                        {{ $field['label'] }}
                    </label>
                    
                    @if($field['type'] === 'text')
                        <input type="text" 
                               id="{{ $field['name'] }}" 
                               name="{{ $field['name'] }}" 
                               value="{{ request($field['name']) }}"
                               placeholder="{{ $field['placeholder'] ?? '' }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @elseif($field['type'] === 'select')
                        <select id="{{ $field['name'] }}" 
                                name="{{ $field['name'] }}"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">{{ $field['placeholder'] ?? 'すべて' }}</option>
                            @foreach($field['options'] as $value => $label)
                                <option value="{{ $value }}" {{ request($field['name']) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'date')
                        <input type="date" 
                               id="{{ $field['name'] }}" 
                               name="{{ $field['name'] }}" 
                               value="{{ request($field['name']) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @endif
                </div>
            @endforeach
            
            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                    検索
                </button>
                <a href="{{ $action }}" 
                   class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200 text-center">
                    リセット
                </a>
            </div>
        </div>
    </form>
</div>