<x-layouts.app.header>
    <div class="moku-container">
        <h1>Device Information</h1>
        <a href="{{ route('moku.dashboard') }}" class="btn-back">← Back to Dashboard</a>

        <form method="GET" class="device-form">
            <label for="serial">Device Serial Number:</label>
            <input type="text" id="serial" name="serial" placeholder="Enter device serial" value="{{ $serial }}" required />
            <button type="submit" class="btn">Get Info</button>
        </form>

        @if ($serial && $success)
            <section class="device-info">
                <h2>Serial: {{ $serial }}</h2>
                <pre class="info-output">{{ $info }}</pre>
            </section>
        @elseif ($serial && !$success)
            <div class="alert alert-error">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif
    </div>
</x-layouts.app.header>
