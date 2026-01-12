<x-layouts.app.header>
    <div class="moku-container">
        <h1>Moku:Go Dashboard</h1>

        @if (!$installed)
            <div class="alert alert-error">
                <strong>Error:</strong> mokucli is not installed or not found in PATH.
                <p>Please install Liquid Instruments mokucli to use this interface.</p>
            </div>
        @else
            <nav class="moku-nav">
                <ul>
                    <li><a href="{{ route('moku.devices') }}">List Devices</a></li>
                    <li><a href="{{ route('moku.device-info') }}">Device Info</a></li>
                    <li><a href="{{ route('moku.execute') }}">Execute Command</a></li>
                </ul>
            </nav>

            <section class="moku-overview">
                <h2>Quick Status</h2>
                @if (count($devices) > 0)
                    <p class="success">{{ count($devices) }} device(s) found</p>
                @else
                    <p class="info">No devices connected</p>
                @endif
            </section>
        @endif
    </div>
</x-layouts.app.header>
