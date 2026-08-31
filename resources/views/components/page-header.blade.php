@props([

    'title',

    'subtitle' => ''

])

<div class="flex items-start justify-between mb-6">

    <div>

        <h1
            class="text-2xl font-bold text-emerald-900"
        >

            {{ $title }}

        </h1>

        @if($subtitle)

            <p
                class="text-slate-500 mt-1"
            >

                {{ $subtitle }}

            </p>

        @endif

    </div>

    <div>

        {{ $action ?? '' }}

    </div>

</div>