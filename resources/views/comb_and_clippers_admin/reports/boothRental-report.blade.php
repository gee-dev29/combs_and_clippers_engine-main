@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Booth Rental Report</h4>

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

        <form method="GET" action="{{ route('admin.reports.boothRentals') }}"
            class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow cnc:mb-6">
            <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:lg:grid-cols-4 cnc:gap-4">
                <div>
                    <label for="from_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Start
                        Date</label>
                    <input type="date" name="from_date" id="from_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('from_date') }}">
                </div>

                <div>
                    <label for="to_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">End
                        Date</label>
                    <input type="date" name="to_date" id="to_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('to_date') }}">
                </div>

                <div>
                    <label for="store_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Store</label>
                    <select name="store_id" id="store_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Stores</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id')==$store->id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="user_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Renter</label>
                    <select name="user_id" id="user_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Renters</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id')==$user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment_timeline"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Payment Timeline</label>
                    <select name="payment_timeline" id="payment_timeline"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Timelines</option>
                        @foreach($paymentTimelines as $timeline)
                        <option value="{{ $timeline }}" {{ request('payment_timeline')==$timeline ? 'selected' : '' }}>
                            {{ ucfirst($timeline) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="service_type_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Service Type</label>
                    <select name="service_type_id" id="service_type_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Service Types</option>
                        @foreach($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}" {{ request('service_type_id')==$serviceType->id ?
                            'selected' : '' }}>
                            {{ $serviceType->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="cnc:col-span-full cnc:md:col-span-2 cnc:lg:col-span-4 cnc:mt-6">
                    <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                    <a href="{{ route('admin.reports.boothRentals') }}" class="btn btn-secondary">Reset Filters</a>
                </div>
            </div>
        </form>

        <div class="cnc:grid cnc:lg:grid-cols-2 cnc:gap-4 cnc:mb-6">
            <div class="card">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Home-2 cnc:text-3xl"></i> Total Booth Rentals
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalRentals) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Bag cnc:text-3xl"></i> Total Rental Amount
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.boothRentals.download.excel', request()->all()) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.boothRentals.download.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="zero_configuration_table"
                    style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>ID</th>
                            <th>Store</th>
                            <th>Renter</th>
                            <th>Payment Timeline</th>
                            <th>Amount</th>
                            <th>Service Type</th>
                            <th>Payment Days</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boothRentals as $rental)
                        <tr>
                            <td>{{ $rental->id }}</td>
                            <td>{{ $rental->store->store_name ?? 'N/A' }}</td>
                            <td>{{ $rental->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($rental->payment_timeline) }}</td>
                            <td>₦{{ number_format($rental->amount, 2) }}</td>
                            <td>{{ $rental->serviceType->name ?? 'N/A' }}</td>
                            <td>{{ $rental->payment_days }}</td>
                            <td>{{ $rental->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No booth rentals found based on the applied filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


        </div>
</div>
@endsection