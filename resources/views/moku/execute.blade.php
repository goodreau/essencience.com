<x-layouts.app.header>
    <div class="moku-container">
        <h1>Execute Command</h1>
        <a href="{{ route('moku.dashboard') }}" class="btn-back">← Back to Dashboard</a>

        <form method="GET" class="command-form">
            <label for="command">mokucli Command:</label>
            <input type="text" id="command" name="command" placeholder="e.g., list, info SERIAL" value="{{ $command }}" required />
            <button type="submit" class="btn">Execute</button>
        </form>

        @if ($command)
            @if ($success)
                <section class="command-output">
                    <h2>Output</h2>
                    <pre class="output">{{ $output }}</pre>
                </section>
            @else
                <div class="alert alert-error">
                    <strong>Error:</strong> {{ $error }}
                    @if ($output)
                        <pre class="output">{{ $output }}</pre>
                    @endif
                </div>
            @endif
        @else
            <p class="info">Enter a mokucli command to execute.</p>
        @endif
    </div>
</x-layouts.app.header>
