@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">General Report</h4>


    <div class="separator-breadcrumb border-top"></div>
    @php
    $reportList = [
    'admin.reports.general',
    'admin.reports.appointments',
    'admin.reports.boothRentals',
    'admin.reports.boothRentalPayments',
    'admin.reports.internalTransactions',
    'admin.reports.bankDetails',
    'admin.reports.walletTransactions',
    'admin.reports.wallets',
    'admin.reports.users',
    'admin.reports.stores',
    'admin.reports.withdrawals',
    ];

    $currentIndex = array_search(Route::currentRouteName(), $reportList);
    $previousIndex = $currentIndex > 0 ? $currentIndex - 1 : false;
    $nextIndex = $currentIndex !== false && $currentIndex < count($reportList) - 1 ? $currentIndex + 1 : false;
        $previousReportRoute=$previousIndex !==false ? route($reportList[$previousIndex], request()->query()) : null;
        $nextReportRoute = $nextIndex !== false ? route($reportList[$nextIndex], request()->query()) : null;
        @endphp

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="i-Arrow-Left"></i> Back
                </a>
            </div>
            <div>
                <a href="{{ $previousReportRoute }}" class="btn btn-outline-primary mr-2" @if (!$previousReportRoute)
                    disabled @endif>
                    <i class="i-Arrow-Left-in-Circle"></i> Previous Report
                </a>
                <select onchange="window.location.href = this.value;"
                    class="form-control form-control-sm d-inline-block w-auto mr-2">
                    <option value="">Select Report</option>
                    <option value="{{ route('admin.reports.general') }}" @if(request()->
                        routeIs('admin.reports.general'))
                        selected @endif>General Report</option>
                    <option value="{{ route('admin.reports.appointments') }}" @if(request()->
                        routeIs('admin.reports.appointments')) selected @endif>Appointment Report</option>
                    <option value="{{ route('admin.reports.boothRentals') }}" @if(request()->
                        routeIs('admin.reports.boothRentals')) selected @endif>BoothRental Report</option>
                    <option value="{{ route('admin.reports.boothRentalPayments') }}" @if(request()->
                        routeIs('admin.reports.boothRentalPayments')) selected @endif>BoothRental Payment Report
                    </option>
                    <option value="{{ route('admin.reports.internalTransactions') }}" @if(request()->
                        routeIs('admin.reports.internalTransactions')) selected @endif>Internal Transaction Report
                    </option>
                    <option value="{{ route('admin.reports.bankDetails') }}" @if(request()->
                        routeIs('admin.reports.bankDetails')) selected @endif>Bank Details Report</option>
                    <option value="{{ route('admin.reports.walletTransactions') }}" @if(request()->
                        routeIs('admin.reports.walletTransactions')) selected @endif>Wallet Transaction Report</option>
                    <option value="{{ route('admin.reports.wallets') }}" @if(request()->
                        routeIs('admin.reports.wallets'))
                        selected @endif>Wallet Report</option>
                    <option value="{{ route('admin.reports.users') }}" @if(request()->routeIs('admin.reports.users'))
                        selected
                        @endif>User Report</option>
                    <option value="{{ route('admin.reports.stores') }}" @if(request()->routeIs('admin.reports.stores'))
                        selected
                        @endif>Store Report</option>
                    <option value="{{ route('admin.reports.withdrawals') }}" @if(request()->
                        routeIs('admin.reports.withdrawals')) selected @endif>Withdrawal Report</option>
                </select>
                <a href="{{ $nextReportRoute }}" class="btn btn-outline-primary" @if (!$nextReportRoute) disabled
                    @endif>
                    Next Report <i class="i-Arrow-Right-in-Circle"></i>
                </a>
            </div>
        </div>

        <div
            class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:sm:grid-cols-2 cnc:grid-cols-1 cnc:gap-4 cnc:mb-6">
            <div class="card">
                <div class="cnc:card-header cnc:bg-indigo-100 cnc:text-indigo-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-User cnc:text-3xl"></i> Total Users
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalUsers }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newUsersInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newUsersInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Clients cnc:text-3xl"></i> Total Customers
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalCustomers }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newCustomersInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newCustomersInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Shop cnc:text-3xl"></i> Total Merchants
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalMerchants }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newMerchantsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newMerchantsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-yellow-100 cnc:text-yellow-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Home1 cnc:text-3xl"></i> Total Stores
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalStores }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newStoresInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newStoresInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-pink-100 cnc:text-pink-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Star cnc:text-3xl"></i> Featured Stores
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $featuredStores }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newFeaturedStoresInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newFeaturedStoresInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-teal-100 cnc:text-teal-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Scissors cnc:text-3xl"></i> Total Services
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalServices }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($newServicesInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $newServicesInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-purple-100 cnc:text-purple-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Calendar-4 cnc:text-3xl"></i> Total Appointments
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalAppointments }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($totalAppointmentsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $totalAppointmentsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-lime-100 cnc:text-lime-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Check cnc:text-3xl"></i> Completed Appointments
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalCompletedAppointments }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($completedAppointmentsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $completedAppointmentsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Bag cnc:text-3xl"></i> Total Revenue
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalRevenue, 2) }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($totalRevenueInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( ₦{{ number_format($totalRevenueInRange, 2) }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-indigo-100 cnc:text-indigo-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Room cnc:text-3xl"></i> Total Booth Rentals
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalBoothRentals }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($boothRentalsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $boothRentalsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Key cnc:text-3xl"></i> Active Booth Rentals
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $activeBoothRentals }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($activeBoothRentalsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $activeBoothRentalsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-2 cnc:text-3xl"></i> Booth Rental Revenue
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($boothRentalRevenue, 2) }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($boothRentalRevenueInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( ₦{{ number_format($boothRentalRevenueInRange, 2) }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-yellow-100 cnc:text-yellow-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Wallet cnc:text-3xl"></i> Total Wallet Balance
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalWalletBalance, 2) }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($walletBalanceInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( ₦{{ number_format($walletBalanceInRange, 2) }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-pink-100 cnc:text-pink-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Withdraw cnc:text-3xl"></i> Total Withdrawals
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalWithdrawals }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($withdrawalsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $withdrawalsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-teal-100 cnc:text-teal-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Loading-3 cnc:text-3xl"></i> Pending Withdrawals
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $pendingWithdrawals }}</h3>
                    @if(isset($startDate) && isset($endDate) && isset($pendingWithdrawalsInRange))
                    <p class="cnc:text-sm cnc:text-gray-600">
                        ( {{ $pendingWithdrawalsInRange }} in selected date range )
                    </p>
                    @endif
                </div>
            </div>

            @if(isset($startDate) && isset($endDate) && isset($newUsersInRange))
            <div class="card cnc:lg:col-span-2 cnc:md:col-span-2 cnc:sm:col-span-1 cnc:col-span-1">
                <div class="cnc:card-header cnc:bg-indigo-100 cnc:text-indigo-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Add-User cnc:text-3xl"></i> New Users
                        @if(isset($startDate) && isset($endDate))
                        <span class="cnc:text-sm cnc:text-indigo-900 font-semibold">
                            ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{
                            \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
                        </span>
                        @endif
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $newUsersInRange }}</h3>
                </div>
            </div>
            @endif

            @if(isset($startDate) && isset($endDate) && isset($newMerchantsInRange))
            <div class="card cnc:lg:col-span-2 cnc:md:col-span-2 cnc:sm:col-span-1 cnc:col-span-1">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Add-UserStar cnc:text-3xl"></i> New Merchants
                        @if(isset($startDate) && isset($endDate))
                        <span class="cnc:text-sm cnc:text-green-900 font-semibold">
                            ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{
                            \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
                        </span>
                        @endif
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $newMerchantsInRange }}</h3>
                </div>
            </div>
            @endif

            @if(isset($startDate) && isset($endDate) && isset($totalAppointmentsInRange))
            <div class="card cnc:lg:col-span-2 cnc:md:col-span-2 cnc:sm:col-span-1 cnc:col-span-1">
                <div class="cnc:card-header cnc:bg-purple-100 cnc:text-purple-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Calendar-4 cnc:text-3xl"></i> Appointments
                        @if(isset($startDate) && isset($endDate))
                        <span class="cnc:text-sm cnc:text-purple-900 font-semibold">
                            ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{
                            \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
                        </span>
                        @endif
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ $totalAppointmentsInRange }}</h3>
                </div>
            </div>
            @endif

            @if(isset($startDate) && isset($endDate) && isset($totalRevenueInRange))
            <div class="card cnc:lg:col-span-2 cnc:md:col-span-2 cnc:sm:col-span-1 cnc:col-span-1">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Bag cnc:text-3xl"></i> Total Revenue
                        @if(isset($startDate) && isset($endDate))
                        <span class="cnc:text-sm cnc:text-green-900 font-semibold">
                            ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{
                            \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
                        </span>
                        @endif
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalRevenueInRange, 2) }}</h3>
                </div>
            </div>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.reports.general') }}"
            class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow cnc:mb-6">
            <div class="cnc:grid cnc:grid-cols-2 cnc:gap-4">
                <div>
                    <label for="start_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Start
                        Date</label>
                    <input type="date" name="start_date" id="start_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('start_date') }}">
                </div>
                <div>
                    <label for="end_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">End
                        Date</label>
                    <input type="date" name="end_date" id="end_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('end_date') }}">
                </div>
            </div>
            <div class="cnc:mt-4">
                <button type="submit" class="btn btn-primary mr-3">Apply Date Filter</button>
                <a href="{{ route('admin.reports.general') }}" class="btn btn-secondary">Reset Date Filter</a>
            </div>
        </form>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.general.download.excel', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.general.download.pdf', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <h5 class="cnc:text-md cnc:font-semibold cnc:mb-3">Key Metrics</h5>
            <div class="cnc:overflow-x-auto">
                <table class="table table-striped table-bordered" style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            @if(isset($startDate) && isset($endDate))
                            <th>Value ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{
                                \Carbon\Carbon::parse($endDate)->format('M d, Y') }})</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Appointments</td>
                            <td>{{ $totalAppointments }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($totalAppointmentsInRange))
                            <td>{{ $totalAppointmentsInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Completed Appointments</td>
                            <td>{{ $totalCompletedAppointments }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($completedAppointmentsInRange))
                            <td>{{ $completedAppointmentsInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Total Revenue</td>
                            <td>₦{{ number_format($totalRevenue, 2) }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($totalRevenueInRange))
                            <td>₦{{ number_format($totalRevenueInRange, 2) }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Booth Rental Revenue</td>
                            <td>₦{{ number_format($boothRentalRevenue, 2) }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($boothRentalRevenueInRange))
                            <td>₦{{ number_format($boothRentalRevenueInRange, 2) }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>New Users</td>
                            <td>{{ $totalUsers }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($newUsersInRange))
                            <td>{{ $newUsersInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>New Customers</td>
                            <td>{{ $totalCustomers }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($newCustomersInRange))
                            <td>{{ $newCustomersInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>New Merchants</td>
                            <td>{{ $totalMerchants }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($newMerchantsInRange))
                            <td>{{ $newMerchantsInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>New Stores</td>
                            <td>{{ $totalStores }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($newStoresInRange))
                            <td>{{ $newStoresInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Featured Stores</td>
                            <td>{{ $featuredStores }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($newFeaturedStoresInRange))
                            <td>{{ $newFeaturedStoresInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Withdrawals</td>
                            <td>{{ $totalWithdrawals }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($withdrawalsInRange))
                            <td>{{ $withdrawalsInRange }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Pending Withdrawals</td>
                            <td>{{ $pendingWithdrawals }}</td>
                            @if(isset($startDate) && isset($endDate) && isset($pendingWithdrawalsInRange))
                            <td>{{ $pendingWithdrawalsInRange }}</td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</div>
@endsection