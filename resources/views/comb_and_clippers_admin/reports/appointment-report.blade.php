@extends('layouts.app')

@section('content')

<div class="main-content">
    <div class="breadcrumb">
        <h1>Appointment Report</h1>
    </div>

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

        <div class="card mb-4">
            <div class="card-header">
                <h5>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.appointments') }}" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Merchant</label>
                            <select name="merchant_id" class="form-control">
                                <option value="">All Merchants</option>
                                @foreach($merchants as $merchant)
                                <option value="{{ $merchant->id }}" {{ request('merchant_id')==$merchant->id ?
                                    'selected' :
                                    '' }}>
                                    {{ $merchant->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Store</label>
                            <select name="store_id" class="form-control">
                                <option value="">All Stores</option>
                                @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id')==$store->id ? 'selected' : ''
                                    }}>
                                    {{ $store->store_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status')==$status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control">
                                <option value="">All Payment Statuses</option>
                                @foreach($paymentStatuses as $key => $status)
                                <option value="{{ $key }}" {{ request('payment_status')==$key ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Search by Customer/Merchant</label>
                            <input type="text" class="form-control" name="search" placeholder="Search..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                        <a href="{{ route('admin.reports.appointments') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h6>Total Appointments</h6>
                        <h3>{{ number_format($totalAppointments) }}</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Total Amount</h6>
                        <h3>₦{{ number_format($totalAmount, 2) }}</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Total Tips</h6>
                        <h3>₦{{ number_format($totalTips, 2) }}</h3>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Total Processing Fees</h6>
                        <h3>₦{{ number_format($totalProcessingFees, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>


        <div class="mb-4">
            <a href="{{ route('admin.reports.appointments.download.excel', request()->all()) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.appointments.download.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Appointment List</h5>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Merchant</th>
                                <th>Store</th>
                                <th>Date & Time</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $index => $appointment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $appointment->customer->name ?? 'N/A' }}</td>
                                <td>{{ $appointment->serviceProvider->name ?? 'N/A' }}</td>
                                <td>{{ $appointment->store->store_name ?? 'N/A' }}</td>
                                <td>{{ $appointment->date }} - {{ $appointment->time }}</td>
                                <td>₦{{ number_format($appointment->total_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="badge badge-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-{{ $appointment->payment_status == 1 ? 'success' : 'danger' }}">
                                        {{ $appointment->payment_status == 1 ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No appointments found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>
@endsection