<x-public-layout>
    <div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <div class="text-center">
                    @if($already_verified ?? false)
                        {{-- Already verified message --}}
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                            Already Verified
                        </h2>
                        <p class="text-gray-600 mb-6">
                            Your account has already been verified. Thank you for signing up to become a poll worker!
                        </p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-800">
                                Your application will be reviewed by the voter registrar's office. You will be contacted once your residency has been verified and party affiliation has been assigned.
                            </p>
                        </div>
                    @else
                        {{-- Expired link message --}}
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                            Verification Link Expired
                        </h2>
                        <p class="text-gray-600 mb-6">
                            This verification link has expired or is invalid. Verification links are valid for 48 hours after registration.
                        </p>

                        @if(session('success'))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <p class="text-sm text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        @endif

                        @if($email ?? null)
                            {{-- Show resend form if we have the email --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <p class="text-sm text-yellow-800 mb-3">
                                    <strong>Need a new verification link?</strong>
                                </p>
                                <form method="POST" action="{{ route('verification.resend', ['email' => $email]) }}">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors"
                                    >
                                        Send New Verification Email
                                    </button>
                                </form>
                            </div>
                        @else
                            {{-- Fallback message if we don't have the email --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <p class="text-sm text-yellow-800 mb-2">
                                    <strong>Need a new verification link?</strong>
                                </p>
                                <p class="text-sm text-yellow-800">
                                    Please contact the voter registrar's office to request a new verification email.
                                </p>
                            </div>
                        @endif
                    @endif

                    <div class="space-y-3">
                        <a href="/" class="block w-full px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
