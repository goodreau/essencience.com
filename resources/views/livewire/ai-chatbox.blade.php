<div class="flex flex-col h-screen max-w-2xl mx-auto p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-3xl font-bold text-white">
                <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                    AI Assistant
                </span>
            </h1>
            <button
                wire:click="toggleMode"
                class="px-4 py-2 rounded-lg font-semibold transition-all duration-200
                {{ $mode === 'chat' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-purple-600 hover:bg-purple-700 text-white' }}"
            >
                Mode: <span class="uppercase">{{ $mode }}</span>
            </button>
        </div>
        
        <div class="bg-slate-700 rounded-lg p-3 text-sm text-slate-300">
            @if($mode === 'chat')
                💬 Chat Mode - Direct conversation with AI
            @else
                🔍 RAG Mode - Context-aware answers from knowledge base
            @endif
        </div>
    </div>

    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto mb-6 space-y-4 pr-2">
        @if(empty($messages))
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="text-6xl mb-4">🤖</div>
                    <p class="text-slate-400 text-lg">
                        Start a conversation with the AI Assistant
                    </p>
                    <p class="text-slate-500 text-sm mt-2">
                        {{ $mode === 'rag' ? 'Your questions will be answered using relevant context' : 'Ask anything and get instant responses' }}
                    </p>
                </div>
            </div>
        @else
            @foreach($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-lg
                        {{ $message['role'] === 'user' 
                            ? 'bg-blue-600 text-white rounded-br-none' 
                            : 'bg-slate-700 text-slate-100 rounded-bl-none' }}">
                        {{ $message['content'] }}
                    </div>
                </div>
            @endforeach
        @endif

        @if($isLoading)
            <div class="flex justify-start">
                <div class="bg-slate-700 text-slate-100 px-4 py-3 rounded-lg rounded-bl-none">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        @endif

        @if($aiResponse && !$isLoading)
            <div class="flex justify-start">
                <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-lg bg-slate-700 text-slate-100 rounded-bl-none">
                    {{ $aiResponse }}
                </div>
            </div>
        @endif
    </div>

    <!-- Input Area -->
    <div class="space-y-3">
        @if(!empty($messages))
            <button
                wire:click="clearHistory"
                class="w-full px-3 py-2 text-sm text-slate-400 hover:text-slate-200 transition-colors duration-200"
            >
                Clear History
            </button>
        @endif

        <div class="flex gap-2">
            <input
                wire:model="userMessage"
                wire:keydown.enter="sendMessage"
                type="text"
                placeholder="Type your message..."
                :disabled="$isLoading"
                class="flex-1 px-4 py-3 rounded-lg bg-slate-700 text-white placeholder-slate-500
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-0
                    disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
            />
            <button
                wire:click="sendMessage"
                :disabled="empty(trim($userMessage)) || $isLoading"
                class="px-6 py-3 rounded-lg font-semibold bg-gradient-to-r from-blue-600 to-cyan-600
                    hover:from-blue-700 hover:to-cyan-700 text-white transition-all duration-200
                    disabled:opacity-50 disabled:cursor-not-allowed"
            >
                @if($isLoading)
                    <span class="inline-block animate-spin mr-2">⟳</span>
                    Thinking...
                @else
                    Send
                @endif
            </button>
        </div>
    </div>

    <!-- Footer Info -->
    <div class="mt-4 text-center text-xs text-slate-500">
        Powered by Ollama • {{ config('ollama.default_model') }}
    </div>
</div>
