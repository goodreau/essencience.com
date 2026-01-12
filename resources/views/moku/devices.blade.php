<x-layouts.app.header>
    <div class="moku-container">
        <h1>Moku:Go Devices</h1>
        <a href="{{ route('moku.dashboard') }}" class="btn-back">← Back to Dashboard</a>

        @if ($success)
            @if (count($devices) > 0)
                <table class="device-table">
                    <thead>
                        <tr>
                            <th>Device Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr>
                                <td>{{ $device }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="info">No devices found. Connect a Moku:Go device and try again.</p>
            @endif
        @else
            <div class="alert alert-error">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif
    </div>
</x-layouts.app.header>
