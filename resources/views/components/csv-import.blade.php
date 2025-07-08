@props([
    'action' => '',
    'format' => '',
    'title' => 'CSVインポート',
    'additionalFields' => []
])

<div class="bg-white shadow-lg rounded-xl p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $title }}</h3>
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="flex items-center space-x-4">
        @csrf
        
        @foreach($additionalFields as $field)
            <input type="hidden" name="{{ $field['name'] }}" value="{{ $field['value'] }}">
        @endforeach
        
        <input type="file" 
               name="csv_file" 
               accept=".csv,.txt"
               required
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        
        <button type="submit" 
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200">
            インポート
        </button>
    </form>
    
    @if($format)
        <p class="text-xs text-gray-500 mt-2">
            形式: {{ $format }}
        </p>
    @endif
</div>