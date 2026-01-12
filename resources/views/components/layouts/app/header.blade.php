<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="layout-root">
        <header class="layout-header">
            <div class="layout-header-left">
                <x-app-logo href="{{ route('dashboard') }}" />
            </div>
            <nav class="layout-nav">
                <ul>
                    <li><a href="{{ route('dashboard') }}" class="layout-nav-link">{{ __('Dashboard') }}</a></li>
                </ul>
            </nav>
            <div class="layout-header-right">
                @auth
                    <x-desktop-user-menu />
                @endauth
            </div>
        </header>

        <aside class="layout-mobile-header">
            <div class="layout-mobile-user">
                @auth
                    <div class="layout-profile">
                        <span class="layout-profile-name">{{ auth()->user()->name }}</span>
                        <span class="layout-profile-email">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="layout-profile-actions">
                        <a href="{{ route('profile.edit') }}" class="layout-action-link">{{ __('Settings') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="layout-action-link" data-test="logout-button">{{ __('Log Out') }}</button>
                        </form>
                    </div>
                @endauth
            </div>
        </aside>

        <main class="layout-content">
            {{ $slot }}
        </main>
    </body>
</html>
