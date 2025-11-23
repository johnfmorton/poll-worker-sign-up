<x-public-layout>
    <div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                        <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                        Invalid Verification Link
                    </h2>
                    <p class="text-gray-600 mb-6">
                        This verification link is not valid. The link may be incorrect or incomplete.
                    </p>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Need to register?</strong>
                        </p>
                        <p class="text-sm text-gray-600">
                            If you haven't registered yet, you can start a new application using the button below.
                        </p>
                    </div>
                    <div class="space-y-3">
                        <a href="/" class="block w-full px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Start New Registration
                        </a>
                        <a href="/" class="block w-full px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
