<div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-purple-500 to-pink-500">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-96">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Livewire Counter</h1>

        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-8 mb-6">
            <p class="text-white text-6xl font-bold text-center">{{ $count }}</p>
        </div>

        <div class="space-y-3">
            <button
                wire:click="increment"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105">
                ➕ Increment
            </button>

            <button
                wire:click="decrement"
                class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105">
                ➖ Decrement
            </button>

            <button
                wire:click="resetCounter"
                class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105">
                🔄 Reset
            </button>
        </div>

        <p class="text-gray-500 text-sm text-center mt-6">
            Click the buttons to see Livewire in action! ⚡
        </p>
    </div>
</div>
