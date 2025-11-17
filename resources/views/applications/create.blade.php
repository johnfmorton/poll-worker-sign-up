<x-public-layout>
    <x-slot:title>Poll Worker Registration</x-slot:title>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="mb-6">
                        <p class="text-gray-600 leading-relaxed">
                            Thank you for your interest in serving as a poll worker in Warren, CT. 
                            Please complete the form below to begin your registration. You will receive 
                            an email to verify your address.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('applications.store') }}" class="space-y-6">
                        @csrf

                        <!-- Name Field -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                value="{{ old('name') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }}"
                                required
                            >
                            @if($errors->has('name'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('name') }}</p>
                            @endif
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                value="{{ old('email') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('email') ? 'border-red-500' : '' }}"
                                required
                            >
                            @if($errors->has('email'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <!-- Street Address Field -->
                        <div>
                            <label for="street_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="street_address" 
                                id="street_address" 
                                value="{{ old('street_address') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('street_address') ? 'border-red-500' : '' }}"
                                placeholder="123 Main Street, Warren, CT"
                                required
                            >
                            @if($errors->has('street_address'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('street_address') }}</p>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between pt-4">
                            <p class="text-sm text-gray-500">
                                <span class="text-red-500">*</span> Required fields
                            </p>
                            <button 
                                type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            >
                                Submit Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
