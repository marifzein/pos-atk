@props([
    'value'
])

<div 
    @click="select('{{ $value }}', $el.innerText.trim())"
    data-value="{{ $value }}"
    :class="value == '{{ $value }}' ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
    class="px-4 py-2.5 text-sm rounded-md cursor-pointer transition-colors duration-150 flex items-center justify-between"
>
    <span>{{ $slot }}</span>
    <!-- Indikator Checkmark jika terpilih -->
    <i class="ri-check-line text-green-600 text-base" x-show="value == '{{ $value }}'"></i>
</div>