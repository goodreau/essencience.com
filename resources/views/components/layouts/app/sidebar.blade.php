<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" />
                <!-- Removed sidebar.collapse toggle -->
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @endauth
        </flux:sidebar>


        <!-- Mobile User Menu (static list) -->
        @auth
        <flux:header class="lg:hidden">
            <!-- Removed sidebar.toggle -->
            <flux:spacer />

            <div class="p-2">
                <div class="grid gap-2">
                    <div class="flex items-center gap-2">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                        <div class="grid">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1">
                            {{ __('Settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </flux:header>
        @endauth

        {{ $slot }}

    </body>
</html>
