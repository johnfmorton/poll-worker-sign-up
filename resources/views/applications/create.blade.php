<x-public-layout>
    <x-slot:title>Poll Worker Registration</x-slot:title>

    {{-- Page background + vertical centering --}}
    <div class=" ">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- Flash messages (kept, just slightly softened) --}}
            @if(session('success'))
                <div class="mb-8 rounded-xl border border-green-200 bg-green-50/90 px-6 py-4 shadow-sm backdrop-blur">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 shrink-0 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium text-green-900">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-8 rounded-xl border border-blue-200 bg-blue-50/90 px-6 py-4 shadow-sm backdrop-blur">
                    <div class="flex items-start">
                        <svg class="mr-3 mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium text-blue-900">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 rounded-xl border border-red-200 bg-red-50/90 px-6 py-4 shadow-sm backdrop-blur">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 shrink-0 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium text-red-900">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(!$registration_enabled)
                <div class="mb-8 rounded-xl border border-blue-200 bg-blue-50/90 px-6 py-5 shadow-sm backdrop-blur">
                    <div class="flex items-start">
                        <svg class="mr-3 mt-0.5 h-6 w-6 shrink-0 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-blue-950">Registration Currently Unavailable</h3>
                            <p class="mb-3 text-blue-900/90">Thank you for your interest in serving as a poll worker! Poll worker sign-up is currently turned off.</p>
                            <p class="text-sm text-blue-900/80">For questions about voter registration or upcoming elections, please contact the Warren Registrar's Office.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Hero heading (new, matches mockup) --}}
            {{-- <div class="mb-8 text-center text-white">
                <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Become a Poll Worker
                </h1>
                <p class="mt-2 text-sm text-white/80">
                    Town of Warren, Connecticut · Voter Registration Office
                </p>
            </div> --}}

            {{-- Two-Column Card --}}
            <div class="overflow-hidden rounded-2xl border border-white/20 bg-white/95 shadow-2xl ring-1 ring-slate-900/5 backdrop-blur">
                <div class="grid gap-0 lg:grid-cols-5">

                    {{-- Left Column --}}
                    <div class="lg:col-span-2 border-r border-slate-200 bg-gradient-to-b from-slate-50 to-white p-8 lg:p-10">
                        <h2 class="mb-6 text-2xl font-semibold tracking-tight text-slate-900">
                            Serve as a Warren Poll Worker
                        </h2>

                        <div class="mb-8 space-y-5">
                            <div class="flex items-start gap-3 rounded-xl bg-white/70 p-3 shadow-sm ring-1 ring-slate-100">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-sm leading-relaxed text-slate-700">
                                    <span class="font-semibold text-slate-900">Help neighbors vote</span>
                                    — Be the friendly face that makes Election Day run smoothly
                                </p>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white/70 p-3 shadow-sm ring-1 ring-slate-100">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <p class="text-sm leading-relaxed text-slate-700">
                                    <span class="font-semibold text-slate-900">Training provided</span>
                                    — Learn everything you need to know before each election
                                </p>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white/70 p-3 shadow-sm ring-1 ring-slate-100">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <p class="text-sm leading-relaxed text-slate-700">
                                    <span class="font-semibold text-slate-900">Support secure, fair elections</span>
                                    — Play a vital role in our democracy
                                </p>
                            </div>

                            <div class="flex items-start gap-3 rounded-xl bg-white/70 p-3 shadow-sm ring-1 ring-slate-100">
                                <svg class="mt-0.5 h-6 w-6 shrink-0 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                <p class="text-sm leading-relaxed text-slate-700">
                                    <span class="font-semibold text-slate-900">Requirements</span>
                                    — You must be a US citizen and a registered voter in Warren CT to serve
                                </p>
                            </div>
                        </div>

                        {{-- Next Steps Box --}}
                        <div class="rounded-xl border border-blue-200 bg-gradient-to-b from-white to-blue-50/30 p-6 shadow-sm">
                            <h3 class="mb-4 text-lg font-semibold text-slate-900">Next Steps</h3>
                            <ol class="space-y-3 text-sm text-slate-700">
                                <li class="flex items-start">
                                    <span class="mr-3 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-800 font-semibold">1</span>
                                    <span>Complete the registration form</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-3 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-800 font-semibold">2</span>
                                    <span>Confirm your email address</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="mr-3 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-800 font-semibold">3</span>
                                    <span>Before each election we will be in touch with more details</span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="lg:col-span-3 p-8 lg:p-10">
                        <h2 class="mb-8 text-2xl font-semibold tracking-tight text-slate-900">
                            Registration Form
                        </h2>

                        <form method="POST" action="{{ route('applications.store') }}" class="space-y-6">
                            @csrf

                            {{-- Name --}}
                            <div>
                                <label for="name" class="mb-2 block text-sm font-semibold text-slate-900">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm outline-none ring-blue-500/10 focus:border-blue-600 focus:ring-2 {{ $errors->has('name') ? 'border-red-500 ring-red-200/50 focus:border-red-500 focus:ring-red-200/70' : '' }} {{ !$registration_enabled ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                @if($errors->has('name'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
                                @endif
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-slate-900">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm outline-none ring-blue-500/10 focus:border-blue-600 focus:ring-2 {{ $errors->has('email') ? 'border-red-500 ring-red-200/50 focus:border-red-500 focus:ring-red-200/70' : '' }} {{ !$registration_enabled ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                <p class="mt-2 text-xs text-slate-500">
                                    We’ll use this to send your confirmation and training updates.
                                </p>
                                @if($errors->has('email'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
                                @endif
                            </div>

                            {{-- Street Address --}}
                            <div>
                                <label for="street_address" class="mb-2 block text-sm font-semibold text-slate-900">
                                    Street Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="street_address"
                                    id="street_address"
                                    value="{{ old('street_address') }}"
                                    placeholder="123 Main Street, Warren, CT"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-slate-900 shadow-sm outline-none ring-blue-500/10 focus:border-blue-600 focus:ring-2 {{ $errors->has('street_address') ? 'border-red-500 ring-red-200/50 focus:border-red-500 focus:ring-red-200/70' : '' }} {{ !$registration_enabled ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                    required
                                >
                                @if($errors->has('street_address'))
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('street_address') }}</p>
                                @endif
                            </div>

                            {{-- Submit --}}
                            <div class="space-y-4 pt-4">
                                <button
                                    type="submit"
                                    class="w-full rounded-lg px-6 py-4 text-base font-semibold shadow-md transition
                                           {{ $registration_enabled
                                                ? 'bg-gradient-to-b from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2'
                                                : 'bg-slate-300 text-slate-500 cursor-not-allowed' }}"
                                    {{ !$registration_enabled ? 'disabled' : '' }}
                                >
                                    Start Registration
                                </button>

                                <div class="flex items-start gap-2 text-xs text-slate-600">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Your information is kept secure by the Voter Registration Office.</span>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

           
        </div>
    </div>
</x-public-layout>