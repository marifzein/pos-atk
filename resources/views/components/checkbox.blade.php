@props([
    'label' => '',
    'name',
    'value' => '1',
    'checked' => false,
    'required' => false, 
    'icon' => null,
    'width' => 'full',
])

@php
    $inputId = $attributes->get('id', $name);
    
    // FIX BUG: Jika ada sesi old (form gagal submit), ikuti old. 
    // Jika tidak ada sesi old (pertama kali dimuat), ikuti parameter $checked dari luar secara mutlak.
    $shouldBeChecked = session()->has('_old_input') 
        ? old($name) == $value 
        : filter_var($checked, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="space-y-1">

    <div class="flex items-center gap-2 relative group mt-6">
        
        @if($icon)
            <i class="{{ $icon }} absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-green-600 transition-all duration-200 text-lg"></i>
        @endif    
        
        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $value }}"
            {{ $shouldBeChecked ? 'checked' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => '
                    w-4 
                    h-4 
                    text-green-600 
                    border-slate-300 
                    rounded
                    bg-white
                    focus:ring-green-500
                    transition-all 
                    duration-200 
                    cursor-pointer
                '
            ]) }}
        >

        @if($label)
            <label
                for="{{ $inputId }}"
                class="text-sm font-medium text-slate-700 select-none cursor-pointer"
            >
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
        @endif

    </div>

    @error($name)
        <p class="text-xs text-red-500 pl-6">
            {{ $message }}
        </p>
    @enderror

</div>