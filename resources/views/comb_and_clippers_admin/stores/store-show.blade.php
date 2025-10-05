@extends('layouts.app')

@section('content')

<style>
    input,
    select {
        height: 40px;
        font-size: 12px;
        padding: 10px;
    }
</style>
<div class="main-content cnc:px-6 cnc:py-8">

    <!-- Breadcrumb -->
    <div class="breadcrumb cnc:mb-10">
        <i class="i-Shop"></i>
        <h1 class="cnc:text-4xl cnc:font-extrabold cnc:text-gray-900 cnc:tracking-tight">Store Details</h1>
    </div>

    <!-- Separator -->
    <div class="cnc:border-t cnc:border-gray-300 cnc:mb-10"></div>

    <!-- Store Cards Grid -->
    <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-8">

        <!-- Basic Info Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <div class="cnc:bg-blue-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-blue-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Shop"></i> Store Info
                </h2>
            </div>
            <div class="cnc:bg-white cnc:p-6 cnc:flex">
                @if($store->store_icon)
                <img src="{{ asset('storage/store/icons/'.$store->store_icon) }}" alt="Store Icon"
                    class="cnc:w-[250px] cnc:square cnc:mr-4 cnc:object-cover">
                @else
                <div
                    class="cnc:w-[250px] cnc:square cnc:mr-4 cnc:bg-gray-200 cnc:flex cnc:items-center cnc:justify-center">
                    <i class="i-Shop cnc:text-5xl cnc:text-gray-400"></i>
                </div>
                @endif
                <div class="cnc:space-y-3">
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Name:</strong> {{ $store->store_name }}</p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Code:</strong> {{ $store->store_code }}</p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Website:</strong>
                        @if($store->website)
                        <a href="{{ $store->website }}" class="cnc:text-blue-600 hover:cnc:underline" target="_blank">{{
                            $store->website }}</a>
                        @else
                        N/A
                        @endif
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Category:</strong>
                        {{ $store->category ? $store->category->name : $store->store_category }}
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Sub Category:</strong>
                        {{ $store->subCategory ? $store->subCategory->name : $store->store_sub_category }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Merchant Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <div class="cnc:bg-indigo-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-indigo-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Add-UserStar"></i> Owner Info
                </h2>
            </div>
            <div class="cnc:bg-white cnc:p-6 cnc:flex">
                @if($store->owner && $store->owner->profile_picture)
                <img src="{{ $store->owner->profile_picture }}" alt="Owner Image"
                    class="cnc:w-[250px] cnc:square cnc:mr-4 cnc:object-cover">
                @else
                <div
                    class="cnc:w-[250px] cnc:square cnc:mr-4 cnc:bg-gray-200 cnc:flex cnc:items-center cnc:justify-center">
                    <i class="i-Add-UserStar cnc:text-5xl cnc:text-gray-400"></i>
                </div>
                @endif
                <div class="cnc:space-y-3">
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Name:</strong>
                        {{ $store->owner ? $store->owner->name : 'N/A' }}
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Email:</strong>
                        {{ $store->owner ? $store->owner->email : 'N/A' }}
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Merchant ID:</strong> {{ $store->merchant_id }}
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Merchant CODE:</strong> {{
                        $store->owner->merchant_code }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Banner Card -->

    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-yellow-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Image"></i> Store Banner
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6">
            <img src="{{ asset('storage/store/banners/'.$store->store_banner) }}" alt="{{ $store->store_name }} Banner"
                class="cnc:w-full cnc:h-64 cnc:object-cover cnc:rounded-lg">
        </div>
    </div>


    <!-- Store Description Card -->
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-green-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-green-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-File-TextImage"></i> Store Description
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6">
            <p class="cnc:text-base cnc:text-gray-600">{{ $store->store_description }}</p>
        </div>
    </div>

    <!-- Store Status Card -->
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-purple-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-purple-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Flag"></i> Store Status
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6 cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-6">
            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Featured:</strong>
                    <span
                        class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $store->featured ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                        {{ $store->featured ? 'Yes' : 'No' }}
                    </span>
                </p>
            </div>

            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Approved:</strong>
                    <span
                        class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $store->approved ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                        {{ $store->approved ? 'Yes' : 'No' }}
                    </span>
                </p>
            </div>

            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Refund Allowed:</strong>
                    <span
                        class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $store->refund_allowed ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                        {{ $store->refund_allowed ? 'Yes' : 'No' }}
                    </span>
                </p>
            </div>

            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Replacement Allowed:</strong>
                    <span
                        class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $store->replacement_allowed ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                        {{ $store->replacement_allowed ? 'Yes' : 'No' }}
                    </span>
                </p>
            </div>

            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Days Available:</strong>
                    @if($store->days_available && is_array(json_decode($store->days_available)))
                    {{ implode(', ', json_decode($store->days_available)) }}
                    @else
                    N/A
                    @endif
                </p>
            </div>

            <div>
                <p class="cnc:text-base cnc:text-gray-600">
                    <strong>Time Available:</strong>
                    @if($store->time_available && is_array(json_decode($store->time_available)))
                    @foreach(json_decode($store->time_available) as $time)
                <p class="cnc:text-sm cnc:mt-1">
                    {{ $time->day ?? null }}: {{ $time->time ?? null }}
                </p>
                @endforeach
                @else
                N/A
                @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Store Address Card -->

    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-teal-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-teal-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Map-Marker"></i> Store Address
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6 cnc:space-y-3">
            <p class="cnc:text-base cnc:text-gray-600"><strong>Address:</strong> {{ $store->storeAddress->address_line1
                }}</p>
            @if($store->storeAddress->address_line2)
            <p class="cnc:text-base cnc:text-gray-600"><strong>Address 2:</strong> {{
                $store->storeAddress->address_line2 }}</p>
            @endif
            <p class="cnc:text-base cnc:text-gray-600"><strong>City:</strong> {{ $store->storeAddress->city }}</p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>State:</strong> {{ $store->storeAddress->state }}</p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>Postal Code:</strong> {{
                $store->storeAddress->postal_code }}</p>
        </div>
    </div>


    <!-- Services Card -->

    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-8">
        <!-- Header -->
        <div class="cnc:bg-amber-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-amber-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Receipt cnc:text-2xl"></i> Services ({{ $store->services->count() }})
            </h2>
        </div>

        <!-- Body -->
        <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6">
            @foreach($store->services as $service)
            <div
                class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                <!-- Image + Name -->
                <div class="cnc:flex cnc:items-start cnc:gap-3 cnc:flex-1 cnc:md:max-w-[250px]">
                    @if($service->photos && $service->photos->first())
                    <img src="{{ $service->photos->first()->image_url }}"
                        class="cnc:w-16 cnc:h-16 cnc:rounded cnc:object-cover cnc:border" alt="">
                    @else
                    <div class="cnc:w-16 cnc:h-16 cnc:bg-gray-200 cnc:rounded"></div>
                    @endif

                    <div class="cnc:min-w-0">
                        <p class="cnc:font-medium cnc:text-sm cnc:text-gray-800 cnc:truncate">{{ $service->name }}</p>
                        <span class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                            {!! $service->description !!}
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                <div class="cnc:text-wrap">
                                    {!! $service->description !!}
                                </div>
                            </div>
                        </span>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="cnc:flex-1 cnc:grid cnc:md:grid-cols-3 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
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
                        <p class="cnc:text-gray-400">Status</p>
                        <span
                            class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full
                            {{ $service->active ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                            {{ $service->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach

            @if($store->services->count() > 3)
            <div class="cnc:pt-4 cnc:text-right">
                <button class="btn-primary btn" data-toggle="modal" data-target="#allServicesModal">
                    See all services
                </button>
            </div>
            @endif
        </div>
    </div>



    {{-- renters card --}}
    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-8">
        <div class="cnc:bg-indigo-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-indigo-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Users cnc:text-2xl"></i> Renters ({{ $store->renters->count() }})
            </h2>
        </div>

        <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6">
            @foreach($store->renters->take(3) as $renter)
            <div
                class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                <div class="cnc:flex cnc:items-start cnc:gap-3 cnc:flex-1 cnc:md:max-w-[250px]">
                    @if($renter->user && $renter->user->profile_image_link)
                    <img src="{{ $renter->user->profile_image_link }}"
                        class="cnc:w-16 cnc:h-16 cnc:rounded cnc:object-cover cnc:border" alt="Renter Image">
                    @else
                    <div class="cnc:w-16 cnc:h-16 cnc:bg-gray-200 cnc:rounded"></div>
                    @endif

                    <div class="cnc:min-w-0">
                        <p class="cnc:font-medium cnc:text-sm cnc:text-gray-800 cnc:truncate">{{ $renter->user->name ??
                            'N/A' }}</p>
                        <span class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                            {{-- Add a tooltip if needed, similar to service description --}}
                            @if($renter->userStoreServiceType && $renter->userStoreServiceType->serviceType)
                            {{ $renter->userStoreServiceType->serviceType->name }}
                            @else
                            N/A
                            @endif
                        </span>
                    </div>
                </div>

                <div class="cnc:flex-1 cnc:grid cnc:md:grid-cols-3 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Account Type</p>
                        <p class="cnc:text-gray-700">{{ $renter->user->account_type ?? 'N/A' }}</p>
                    </div>

                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Payment Status</p>
                        <p class="cnc:text-green-600 cnc:font-semibold">
                            @if($renter->boothRentPayment)
                            {{ $renter->boothRentPayment->payment_status ?? 'N/A' }}
                            @else
                            N/A
                            @endif
                        </p>
                    </div>

                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Availability</p>
                        <span
                            class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full
                            {{ $renter->available && $renter->available->status ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                            {{ $renter->available && $renter->available->status ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach

            @if($store->renters->count() > 3)
            <div class="cnc:pt-4 cnc:text-right">
                <button class="btn-primary btn" data-toggle="modal" data-target="#allRentersModal">
                    See all renters
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Booth Rent and Bookings --}}
    <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-8 cnc:mt-6 cnc:pb-6">

        <div
            class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6 cnc:flex cnc:flex-col cnc:h-full">
            <div class="cnc:bg-purple-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-purple-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Home cnc:text-2xl"></i> Booth Rents ({{ $store->boothRent->count() }})
                </h2>
            </div>

            <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6 cnc:flex-1 cnc:flex cnc:flex-col">
                @foreach($store->boothRent->take(4) as $rent)
                <div
                    class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                    <div class="cnc:flex-1 cnc:grid cnc:truncate cnc:grid-cols-5 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                        <div class="cnc:min-w-0 cnc:truncate">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $rent->serviceType->name ?? 'N/A' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $rent->serviceType->name ?? 'N/A' }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $rent->user->name ?? 'N/A' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $rent->user->name ?? 'N/A' }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                ₦{{ number_format($rent->amount, 2) }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>₦{{ number_format($rent->amount, 2) }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span
                                class="cnc:text-sm {{ $rent->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }} cnc:truncate group">
                                {{ $rent->payment_status ? 'Paid' : 'Not Paid' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $rent->payment_status ? 'Paid' : 'Not Paid' }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($rent->created_at)->format('M d, Y') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ \Carbon\Carbon::parse($rent->created_at)->format('M d, Y') }}</div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="cnc:pt-4 cnc:mt-auto cnc:text-right">
                    <button class="btn-primary btn" data-toggle="modal" data-target="#allBoothRentsModal">
                        See all booth rents
                    </button>
                </div>
            </div>
        </div>

        <div
            class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6 cnc:flex cnc:flex-col cnc:h-full">
            <div class="cnc:bg-yellow-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Calendar cnc:text-2xl"></i> Store Bookings ({{ $store->bookings->count() }})
                </h2>
            </div>

            <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6 cnc:flex-1 cnc:flex cnc:flex-col">
                @foreach($store->bookings->take(4) as $booking)
                <div
                    class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                    <div class="cnc:flex-1 cnc:grid cnc:truncate cnc:grid-cols-6 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">

                        <div class="cnc:min-w-0 cnc:truncate">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $booking->status }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $booking->status }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ $booking->customer->name ?? 'N/A' }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ $booking->customer->name ?? 'N/A' }}</div>
                                </div>
                            </span>
                        </div>

                        <div class="cnc:text-sm cnc:truncate cnc:hover:bg-white cnc:hover:z-10">
                            <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                                {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}
                                <div
                                    class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                    <div>{{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}</div>
                                </div>
                            </span>
                        </div>

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
                    <button class="btn-primary btn" data-toggle="modal" data-target="#allStoreBookingsModal">
                        See all store bookings
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-8">
        <div class="cnc:bg-teal-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-teal-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Layers cnc:text-2xl"></i> Service Types ({{ $store->serviceTypes->count() }})
            </h2>
        </div>

        <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6">
            @foreach($store->serviceTypes as $serviceType)
            <div
                class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                <div class="cnc:flex-1 cnc:grid cnc:truncate cnc:grid-cols-1 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                    <div class="cnc:min-w-0 cnc:truncate">
                        <span class="cnc:text-sm cnc:text-gray-800 cnc:truncate group">
                            {{ $serviceType->serviceType->name ?? 'N/A' }}
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                <div>{{ $serviceType->serviceType->name ?? 'N/A' }}</div>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
            @endforeach

            @if($store->serviceTypes->count() > 4)
            <div class="cnc:pt-4 cnc:text-right">
                <button class="btn-primary btn" data-toggle="modal" data-target="#allServiceTypesModal">
                    See all service types
                </button>
            </div>
            @endif
        </div>
    </div>


    <!-- Work Samples Card -->

    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-pink-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-pink-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Image"></i> Work Samples ({{ $store->workdoneImages->count() }})
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6">
            <div class="cnc:grid cnc:grid-cols-2 cnc:md:grid-cols-4 cnc:gap-4">
                @foreach($store->workdoneImages as $image)
                <div class="cnc:h-40 cnc:rounded cnc:overflow-hidden cnc:shadow-sm cnc:border cnc:border-gray-200">
                    <img src="{{ asset('storage/workdone/'.$image->image_path) }}" alt="Work Sample"
                        class="cnc:w-full cnc:h-full cnc:object-cover">
                </div>
                @endforeach
            </div>
        </div>
    </div>


    <!-- Additional Details Card -->
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-orange-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-orange-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-File-Settings"></i> Additional Details
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6">
            @if($store->rewards)
            <div class="cnc:mb-6">
                <h3 class="cnc:text-lg cnc:font-medium cnc:mb-2 cnc:text-gray-800">Rewards</h3>
                <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 lg:grid-cols-3 cnc:gap-4">
                    @if(is_array($store->rewards))
                    @foreach($store->rewards as $key => $reward)
                    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
                        <div class="cnc:bg-green-100 cnc:px-6 cnc:py-4">
                            <h4
                                class="cnc:text-lg cnc:font-semibold cnc:text-green-700 cnc:flex cnc:items-center cnc:gap-2">
                                <i class="i-Gift"></i> {{ ucwords(str_replace('_', ' ', $key)) }}
                            </h4>
                        </div>
                        <div class="cnc:bg-white cnc:p-4">
                            @if(is_array($reward))
                            @foreach($reward as $rewardKey => $rewardValue)
                            <p class="cnc:text-sm cnc:text-gray-600">
                                {{ ucwords(str_replace('_', ' ', $rewardKey)) }}: {{ $rewardValue }}
                            </p>
                            @endforeach
                            @else
                            <p class="cnc:text-sm cnc:text-gray-600">{{ $reward }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="cnc:text-gray-600">Rewards data is not in the expected format.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($store->payment_preferences)
            <div class="cnc:mb-6">
                <h3 class="cnc:text-lg cnc:font-medium cnc:mb-2 cnc:text-gray-800">Payment Preferences</h3>
                <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 lg:grid-cols-3 cnc:gap-4">
                    @if(is_array($store->payment_preferences))
                    @foreach($store->payment_preferences as $key => $preference)
                    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
                        <div class="cnc:bg-blue-100 cnc:px-6 cnc:py-4">
                            <h4
                                class="cnc:text-lg cnc:font-semibold cnc:text-blue-700 cnc:flex cnc:items-center cnc:gap-2">
                                <i class="i-Credit-Card"></i> {{ ucwords(str_replace('_', ' ', $key)) }}
                            </h4>
                        </div>
                        <div class="cnc:bg-white cnc:p-4">
                            @if(is_array($preference))
                            @foreach($preference as $preferenceKey => $preferenceValue)
                            <p class="cnc:text-sm cnc:text-gray-600">
                                {{ ucwords(str_replace('_', ' ', $preferenceKey)) }}: {{ $preferenceValue }}
                            </p>
                            @endforeach
                            @else
                            <p class="cnc:text-sm cnc:text-gray-600">{{ $preference }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="cnc:text-gray-600">Payment preferences data is not in the expected format.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($store->booking_preferences)
            <div class="cnc:mb-6">
                <h3 class="cnc:text-lg cnc:font-medium cnc:mb-2 cnc:text-gray-800">Booking Preferences</h3>
                <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 lg:grid-cols-3 cnc:gap-4">
                    @if(is_array($store->booking_preferences))
                    @foreach($store->booking_preferences as $key => $preference)
                    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
                        <div class="cnc:bg-purple-100 cnc:px-6 cnc:py-4">
                            <h4
                                class="cnc:text-lg cnc:font-semibold cnc:text-purple-700 cnc:flex cnc:items-center cnc:gap-2">
                                <i class="i-Calendar"></i> {{ ucwords(str_replace('_', ' ', $key)) }}
                            </h4>
                        </div>
                        <div class="cnc:bg-white cnc:p-4">
                            @if(is_array($preference))
                            @foreach($preference as $preferenceKey => $preferenceValue)
                            <p class="cnc:text-sm cnc:text-gray-600">
                                {{ ucwords(str_replace('_', ' ', $preferenceKey)) }}: {{ $preferenceValue }}
                            </p>
                            @endforeach
                            @else
                            <p class="cnc:text-sm cnc:text-gray-600">{{ $preference }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="cnc:text-gray-600">Booking preferences data is not in the expected format.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($store->availability)
            <div class="cnc:mb-6">
                <h3 class="cnc:text-lg cnc:font-medium cnc:mb-2 cnc:text-gray-800">Availability</h3>
                <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 lg:grid-cols-3 cnc:gap-4">
                    @if(is_array($store->availability))
                    @foreach($store->availability as $key => $value)
                    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
                        <div class="cnc:bg-yellow-100 cnc:px-6 cnc:py-4">
                            <h4
                                class="cnc:text-lg cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                                <i class="i-Clock"></i> {{ ucwords(str_replace('_', ' ', $key)) }}
                            </h4>
                        </div>
                        <div class="cnc:bg-white cnc:p-4">
                            @if(is_array($value))
                            @foreach($value as $valueKey => $valueValue)
                            <p class="cnc:text-sm cnc:text-gray-600">
                                {{ ucwords(str_replace('_', ' ', $valueKey)) }}: {{ $valueValue }}
                            </p>
                            @endforeach
                            @else
                            <p class="cnc:text-sm cnc:text-gray-600">{{ $value }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="cnc:text-gray-600">Availability data is not in the expected format.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($store->booking_limits)
            <div class="cnc:mb-6">
                <h3 class="cnc:text-lg cnc:font-medium cnc:mb-2 cnc:text-gray-800">Booking Limits</h3>
                <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 lg:grid-cols-3 cnc:gap-4">
                    @if(is_array($store->booking_limits))
                    @foreach($store->booking_limits as $key => $value)
                    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
                        <div class="cnc:bg-red-100 cnc:px-6 cnc:py-4">
                            <h4
                                class="cnc:text-lg cnc:font-semibold cnc:text-red-700 cnc:flex cnc:items-center cnc:gap-2">
                                <i class="i-Ban"></i> {{ ucwords(str_replace('_', ' ', $key)) }}
                            </h4>
                        </div>
                        <div class="cnc:bg-white cnc:p-4">
                            @if(is_array($value))
                            @foreach($value as $valueKey => $valueValue)
                            <p class="cnc:text-sm cnc:text-gray-600">
                                {{ ucwords(str_replace('_', ' ', $valueKey)) }}: {{ $valueValue }}
                            </p>
                            @endforeach
                            @else
                            <p class="cnc:text-sm cnc:text-gray-600">{{ $value }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="cnc:text-gray-600">Booking limits data is not in the expected format.</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>


    <!-- Action Buttons -->
    <div class="cnc:mt-12 cnc:flex cnc:gap-4 cnc:rounded-2xl">

        <form id="delete-form" action="{{ route('admin.stores.destroy', $store->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" id="alert-confirm" class="btn btn-danger">
                <i class="i-Folder-Trash"></i> Delete store
            </button>
        </form>
    </div>
    {{-- modals --}}
    {{-- service modal --}}
    <div class="modal fade" id="allServicesModal" tabindex="-1" role="dialog" aria-labelledby="allServicesModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allServicesModalTitle">
                        <i class="i-Toolbox cnc:text-blue-500 mr-2"></i> All Services ({{ $store->services->count() }})
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
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
                                @foreach($store->services as $service)
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
                                                {{ $service->promotions->count() }} promo{{
                                                $service->promotions->count() >
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
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- renter modal --}}
    <div class="modal fade" id="allRentersModal" tabindex="-1" role="dialog" aria-labelledby="allRentersModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
            <div class="modal-content">
                <div class="modal-header">6t
                    <h5 class="modal-title" id="allRentersModalTitle">
                        <i class="i-Users cnc:text-blue-500 mr-2"></i> All Renters ({{ $store->renters->count() }})
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="cnc:overflow-auto">
                        <table
                            class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                            <thead class="cnc:bg-purple-100 cnc:text-black-700">
                                <tr>
                                    <th class="cnc:p-4 cnc:font-semibold">Name</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Account Type</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Service Type</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Payment Status</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Availability</th>
                                </tr>
                            </thead>
                            <tbody class="cnc:divide-y cnc:divide-gray-100 cnc:bg-white">
                                @foreach($store->renters as $renter)
                                <tr class="cnc:hover:bg-gray-50 transition">
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $renter->user->name ??
                                        'N/A' }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-700">{{ $renter->user->account_type ?? 'N/A' }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-600">
                                        @if($renter->userStoreServiceType &&
                                        $renter->userStoreServiceType->serviceType)
                                        {{ $renter->userStoreServiceType->serviceType->name }}
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td class="cnc:p-4 cnc:text-green-600 cnc:font-semibold">
                                        @if($renter->boothRentPayment)
                                        {{ $renter->boothRentPayment->payment_status ?? 'N/A' }}
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td class="cnc:p-4">
                                        <span
                                            class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full
                                        {{ $renter->available && $renter->available->status ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                                            {{ $renter->available && $renter->available->status ? 'Available' :
                                            'Unavailable' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Booth Rents Modals --}}
    <div class="modal fade" id="allBoothRentsModal" tabindex="-1" role="dialog"
        aria-labelledby="allBoothRentsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allBoothRentsModalTitle">
                        <i class="i-Home cnc:text-blue-500 mr-2"></i> All Booth Rents ({{ $store->boothRent->count() }})
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="cnc:overflow-auto">
                        <table
                            class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                            <thead class="cnc:bg-purple-100 cnc:text-black-700">
                                <tr>
                                    <th class="cnc:p-4 cnc:font-semibold">Service Type</th>
                                    <th class="cnc:p-4 cnc:font-semibold">User</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Amount</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Payment Status</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Created At</th>
                                </tr>
                            </thead>
                            <tbody class="cnc:divide-y cnc:divide-gray-100 cnc:bg-white">
                                @foreach($store->boothRent as $rent)
                                <tr class="cnc:hover:bg-gray-50 transition">
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $rent->serviceType->name ??
                                        'N/A' }}</td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $rent->user->name ?? 'N/A'
                                        }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">₦{{
                                        number_format($rent->amount,
                                        2) }}</td>
                                    <td
                                        class="cnc:p-4 {{ $rent->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }}">
                                        {{ $rent->payment_status ? 'Paid' : 'Not Paid' }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">
                                        {{ \Carbon\Carbon::parse($rent->created_at)->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bookings Modals --}}
    <div class="modal fade" id="allStoreBookingsModal" tabindex="-1" role="dialog"
        aria-labelledby="allStoreBookingsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allStoreBookingsModalTitle">
                        <i class="i-Calendar cnc:text-blue-500 mr-2"></i> All Store Bookings ({{
                        $store->bookings->count()
                        }})
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="cnc:overflow-auto">
                        <table
                            class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                            <thead class="cnc:bg-yellow-100 cnc:text-black-700">
                                <tr>
                                    <th class="cnc:p-4 cnc:font-semibold">Date</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Status</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Customer</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Time</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Services</th>
                                    <th class="cnc:p-4 cnc:font-semibold">Payment Status</th>
                                </tr>
                            </thead>
                            <tbody class="cnc:divide-y cnc:divide-gray-100 cnc:bg-white">
                                @foreach($store->bookings as $booking)
                                <tr class="cnc:hover:bg-gray-50 transition">
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">
                                        {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $booking->status }}</td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">{{ $booking->customer->name ??
                                        'N/A' }}</td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">
                                        {{ \Carbon\Carbon::parse($booking->time)->format('g:i A') }}
                                    </td>
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">
                                        @foreach ($booking->appointmentService as $service)
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
                                        @endforeach
                                    </td>
                                    <td
                                        class="cnc:p-4 {{ $booking->payment_status ? 'cnc:text-green-600' : 'cnc:text-red-500' }}">
                                        {{ $booking->payment_status ? 'Paid' : 'Not Paid' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Service Types Modal --}}
    <div class="modal fade" id="allServiceTypesModal" tabindex="-1" role="dialog"
        aria-labelledby="allServiceTypesModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered cnc:max-w-2xl! cnc:w-full!" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="allServiceTypesModalTitle">
                        <i class="i-Layers cnc:text-blue-500 mr-2"></i> All Service Types ({{
                        $store->serviceTypes->count()
                        }})
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="cnc:overflow-auto">
                        <table
                            class="cnc:min-w-full cnc:text-sm cnc:text-left cnc:rounded-2xl cnc:overflow-hidden cnc:border cnc:border-gray-100 cnc:shadow-sm">
                            <thead class="cnc:bg-teal-100 cnc:text-black-700">
                                <tr>
                                    <th class="cnc:p-4 cnc:font-semibold">Service Type Name</th>
                                </tr>
                            </thead>
                            <tbody class="cnc:divide-y cnc:divide-gray-100 cnc:bg-white">
                                @foreach($store->serviceTypes as $serviceType)
                                <tr class="cnc:hover:bg-gray-50 transition">
                                    <td class="cnc:p-4 cnc:text-gray-800 cnc:font-medium">
                                        {{ $serviceType->serviceType->name ?? 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.getElementById('alert-confirm').addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default form submission
        
        Swal.fire({
        title: 'Are you sure?',
        text: "This Store will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
        }).then((result) => {
        if (result.isConfirmed) {
        // Submit the form if confirmed
        document.getElementById('delete-form').submit();
        }
        });
        });

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