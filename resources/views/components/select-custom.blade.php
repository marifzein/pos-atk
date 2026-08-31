@props([
    'label' => '',
    'name',
    'value' => '',
    'required' => false,
    'options' => []
])

@php
    $currentValue = old($name, $value);
@endphp

<div class="space-y-2" 
     x-data="{ 
        open: false, 
        value: '{{ $currentValue }}',
        init() {
            let activeOption = this.$refs.optionsContainer.querySelector(`[data-value='${this.value}']`);
            if(activeOption) {
                this.selectedLabel = activeOption.innerText.trim();
            }
        },
        selectedLabel: 'Pilih...',
        select(val, label) {
            this.value = val;
            this.selectedLabel = label;
            this.open = false;
        }
     }">
    
    @if($label)
        <label class="text-sm font-semibold text-slate-700 block select-none">
            {{ $label }}
            @if($required)

                <span class="text-red-500">*</span>

            @endif
        </label>
    @endif

    <div class="relative">
        <button 
            type="button" 
            @click="open = !open" 
            @click.outside="open = false"
            class="w-full flex justify-between items-center bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-700 outline-none focus:outline-none focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all duration-200 text-left cursor-pointer"
        >
            <span x-text="selectedLabel" class="truncate"></span>
            <i class="ri-arrow-down-s-line text-slate-400 transition-transform duration-200 text-lg" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <input type="hidden" name="{{ $name }}" :value="value">

        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            x-ref="optionsContainer"
            class="absolute {{ $attributes->get('direction') == 'up' ? 'bottom-full mb-1' : 'top-full mt-1' }} z-50 w-full bg-white border border-slate-200 rounded-lg shadow-xl max-h-60 overflow-y-auto"
            style="display: none;"
        >
            <div class="p-1 space-y-0.5">
                {{ $slot }}
            </div>
        </div>
    </div>

    @error($name)
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>