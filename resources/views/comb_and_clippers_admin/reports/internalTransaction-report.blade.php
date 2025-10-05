@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Internal Transaction Report</h4>


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

        <form method="GET" action="{{ route('admin.reports.internalTransactions') }}"
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
                    <label for="merchant_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Merchant</label>
                    <select name="merchant_id" id="merchant_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Merchants</option>
                        @foreach($merchants as $merchant)
                        <option value="{{ $merchant->id }}" {{ request('merchant_id')==$merchant->id ? 'selected' : ''
                            }}>
                            {{ $merchant->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="customer_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Customer</label>
                    <select name="customer_id" id="customer_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id')==$customer->id ? 'selected' : ''
                            }}>
                            {{ $customer->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Transaction
                        Type</label>
                    <select name="type" id="type"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Types</option>
                        <option value="deposit" {{ request('type')=='deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="withdrawal" {{ request('type')=='withdrawal' ? 'selected' : '' }}>Withdrawal
                        </option>
                        <option value="transfer" {{ request('type')=='transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <div>
                    <label for="payment_status"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Payment Status</label>
                    <select name="payment_status" id="payment_status"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Statuses</option>
                        <option value="0" {{ request('payment_status')=='0' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="1" {{ request('payment_status')=='1' ? 'selected' : '' }}>
                            Successful
                        </option>
                        {{-- <option value="failed" {{ request('payment_status')=='failed' ? 'selected' : '' }}>Failed
                        </option> --}}
                    </select>
                </div>

                <div class="cnc:col-span-full cnc:md:col-span-2 cnc:lg:col-span-4 cnc:mt-4">
                    <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                    <a href="{{ route('admin.reports.internalTransactions') }}" class="btn btn-secondary">Reset
                        Filters</a>
                </div>
            </div>
        </form>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.internalTransactions.download.excel', request()->all()) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.internalTransactions.download.pdf', request()->all()) }}"
                class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:grid cnc:lg:grid-cols-3 cnc:gap-4 cnc:mb-6">
            <div class="card">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-List-Numbered cnc:text-3xl"></i> Total Transactions
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Money-Bag cnc:text-3xl"></i> Total Amount
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="cnc:card-header cnc:bg-yellow-100 cnc:text-yellow-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                        <i class="i-Clock cnc:text-3xl"></i> Average Transaction Amount
                    </h5>
                </div>
                <div class="cnc:card-body cnc:px-6 cnc:py-5">
                    <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($averageAmount, 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="internalTransactionsReportTable"
                    style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>ID</th>
                            <th>Merchant</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Transaction Ref</th>
                            <th>Narration</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($internalTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->merchant->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td>{{ $transaction->transaction_ref }}</td>
                            <td>{{ $transaction->narration }}</td>
                            <td>{{ strtoupper($transaction->currency) }}</td>
                            <td>₦{{ number_format($transaction->amount, 2) }}</td>
                            <td>
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn 
                                @if($transaction->payment_status == 'successful') btn-success
                                @elseif($transaction->payment_status == 'failed') btn-danger
                                @else btn-warning @endif">
                                    {{ ucfirst($transaction->payment_status) }}
                                </span>
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No internal transactions found based on the applied
                                filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="cnc:mt-4">
                {{ $internalTransactions->appends(request()->query())->links() }}
            </div>
        </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#internalTransactionsReportTable').DataTable({
            "order": [[9, "desc"]], // Sort by Creation Date
            "pageLength": 15
        });
    });
</script>
@endsection