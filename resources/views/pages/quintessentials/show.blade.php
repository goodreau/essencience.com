<x-layouts.app.header>
    <section class="page">
        <div class="flex items-center space-x-4 mb-4">
            <span class="text-6xl">{{ $quintessential->icon }}</span>
            <div>
                <div class="text-sm font-semibold text-gray-500 mb-1">
                    Quintessential {{ $quintessential->number }}
                </div>
                <h1 class="page-title" style="color: {{ $quintessential->color }};">
                    {{ $quintessential->name }}
                </h1>
            </div>
        </div>
        <p class="page-lead">
            {{ $quintessential->description }}
        </p>
    </section>
</x-layouts.app.header>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8 border-t-4" style="border-top-color: {{ $quintessential->color }};">
            <div class="prose max-w-none">
                {!! nl2br(e($quintessential->content)) !!}
            </div>
        </div>

        <div class="mt-8">
            <a href="{{ route('quintessentials.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to All Quintessentials
            </a>
        </div>

        <!-- Navigation to previous/next quintessential -->
        <div class="mt-8 flex justify-between items-center">
            @if ($quintessential->number > 1)
                @php
                    $previous = App\Models\Quintessential::where('number', $quintessential->number - 1)->first();
                @endphp
                @if ($previous)
                    <a href="{{ route('quintessentials.show', $previous->slug) }}" 
                       class="flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ $previous->number }}. {{ $previous->name }}
                    </a>
                @endif
            @else
                <div></div>
            @endif

            @if ($quintessential->number < 10)
                @php
                    $next = App\Models\Quintessential::where('number', $quintessential->number + 1)->first();
                @endphp
                @if ($next)
                    <a href="{{ route('quintessentials.show', $next->slug) }}" 
                       class="flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                        {{ $next->number }}. {{ $next->name }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            @else
                <div></div>
            @endif
        </div>
    </div>
</div>
