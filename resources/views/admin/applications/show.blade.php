<x-admin-layout>
    <x-slot name="title">Application Details - {{ $application->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Application Details
            </h2>
            <a 
                href="{{ route('admin.applications.index') }}" 
                class="text-sm text-gray-600 hover:text-gray-900 underline"
            >
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Application Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Applicant Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <p class="text-gray-900">{{ $application->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <p class="text-gray-900">{{ $application->email }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                            <p class="text-gray-900">{{ $application->street_address }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Verification Status</label>
                            @if($application->email_verified_at)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Verified on {{ $application->email_verified_at->format('M d, Y g:i A') }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Unverified
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application Submitted</label>
                            <p class="text-gray-900">{{ $application->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Residency Validation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Residency Validation</h3>
                    
                    <!-- Current Status -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Status</label>
                        @if($application->residency_status === 'approved')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Approved
                            </span>
                        @elseif($application->residency_status === 'rejected')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Rejected
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Pending
                            </span>
                        @endif
                    </div>

                    <!-- Validation History -->
                    @if($application->residency_validated_at)
                        <div class="mb-4 p-4 bg-gray-50 rounded-md">
                            <p class="text-sm text-gray-700">
                                <strong>Validated by:</strong> {{ $application->residencyValidator->name ?? 'Unknown' }}
                            </p>
                            <p class="text-sm text-gray-700">
                                <strong>Validated on:</strong> {{ $application->residency_validated_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    <!-- Validation Controls -->
                    <form method="POST" action="{{ route('admin.applications.updateResidency', $application->id) }}" class="flex gap-2">
                        @csrf
                        <button 
                            type="submit" 
                            name="residency_status" 
                            value="approved"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Approve Residency
                        </button>
                        <button 
                            type="submit" 
                            name="residency_status" 
                            value="rejected"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Reject Residency
                        </button>
                    </form>
                </div>
            </div>

            <!-- Party Affiliation Assignment -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Party Affiliation</h3>
                    
                    <!-- Current Assignment -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Assignment</label>
                        @if($application->party_affiliation)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                {{ $application->party_affiliation }}
                            </span>
                        @else
                            <span class="text-gray-400">Not assigned</span>
                        @endif
                    </div>

                    <!-- Assignment History -->
                    @if($application->party_assigned_at)
                        <div class="mb-4 p-4 bg-gray-50 rounded-md">
                            <p class="text-sm text-gray-700">
                                <strong>Assigned by:</strong> {{ $application->partyAssigner->name ?? 'Unknown' }}
                            </p>
                            <p class="text-sm text-gray-700">
                                <strong>Assigned on:</strong> {{ $application->party_assigned_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    @endif

                    <!-- Assignment Controls -->
                    <form method="POST" action="{{ route('admin.applications.updateParty', $application->id) }}" class="flex gap-2 items-end">
                        @csrf
                        <div class="flex-1">
                            <label for="party_affiliation" class="block text-sm font-medium text-gray-700 mb-1">
                                Select Party
                            </label>
                            <select 
                                name="party_affiliation" 
                                id="party_affiliation"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                                required
                            >
                                <option value="">-- Select Party --</option>
                                <option value="democrat" {{ $application->party_affiliation === 'democrat' ? 'selected' : '' }}>
                                    Democrat
                                </option>
                                <option value="republican" {{ $application->party_affiliation === 'republican' ? 'selected' : '' }}>
                                    Republican
                                </option>
                                <option value="independent" {{ $application->party_affiliation === 'independent' ? 'selected' : '' }}>
                                    Independent
                                </option>
                                <option value="unaffiliated" {{ $application->party_affiliation === 'unaffiliated' ? 'selected' : '' }}>
                                    Unaffiliated
                                </option>
                            </select>
                        </div>
                        <button 
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Assign Party
                        </button>
                    </form>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    <div class="flex flex-wrap gap-2">
                        <!-- Edit Button -->
                        <a 
                            href="{{ route('admin.applications.edit', $application->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Edit Application
                        </a>

                        <!-- Resend Verification Email (only for unverified) -->
                        @if(!$application->email_verified_at)
                            <form method="POST" action="{{ route('admin.applications.resendVerification', $application->id) }}" class="inline">
                                @csrf
                                <button 
                                    type="submit"
                                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                                >
                                    Resend Verification Email
                                </button>
                            </form>
                        @endif

                        <!-- Delete Button -->
                        <form 
                            method="POST" 
                            action="{{ route('admin.applications.destroy', $application->id) }}" 
                            class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this application? This action cannot be undone.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            >
                                Delete Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
