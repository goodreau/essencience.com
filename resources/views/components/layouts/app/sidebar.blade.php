<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="layout-root">
        <aside class="layout-sidebar">
            <div class="layout-sidebar-header">
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" />
            </div>

            <nav class="layout-sidebar-nav">
                <h2 class="layout-sidebar-heading">{{ __('Platform') }}</h2>
                <ul>
                    <li><a href="{{ route('dashboard') }}" class="layout-sidebar-link">{{ __('Dashboard') }}</a></li>
                </ul>
            </nav>

            <div class="layout-sidebar-links">
                <ul>
                    <li><a href="https://github.com/laravel/livewire-starter-kit" target="_blank" class="layout-sidebar-link">{{ __('Repository') }}</a></li>
                    <li><a href="https://laravel.com/docs/starter-kits#livewire" target="_blank" class="layout-sidebar-link">{{ __('Documentation') }}</a></li>
                </ul>
            </div>

            @auth
                <div class="layout-desktop-user">
                    <x-desktop-user-menu class="visible-lg" :name="auth()->user()->name" />
                </div>
            @endauth
        </aside>

        <header class="layout-mobile-header">
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
        </header>

        <main class="layout-content">
            {{ $slot }}
        </main>
    </body>
</html>
