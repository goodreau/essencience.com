<x-layouts.app.header>
    <section class="page">
        <h1 class="page-title">The Ten Quintessentials</h1>
        <p class="page-lead">
            The foundational principles of The Age of Quintessence—guiding lights for transformation, harmony, and sovereign expression.
        </p>
    </section>
</x-layouts.app.header>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($quintessentials as $quintessential)
            <a href="{{ route('quintessentials.show', $quintessential->slug) }}" 
               class="block p-6 bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-l-4"
               style="border-left-color: {{ $quintessential->color }};">
                <div class="flex items-start space-x-4">
                    <div class="text-4xl flex-shrink-0">
                        {{ $quintessential->icon }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-sm font-semibold text-gray-500">{{ $quintessential->number }}</span>
                            <h3 class="text-xl font-bold" style="color: {{ $quintessential->color }};">
                                {{ $quintessential->name }}
                            </h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            {{ $quintessential->description }}
                        </p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
