<x-public-layout>
    <x-slot:title>Welcome - Poll Worker Registration</x-slot:title>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Welcome to Poll Worker Registration
                </h1>
                <p class="text-lg text-gray-600 mb-6">
                    Thank you for your interest in serving as a poll worker in Warren, Connecticut. 
                    Poll workers play a vital role in ensuring fair and accessible elections for all citizens.
                </p>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-blue-900 mb-3">What Poll Workers Do</h2>
                    <ul class="list-disc list-inside space-y-2 text-blue-800">
                        <li>Help voters check in and verify their registration</li>
                        <li>Assist with voting equipment and answer questions</li>
                        <li>Ensure polling places run smoothly and efficiently</li>
                        <li>Protect the integrity of the voting process</li>
                    </ul>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-green-900 mb-3">Requirements</h2>
                    <ul class="list-disc list-inside space-y-2 text-green-800">
                        <li>Must be a resident of Warren, CT</li>
                        <li>Must be available on election days</li>
                        <li>Training will be provided</li>
                        <li>Compensation provided for service</li>
                    </ul>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a 
                        href="{{ route('applications.create') }}"
                        class="flex-1 text-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                    >
                        Register Now
                    </a>
                    <a 
                        href="{{ route('login') }}"
                        class="flex-1 text-center px-8 py-4 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors"
                    >
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
