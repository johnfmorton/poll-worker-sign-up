<x-public-layout>
    <x-slot:title>Poll Worker Registration</x-slot:title>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-8 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(!$registration_enabled)
                <div class="mb-8 bg-blue-50 border border-blue-200 text-blue-900 px-6 py-5 rounded-lg shadow-sm">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Registration Currently Unavailable</h3>
                            <p class="mb-3">Thank you for your interest in serving as a poll worker! Poll worker sign-up is currently turned off.</p>
                            <p class="text-sm">For questions about voter registration or upcoming elections, please contact the Warren Registrar's Office.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Two-Column Card -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border-t-4 border-blue-600">
                <div class="grid lg:grid-cols-5 gap-0">
                    <!-- Left Column: Why This Matters -->
                    <div class="lg:col-span-2 bg-gray-50 p-8 lg:p-10 border-r border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">
                            Serve as a Warren Poll Worker
                        </h2>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-gray-700 leading-relaxed">
                                    <span class="font-semibold">Help neighbors vote</span> — Be the friendly face that makes Election Day run smoothly
                                </p>
                            </div>
                            
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <p class="text-gray-700 leading-relaxed">
                                    <span class="font-semibold">Training provided</span> — Learn everything you need to know before each election
                                </p>
                            </div>
                            
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="text-gray-700 leading-relaxed">
                                    <span class="font-semibold">Support secure, fair elections</span> — Play a vital role in our democracy
                                </p>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                <p class="text-gray-700 leading-relaxed">
                                    <span class="font-semibold">Requirements</span> — You must be a US citizen and a registered voter in Warren CT to serve
                                </p>
                            </div>
                            
                        </div>

                        <!-- Next Steps Box -->
                        <div class="bg-white border-2 border-blue-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Next Steps</h3>
                            <ol class="space-y-3 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-semibold mr-3 shrink-0">1</span>
                                    <span>Complete the registration form</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-semibold mr-3 shrink-0">2</span>
                                    <span>Confirm your email address</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-semibold mr-3 shrink-0">3</span>
                                    <span>Before each election we will be in touch with more details</span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Right Column: The Form -->
                    <div class="lg:col-span-3 p-8 lg:p-10">
                        <h2 class="text-2xl font-bold text-gray-900 mb-8">
                            Registration Form
                        </h2>

                        <form method="POST" action="{{ route('applications.store') }}" class="space-y-6">
                            @csrf

                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-base font-semibold text-gray-900 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name') }}"
                                    class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }} {{ !$registration_enabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                @if($errors->has('name'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
                                @endif
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-base font-semibold text-gray-900 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    value="{{ old('email') }}"
                                    class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('email') ? 'border-red-500' : '' }} {{ !$registration_enabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                <p class="mt-2 text-sm text-gray-600">
                                    We'll use this to send your confirmation and training updates.
                                </p>
                                @if($errors->has('email'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
                                @endif
                            </div>

                            <!-- Street Address Field -->
                            <div>
                                <label for="street_address" class="block text-base font-semibold text-gray-900 mb-2">
                                    Street Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="street_address" 
                                    id="street_address" 
                                    value="{{ old('street_address') }}"
                                    class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('street_address') ? 'border-red-500' : '' }} {{ !$registration_enabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    placeholder="123 Main Street, Warren, CT"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                @if($errors->has('street_address'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('street_address') }}</p>
                                @endif
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-6 space-y-4">
                                <button 
                                    type="submit" 
                                    class="w-full px-6 py-4 text-lg font-semibold rounded-lg transition-colors shadow-sm {{ $registration_enabled ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                >
                                    Submit Your Information
                                </button>
                                
                                <div class="flex items-start text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-gray-400 mr-2 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Your information is kept secure by the Voter Registration Office</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
