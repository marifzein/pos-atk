@props([
    'title',
    'subtitle' => '',
    'subtitleColor' => 'text-slate-500' // default tetap abu-abu jika tidak diisi
])

<div class="flex items-start justify-between mb-6">

    <div>

        <h1
            class="text-2xl font-bold text-emerald-900"
        >

            {{ $title }}

        </h1>

        @if($subtitle)

            <p class="{{ $subtitleColor }} mt-1 font-medium">
                {{ $subtitle }}
            </p>

        @endif

    </div>

    <div>

        {{ $action ?? '' }}

    </div>

</div>