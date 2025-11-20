<x-admin-layout>
    <x-slot name="title">Admin Dashboard - Poll Worker Applications</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Poll Worker Applications
            </h2>
            <a 
                href="{{ route('admin.applications.export') }}"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 inline-flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export to CSV
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

            <!-- Filter Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Filter Applications</h3>
                    <form method="GET" action="{{ route('admin.applications.index') }}" class="space-y-4">
                        <!-- Search Input -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                                Search (Name, Email, or Address)
                            </label>
                            <input 
                                type="text" 
                                name="search" 
                                id="search" 
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Search applications..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Email Verification Filter -->
                            <div>
                                <label for="email_verified" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Verification
                                </label>
                                <select 
                                    name="email_verified" 
                                    id="email_verified"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All</option>
                                    <option value="yes" {{ ($filters['email_verified'] ?? '') === 'yes' ? 'selected' : '' }}>
                                        Verified
                                    </option>
                                    <option value="no" {{ ($filters['email_verified'] ?? '') === 'no' ? 'selected' : '' }}>
                                        Unverified
                                    </option>
                                </select>
                            </div>

                            <!-- Residency Status Filter -->
                            <div>
                                <label for="residency_status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Residency Status
                                </label>
                                <select 
                                    name="residency_status" 
                                    id="residency_status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All</option>
                                    <option value="pending" {{ ($filters['residency_status'] ?? '') === 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="approved" {{ ($filters['residency_status'] ?? '') === 'approved' ? 'selected' : '' }}>
                                        Approved
                                    </option>
                                    <option value="rejected" {{ ($filters['residency_status'] ?? '') === 'rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>
                                </select>
                            </div>

                            <!-- Party Affiliation Filter -->
                            <div>
                                <label for="party_affiliation" class="block text-sm font-medium text-gray-700 mb-1">
                                    Party Affiliation
                                </label>
                                <select 
                                    name="party_affiliation" 
                                    id="party_affiliation"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All</option>
                                    <option value="democrat" {{ ($filters['party_affiliation'] ?? '') === 'democrat' ? 'selected' : '' }}>
                                        Democrat
                                    </option>
                                    <option value="republican" {{ ($filters['party_affiliation'] ?? '') === 'republican' ? 'selected' : '' }}>
                                        Republican
                                    </option>
                                    <option value="independent" {{ ($filters['party_affiliation'] ?? '') === 'independent' ? 'selected' : '' }}>
                                        Independent
                                    </option>
                                    <option value="unaffiliated" {{ ($filters['party_affiliation'] ?? '') === 'unaffiliated' ? 'selected' : '' }}>
                                        Unaffiliated
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button 
                                type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Apply Filters
                            </button>
                            <a 
                                href="{{ route('admin.applications.index') }}"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Applications Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($applications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Address
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email Verified
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Residency
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Party
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($applications as $application)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $application->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $application->email }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $application->street_address }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($application->email_verified_at)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Verified
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Unverified
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($application->party_affiliation)
                                                    <span class="capitalize">{{ $application->party_affiliation }}</span>
                                                @else
                                                    <span class="text-gray-400">Not assigned</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a 
                                                    href="{{ route('admin.applications.show', $application->id) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $applications->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 text-lg">No applications found.</p>
                            @if(!empty($filters['search']) || !empty($filters['residency_status']) || !empty($filters['party_affiliation']) || isset($filters['email_verified']))
                                <p class="text-gray-400 text-sm mt-2">Try adjusting your filters.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
