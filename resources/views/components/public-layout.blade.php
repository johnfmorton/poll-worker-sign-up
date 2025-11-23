<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Poll Worker Registration - Warren, CT' }}</title>
        <x-favicon />
 
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-linear-to-b from-gray-50 to-gray-100">
        <div class="min-h-screen flex flex-col">
            <!-- Public Header -->
            <header class="bg-blue-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-3">
                    <span class="mx-2">🇺🇸</span> Become a Poll Worker <span class="mx-2">🇺🇸</span>
                </h1>
                <p class="text-blue-100 text-lg max-w-2xl mx-auto">
                    Town of Warren, Connecticut · Voter Registration Office
                </p>
            </div>
        </div>
            </header>

            <!-- Page Heading -->
            @isset($header)
                <div class="bg-white border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset
            
            <!-- Page Content -->
            <main class="flex-grow bg-gradient-to-b from-blue-700 via-red-500 to-slate-50">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="text-center text-sm text-gray-600">
                        <p>&copy; {{ date('Y') }} Town of Warren, Connecticut</p>
                        <p class="mt-1">For questions, please contact the Voter Registration Office</p>
                    </div>
                </div>
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
