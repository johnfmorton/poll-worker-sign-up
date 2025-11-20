<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Admin Dashboard - Poll Worker System' }}</title>
        <x-favicon />
 
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <!-- Admin Navigation -->
            <nav class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <h1 class="text-xl font-bold text-gray-900">
                                Poll Worker Admin
                            </h1>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <a 
                                href="{{ route('admin.dashboard') }}" 
                                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100' : '' }}"
                            >
                                Dashboard
                            </a>
                            <a 
                                href="{{ route('admin.applications.index') }}" 
                                class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'bg-gray-100' : '' }}"
                            >
                                Applications
                            </a>
                            
                            @auth
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600">
                                        {{ auth()->user()->name }}
                                    </span>
                                    <form method="POST" action="{{ route('logout') }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md font-medium hover:bg-gray-100 transition-colors"
                                        >
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
