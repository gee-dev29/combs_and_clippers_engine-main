@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="cnc:max-w-7xl cnc:mx-auto cnc:py-10 cnc:px-6 space-y-10">

    {{-- Top Section: Avatar and Summary --}}
    <div
        class="cnc:bg-white mt-4 cnc:rounded-2xl cnc:p-8 cnc:flex cnc:flex-col cnc:md:flex-row cnc:items-center cnc:gap-10">

        {{-- Profile Picture --}}
        <div class="cnc:w-40 cnc:h-40 mt-4 cnc:rounded-full cnc:overflow-hidden">
            <img src="{{ $user->profile_image_link ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                class="cnc:w-full cnc:h-full cnc:object-cover" alt="Profile Picture">
        </div>

        {{-- Basic Info --}}
        <div class="cnc:flex-1">
            <div class="cnc:flex cnc:items-center cnc:justify-between cnc:mb-4">
                <h1 class="cnc:text-4xl cnc:font-extrabold cnc:text-gray-800 flex items-center gap-4">
                    <i class="i-User cnc:text-blue-500 cnc:text-3xl"></i>
                    {{ $user->name }}
                </h1>
                <span
                    class="cnc:inline-block cnc:bg-blue-100 cnc:text-blue-600 cnc:text-base cnc:font-medium cnc:px-6 cnc:py-2 cnc:rounded-full">
                    {{ ucfirst($user->account_type ?? 'User') }}
                </span>
            </div>
            <p class="cnc:text-gray-600 cnc:text-lg"><i class="i-Mail cnc:text-gray-400 mr-2"></i>{{ $user->email ??
                'N/A' }}</p>
            <p class="cnc:text-gray-600 cnc:text-lg"><i class="i-Old-Telephone cnc:text-gray-400 mr-2"></i>{{
                $user->phone ??
                'N/A' }}</p>
            @if (isset($user->merchant_code))
            <p class="cnc:text-gray-600 cnc:text-lg"><i class="i-Tag cnc:text-gray-400 mr-2"></i>Merchant Code: {{
                $user->merchant_code ?? 'N/A' }}</p>
            @endif
        </div>
    </div>

    {{-- Account Info and Wallet Summary --}}
    <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-8">

        <!-- Account Info Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <!-- Card Header -->
            <div class="cnc:bg-blue-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-blue-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Wallet cnc:text-2xl"></i> Account Info
                </h2>
            </div>
            <!-- Card Body -->
            <div class="cnc:bg-white cnc:p-6 cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-x-6 cnc:gap-y-4">
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Wallet ID</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->wallet->wallet_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Balance</p>
                    <p class="cnc:text-lg cnc:font-semibold cnc:text-green-600">₦{{ number_format($user->wallet->balance
                        ??
                        0, 2) }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Store</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->store->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Subscription</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->subscriptionStatus() }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Has Store</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->hasStore() ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Has Product</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->hasProduct() ? 'Yes' : 'No' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Wallet Summary Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <!-- Card Header -->
            <div class="cnc:bg-yellow-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Wallet cnc:text-2xl"></i> Wallet Summary
                </h2>
            </div>
            <!-- Card Body -->
            <div class="cnc:bg-white cnc:p-6 cnc:space-y-5">
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Wallet ID</p>
                    <p class="cnc:text-lg cnc:font-medium cnc:text-gray-800">{{ $user->wallet->wallet_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="cnc:text-sm cnc:text-gray-500">Balance</p>
                    <p class="cnc:text-lg cnc:font-semibold cnc:text-green-600">₦{{ number_format($user->wallet->balance
                        ??
                        0, 2) }}</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Services --}}
    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6">
        <!-- Header -->
        <div class="cnc:bg-purple-100 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-purple-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Gear cnc:text-2xl"></i> Services ({{ $user->services->count() }})
            </h2>
        </div>

        <!-- Body -->
        <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6">
            @foreach($user->services->take(3) as $service)
            <div
                class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                <!-- Image + Name -->
                <div class="cnc:flex cnc:items-start cnc:gap-3 cnc:flex-1 cnc:md:max-w-[250px]">
                    @if($service->photos->first())
                    <img src="{{ $service->photos->first()->image_url }}"
                        class="cnc:w-16 cnc:h-16 cnc:rounded cnc:object-cover cnc:border" alt="">
                    @else
                    <div class="cnc:w-16 cnc:h-16 cnc:bg-gray-200 cnc:rounded"></div>
                    @endif

                    <div class="cnc:min-w-0">
                        <p class="cnc:font-medium cnc:text-sm cnc:text-gray-800 cnc:truncate">{{ $service->name }}</p>
                        <p class="cnc:text-xs cnc:text-gray-500 cnc:truncate">{!! $service->description !!}</p>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="cnc:flex-1 cnc:grid cnc:md:grid-cols-4 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                    <!-- Duration -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Duration</p>
                        <p class="cnc:text-gray-700">{{ $service->duration ?? 'N/A' }}</p>
                    </div>

                    <!-- Price -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Price</p>
                        <p class="cnc:text-green-600 cnc:font-semibold">₦{{ number_format($service->price, 2) }}</p>
                    </div>

                    <!-- Availability -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Availability</p>
                        @if($service->availabilityHours->count())
                        <span class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                            {{ $service->availabilityHours->count() }} day(s)
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($service->availabilityHours as $hour)
                                <div>
                                    {{ ucfirst($hour->day) }}:
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $hour->start_time)->format('g:i A') }}
                                    -
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $hour->end_time)->format('g:i A') }}
                                </div>
                                @endforeach
                            </div>
                        </span>
                        @else
                        <span class="cnc:text-gray-500">No hours</span>
                        @endif
                    </div>

                    <!-- Promotions -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Promotions</p>
                        @if($service->promotions->count())
                        <span class="group relative">
                            {{ $service->promotions->count() }} promo{{ $service->promotions->count() > 1 ? 's' : '' }}
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($service->promotions as $p)
                                <div>{{ $p->title }}</div>
                                @endforeach
                            </div>
                        </span>
                        @else
                        <span class="cnc:text-gray-500">No promo</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            @if($user->services->count() > 3)
            <div class="cnc:pt-4 cnc:text-right">
                <button class="btn-primary btn" data-toggle="modal" data-target="#allServicesModal">
                    See all services
                </button>
            </div>
            @endif
        </div>
    </div>


    {{-- Appointments and Bookings --}}
    <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-8 cnc:mt-6 cnc:pb-6">

        <!-- Appointments Card -->
        <div
            class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6 cnc:flex cnc:flex-col cnc:h-full">
            <!-- Header -->
            <div class="cnc:bg-blue-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-blue-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Calendar cnc:text-2xl"></i> Upcoming Appointments ({{ $user->appointments->count() }})
                </h2>
            </div>

            <!-- Body -->
            <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6 cnc:flex-1 cnc:flex cnc:flex-col">
                @foreach($user->appointments->take(4) as $appt)
                <!-- Adjusted to show exactly 6 appointments -->
                <div
                    class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                    <!-- Appointment Details -->
                    <div class="cnc:flex-1 cnc:grid cnc:truncate  cnc:grid-cols-6 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                        <div class="cnc:min-w-0 cnc:truncate">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>

                                        {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Status -->
                        <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">

                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $appt->status }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>

                                        {{ $appt->status }}
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Service Provider -->
                        <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">

                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $appt->serviceProvider->name }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>

                                        {{ $appt->serviceProvider->name }}
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Time -->
                        <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>

                                        {{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}
                                    </div>
                                </div>
                            </span>
                        </div>
                        <!-- services order -->
                        <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                Services : {{ $appt->appointmentService->count() }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>
                                        @foreach ($appt->appointmentService as $service)
                                        <div>
                                            {{ $service->service->name }} - {{ $service->price }}
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Payment Status -->
                        <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-green-500 cnc:truncate group">
                                {{ $appt->payment_status ? 'Paid' : 'Not Paid' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>

                                        {{ $appt->payment_status ? 'Paid' : 'Not Paid' }}
                                    </div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="cnc:pt-4 cnc:mt-auto cnc:text-right">
                    <button class="btn-primary btn" data-toggle="modal" data-target="#allAppointmentsModal">
                        See all appointments
                    </button>
                </div>

            </div>
        </div>

        <!-- Bookings Card -->
        <div
            class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6 cnc:flex cnc:flex-col cnc:h-full">
            <!-- Header -->
            <div class="cnc:bg-yellow-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Calendar cnc:text-2xl"></i> Upcoming Bookings ({{ $user->bookings->count() }})
                </h2>
            </div>

            <!-- Body -->
            <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6 cnc:flex-1 cnc:flex cnc:flex-col">
                @foreach($user->bookings->take(4) as $booking)
                <div
                    class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                    <!-- Booking Details -->
                    <div class="cnc:flex-1 cnc:grid cnc:truncate cnc:grid-cols-6 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">

                        <!-- Date -->
                        <div class="cnc:min-w-0 cnc:truncate">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>
                                        {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Status -->
                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $booking->status }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $booking->status }}</div>
                                </div>
                            </span>
                        </div>

                        <!-- Booker Name -->
                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $booking->customer->name ?? 'N/A' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $booking->customer->name ?? 'N/A' }}</div>
                                </div>
                            </span>
                        </div>

                        <!-- Time -->
                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}</div>
                                </div>
                            </span>
                        </div>

                        <!-- Booked Services -->
                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                Services: {{ $booking->appointmentService->count() }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>
                                        @foreach ($booking->appointmentService as $service)
                                        <div>{{ $service->service->name ?? 'N/A' }} - {{ $service->price }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </span>
                        </div>

                        <!-- Payment -->
                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span
                                class="cnc:text-sm {{ $booking->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }} cnc:truncate group">
                                {{ $booking->payment_status ? 'Paid' : 'Not Paid' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $booking->payment_status ? 'Paid' : 'Not Paid' }}</div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="cnc:pt-4 cnc:mt-auto cnc:text-right">
                    <button class="btn-primary btn" data-toggle="modal" data-target="#allBookingsModal">
                        See all bookings
                    </button>
                </div>

            </div>
        </div>

    </div>



    {{-- Work Done Images --}}
    <div
        class="cnc:bg-white mt-4 cnc:rounded-2xl cnc:shadow-md cnc:border cnc:overflow-hidden cnc:border-gray-100 cnc:mt-6">

        <div class="cnc:bg-green-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-2xl cnc:font-semibold cnc:mb-6"><i class="i-Yes cnc:text-2xl"></i>
                Work
                Done
            </h2>
        </div>
        <div class="cnc:flex cnc:gap-6 cnc:p-4">
            @forelse($user->workdoneImages->take(4) as $image)
            <div class="cnc:w-1/2 cnc:sm:w-1/3 mt-4 cnc:rounded-xl cnc:overflow-hidden">
                <img src="{{ $image->url }}" class="cnc:object-cover cnc:w-full cnc:h-full" alt="Work image">
            </div>
            @empty
            <p class="col-span-full cnc:text-sm cnc:text-gray-500">No work images uploaded.</p>
            @endforelse
        </div>
    </div>

    {{-- Additional Insights --}}
    {{-- Additional Insights --}}
    <div
        class="cnc:bg-white mt-4 cnc:rounded-2xl cnc:shadow-md cnc:border cnc:overflow-hidden cnc:border-gray-100 cnc:mt-6">

        <div class="cnc:bg-indigo-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-2xl cnc:font-semibold"><i class="i-Info cnc:text-blue-500 cnc:text-2xl"></i> Additional
                Insights
            </h2>
        </div>

        <div class="cnc:grid cnc:grid-cols-1 cnc:sm:grid-cols-2 cnc:md:grid-cols-3 cnc:gap-8 cnc:text-lg">

            {{-- Total Bookings --}}
            <div class="cnc:bg-gray-50 cnc:p-6 mt-4 cnc:rounded-xl">
                <p class="cnc:font-medium cnc:text-gray-600">Total Bookings</p>
                <p class="cnc:text-2xl cnc:font-bold cnc:text-blue-600">{{ $user->bookings->count() }}</p>
            </div>

            {{-- Billing History --}}
            <div class="cnc:bg-gray-50 cnc:p-6 mt-4 cnc:rounded-xl">
                <p class="cnc:font-medium cnc:text-gray-600">Billing History</p>
                <p class="cnc:text-lg cnc:text-gray-700">{{ $user->billingHistory }}</p>
            </div>

            {{-- Rented Stores --}}
            <div class="cnc:bg-gray-50 cnc:p-6 mt-4 cnc:rounded-xl">
                <p class="cnc:font-medium cnc:text-gray-600">Rented Stores</p>
                <ul class="cnc:mt-2">
                    @forelse($user->rentedStores as $store)
                    <li>{{ $store->name }}</li>
                    @empty
                    <li class="cnc:text-gray-500">None</li>
                    @endforelse
                </ul>
            </div>

            {{-- Rented Booths --}}
            <div class="cnc:bg-gray-50 cnc:p-6 mt-4 cnc:rounded-xl">
                <p class="cnc:font-medium cnc:text-gray-600">Rented Booths</p>
                <ul class="cnc:mt-2">
                    @forelse($user->rentedBooths as $booth)
                    <li>{{ $booth->store->store_name }}</li>
                    @empty
                    <li class="cnc:text-gray-500">None</li>
                    @endforelse
                </ul>
            </div>

            {{-- Favorite Stylists --}}
            <div class="cnc:bg-gray-50 cnc:p-6 mt-4 cnc:rounded-xl">
                <p class="cnc:font-medium cnc:text-gray-600">Favorite Stylists</p>
                <ul class="cnc:mt-2">
                    @forelse($user->favoriteStylists as $stylist)
                    <li>{{ $stylist->name }}</li>
                    @empty
                    <li class="cnc:text-gray-500">None</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</div>

{{--Service modal --}}
<div class="modal fade" id="allServicesModal" tabindex="-1" role="dialog" aria-labelledby="allServicesModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="allServicesModalTitle">
                    <i class="i-Toolbox cnc:text-blue-500 mr-2"></i> All Services ({{ $user->services->count() }})
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="cnc:overflow-auto">
                    <table
                        class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                        <thead class="cnc:bg-purple-100 cnc:text-black-700">
                            <tr>
                                <th class="cnc:p-4 cnc:font-semibold">Name</th>
                                <th class="cnc:p-4 cnc:font-semibold">Price</th>
                                <th class="cnc:p-4 cnc:font-semibold">Description</th>
                                <th class="cnc:p-4 cnc:font-semibold">Duration</th>
                                <th class="cnc:p-4 cnc:font-semibold">Availability</th>
                                <th class="cnc:p-4 cnc:font-semibold">Photos</th>
                                <th class="cnc:p-4 cnc:font-semibold">Promotions</th>
                            </tr>
                        </thead>
                        <tbody class="cnc:divide-y cnc:divide-gray-100 cnc:bg-white">
                            @foreach($user->services as $service)
                            <tr class="cnc:hover:bg-gray-50 transition">
                                <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $service->name }}</td>
                                <td class="cnc:p-4 cnc:text-green-600 cnc:font-semibold">₦{{
                                    number_format($service->price, 2) }}</td>
                                <td class="cnc:p-4 cnc:text-gray-600">{!! $service->description !!}</td>
                                <td class="cnc:p-4 cnc:text-gray-600">{{ $service->duration ?? 'N/A' }}</td>
                                <td class="cnc:p-4">
                                    <div class="cnc:text-sm">
                                        @if($service->availabilityHours->count())
                                        <span
                                            class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                                            {{ $service->availabilityHours->count() }} day(s)
                                            <span class="cnc:text-gray-400">...</span>
                                            <div
                                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                                @foreach($service->availabilityHours as $hour)
                                                <div>
                                                    {{ ucfirst($hour->day) }}:
                                                    {{ \Carbon\Carbon::createFromFormat('H:i:s',
                                                    $hour->start_time)->format('g:i A') }}
                                                    -
                                                    {{ \Carbon\Carbon::createFromFormat('H:i:s',
                                                    $hour->end_time)->format('g:i A') }}
                                                </div>
                                                @endforeach
                                            </div>
                                        </span>
                                        @else
                                        <span class="cnc:text-gray-500">No hours</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="cnc:p-4">
                                    @forelse($service->photos->take(2) as $photo)
                                    <img src="{{ $photo->image_url }}"
                                        class="cnc:w-10 cnc:h-10 cnc:rounded cnc:object-cover inline-block mr-1"
                                        alt="photo">
                                    @empty
                                    <div class="cnc:text-gray-500">No photos</div>
                                    @endforelse
                                </td>
                                <td class="cnc:p-4">
                                    <div class="cnc:text-sm">

                                        @if($service->promotions->count())
                                        <span class="group relative">
                                            {{ $service->promotions->count() }} promo{{ $service->promotions->count() >
                                            1 ? 's' : '' }}
                                            <div
                                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                                @foreach($service->promotions as $p)
                                                <div>{{ $p->title }}</div>
                                                @endforeach
                                            </div>
                                        </span>
                                        @else
                                        <span class="cnc:text-gray-500">No promo</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                <button class="btn btn-primary ml-2" type="button">Save changes</button>
            </div>

        </div>
    </div>
</div>

<!-- Appointments Modal -->
<div class="modal fade" id="allAppointmentsModal" tabindex="-1" role="dialog"
    aria-labelledby="allAppointmentsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="allAppointmentsModalTitle">
                    <i class="i-Calendar cnc:text-blue-500 mr-2"></i> All Appointments ({{ $user->appointments->count()
                    }})
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <div class="cnc:overflow-auto">
                    <table
                        class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                        <thead class="cnc:bg-blue-100 cnc:text-black-700">
                            <tr>
                                <th class="cnc:p-4 cnc:font-semibold">Date</th>
                                <th class="cnc:p-4 cnc:font-semibold">Time</th>
                                <th class="cnc:p-4 cnc:font-semibold">Status</th>
                                <th class="cnc:p-4 cnc:font-semibold">Provider</th>
                                <th class="cnc:p-4 cnc:font-semibold">Services</th>
                                <th class="cnc:p-4 cnc:font-semibold">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="cnc:divide-y">
                            @foreach ($user->appointments as $appt)
                            <tr class="hover:bg-gray-50">
                                <td class="cnc:p-4">{{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}</td>
                                <td class="cnc:p-4">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</td>
                                <td class="cnc:p-4">{{ $appt->status }}</td>
                                <td class="cnc:p-4">{{ $appt->serviceProvider->name ?? 'N/A' }}</td>
                                <td class="cnc:p-4">
                                    <div class="cnc:text-sm cnc:truncate  cnc:hover:bg-white cnc:hover:z-10">
                                        <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                            Services : {{ $appt->appointmentService->count() }}
                                            <div
                                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                                <div>
                                                    @foreach ($appt->appointmentService as $service)
                                                    <div>
                                                        {{ $service->service->name }} - {{ $service->price }}
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                                <td class="cnc:p-4">
                                    <span
                                        class="{{ $appt->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }}">
                                        {{ $appt->payment_status ? 'Paid' : 'Not Paid' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                <button class="btn btn-primary ml-2" type="button">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bookings Modal -->
<div class="modal fade" id="allBookingsModal" tabindex="-1" role="dialog" aria-labelledby="allBookingsModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="allBookingsModalTitle">
                    <i class="i-Calendar cnc:text-yellow-500 mr-2"></i> All Bookings ({{ $user->bookings->count() }})
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4">
                <div class="cnc:overflow-auto">
                    <table
                        class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                        <thead class="cnc:bg-yellow-100 cnc:text-black-700">
                            <tr>
                                <th class="cnc:p-4 cnc:font-semibold">Date</th>
                                <th class="cnc:p-4 cnc:font-semibold">Time</th>
                                <th class="cnc:p-4 cnc:font-semibold">Status</th>
                                <th class="cnc:p-4 cnc:font-semibold">Customer</th>
                                <th class="cnc:p-4 cnc:font-semibold">Services</th>
                                <th class="cnc:p-4 cnc:font-semibold">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="cnc:divide-y">
                            @foreach ($user->bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="cnc:p-4">{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</td>
                                <td class="cnc:p-4">{{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}</td>
                                <td class="cnc:p-4">{{ $booking->status }}</td>
                                <td class="cnc:p-4">{{ $booking->customer->name ?? 'N/A' }}</td>
                                <td class="cnc:p-4">
                                    <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                        Services: {{ $booking->appointmentService->count() }}
                                        <div
                                            class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                            <div>
                                                @foreach ($booking->appointmentService as $service)
                                                <div>{{ $service->service->name ?? 'N/A' }} - {{ $service->price }}
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </span>
                                </td>
                                <td class="cnc:p-4">
                                    <span
                                        class="{{ $booking->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }}">
                                        {{ $booking->payment_status ? 'Paid' : 'Not Paid' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                <button class="btn btn-primary ml-2" type="button">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Add this script for mobile tap/focus support
            document.querySelectorAll('.group').forEach(item => {
            item.addEventListener('click', (e) => {
            const tooltip = e.target.querySelector('.tooltip-content');
            console.log("somethong",tooltip)
            if (tooltip) {
            tooltip.classList.toggle('cnc:hidden'); // Toggle the hidden class to show/hide tooltip
            }
            });

            item.addEventListener('mouseover', (e) => {
            const tooltip = e.target.querySelector('.tooltip-content');
            console.log("somethong",tooltip)
            if (tooltip) {
            tooltip.classList.remove('cnc:hidden'); // Show tooltip on focus
            }
            });

            item.addEventListener('mouseleave', (e) => {
            const tooltip = e.target.querySelector('.tooltip-content');
            console.log("somethong")
            if (tooltip) {
            tooltip.classList.add('cnc:hidden'); // Hide tooltip when focus is lost
            }
            });

            item.addEventListener('blur', (e) => {
            const tooltip = e.target.querySelector('.tooltip-content');
            console.log("somethong")
            if (tooltip) {
            tooltip.classList.add('cnc:hidden'); // Hide tooltip when focus is lost
            }
            });
            });
</script>
@endsection