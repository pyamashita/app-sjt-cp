@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'options' => [],
    'rows' => 3,
    'colSpan' => 1,
    'helpText' => ''
])

<div class="col-span-{{ $colSpan }}">
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
        {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
    </label>
    
    @if($type === 'text' || $type === 'email' || $type === 'password' || $type === 'date' || $type === 'url')
        <input type="{{ $type }}" 
               id="{{ $name }}" 
               name="{{ $name }}" 
               value="{{ old($name, $value) }}"
               @if($required) required @endif
               @if($placeholder) placeholder="{{ $placeholder }}" @endif
               class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm @error($name) border-red-300 focus:ring-red-500 @enderror">
    
    @elseif($type === 'select')
        <select id="{{ $name }}" 
                name="{{ $name }}" 
                @if($required) required @endif
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm @error($name) border-red-300 focus:ring-red-500 @enderror">
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    
    @elseif($type === 'textarea')
        <textarea id="{{ $name }}" 
                  name="{{ $name }}" 
                  rows="{{ $rows }}"
                  @if($required) required @endif
                  @if($placeholder) placeholder="{{ $placeholder }}" @endif
                  class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm @error($name) border-red-300 focus:ring-red-500 @enderror">{{ old($name, $value) }}</textarea>
    
    @elseif($type === 'file')
        <input type="file" 
               id="{{ $name }}" 
               name="{{ $name }}" 
               @if($required) required @endif
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
    @endif
    
    @error($name)
        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
    @enderror
    
    @if($helpText)
        <p class="text-xs text-gray-500 mt-1">{{ $helpText }}</p>
    @endif
</div>