@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Booth Rental Payment Report</h4>

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

        <form method="GET" action="{{ route('admin.reports.boothRentalPayments') }}"
            class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow cnc:mb-6">
            <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:lg:grid-cols-5 cnc:gap-4">
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
                    <label for="tenant"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Tenant</label>
                    <input type="text" name="tenant" id="tenant"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Search by Tenant" value="{{ request('tenant') }}">
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
                    <label for="payment_status"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Payment Status</label>
                    <select name="payment_status" id="payment_status"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ request('payment_status')=='paid' ? 'selected' : '' }}>Paid</option>
                        <option value="upcoming" {{ request('payment_status')=='upcoming' ? 'selected' : '' }}>Upcoming
                        </option>
                        <option value="due" {{ request('payment_status')=='due' ? 'selected' : '' }}>Due</option>
                        <option value="overdue" {{ request('payment_status')=='overdue' ? 'selected' : '' }}>Overdue
                        </option>
                    </select>
                </div>

                <div class="cnc:col-span-full cnc:md:col-span-2 cnc:lg:col-span-5 cnc:mt-4">
                    <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                    <a href="{{ route('admin.reports.boothRentalPayments') }}" class="btn btn-secondary">Clear
                        Filters</a>
                </div>
            </div>
        </form>

        <div class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Bag cnc:text-3xl"></i> Total Revenue
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-yellow-100 cnc:text-yellow-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Clock cnc:text-3xl"></i> Pending Payments
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($pendingPayments, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-red-100 cnc:text-red-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Over-Time-2 cnc:text-3xl"></i> Overdue Payments
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($overduePayments, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Receipt-3 cnc:text-3xl"></i> Processing Fees
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($processingFees, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.boothRentalPayments.download.excel', request()->all()) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.boothRentalPayments.download.pdf', request()->all()) }}"
                class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="boothRentPaymentReportTable"
                    style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Tenant</th>
                            <th>Store</th>
                            <th>Amount</th>
                            <th>Processing Fee</th>
                            <th>Last Payment Date</th>
                            <th>Next Payment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boothPayments as $index => $rental)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rental->boothRental->user->name ?? 'N/A' }}</td>
                            <td>{{ $rental->boothRental->store->store_name ?? 'N/A' }}</td>
                            <td>₦{{ number_format($rental->amount, 2) }}</td>
                            <td>₦{{ number_format($rental->processing_fee, 2) }}</td>
                            <td>{{ optional($rental->last_payment_date)->format('M d, Y') }}</td>
                            <td>{{ optional($rental->next_payment_date)->format('M d, Y') }}</td>
                            <td>
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn
                                {{ $rental->payment_status == 'paid' ? 'btn-success' :
                                ($rental->payment_status == 'overdue' ? 'btn-danger' : 'btn-warning') }}">
                                    {{ ucfirst($rental->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready( function () {
        $('#boothRentPaymentReportTable').DataTable({
            "order": [[6, "asc"]], // Sort by Next Payment Date
            "pageLength": 10
        });
    });
</script>
@endsection