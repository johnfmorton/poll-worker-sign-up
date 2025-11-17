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
    <body class="font-sans antialiased bg-gradient-to-b from-gray-50 to-gray-100">
        <div class="min-h-screen flex flex-col">
            <!-- Public Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                Warren, CT Poll Worker Registration
                            </h1>
                            <p class="text-sm text-gray-600 mt-1">
                                Town of Warren Voter Registration Office
                            </p>
                        </div>
                        <a 
                            href="{{ route('login') }}" 
                            class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                        >
                            Admin Login
                        </a>
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
            <main class="flex-grow">
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
