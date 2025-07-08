@props([
    'headers' => [],
    'rows' => [],
    'actions' => [],
    'emptyMessage' => 'データがありません。',
    'pagination' => null
])

<div class="bg-white shadow-lg rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                    @endforeach
                    @if(count($actions) > 0)
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            操作
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50">
                        @foreach($row['data'] as $cell)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {!! $cell !!}
                            </td>
                        @endforeach
                        @if(count($actions) > 0)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                @foreach($actions as $action)
                                    @if($action['type'] === 'link')
                                        <a href="{{ str_replace(':id', $row['id'], $action['url']) }}" 
                                           class="text-{{ $action['color'] ?? 'blue' }}-600 hover:text-{{ $action['color'] ?? 'blue' }}-900">
                                            {{ $action['label'] }}
                                        </a>
                                    @elseif($action['type'] === 'form')
                                        <form method="POST" action="{{ str_replace(':id', $row['id'], $action['url']) }}" class="inline" 
                                              @if(isset($action['confirm'])) onsubmit="return confirm('{{ $action['confirm'] }}')" @endif>
                                            @csrf
                                            @if(isset($action['method']))
                                                @method($action['method'])
                                            @endif
                                            <button type="submit" class="text-{{ $action['color'] ?? 'red' }}-600 hover:text-{{ $action['color'] ?? 'red' }}-900">
                                                {{ $action['label'] }}
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + (count($actions) > 0 ? 1 : 0) }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($pagination && $pagination->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            {{ $pagination->links() }}
        </div>
    @endif
</div>