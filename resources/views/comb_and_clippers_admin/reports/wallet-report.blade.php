@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Wallet Report</h4>


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

        <form method="GET" action="{{ route('admin.reports.wallets') }}"
            class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow cnc:mb-6">
            <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:lg:grid-cols-4 cnc:gap-4">
                <div>
                    <label for="from_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Start
                        Date (Wallet Creation)</label>
                    <input type="date" name="from_date" id="from_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('from_date') }}">
                </div>

                <div>
                    <label for="to_date" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">End
                        Date (Wallet Creation)</label>
                    <input type="date" name="to_date" id="to_date"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ request('to_date') }}">
                </div>

                <div>
                    <label for="user_id"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">User</label>
                    <select name="user_id" id="user_id"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id')==$user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="currency"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Currency</label>
                    <select name="currency" id="currency"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Currencies</option>
                        <option value="NGN" {{ request('currency')=='NGN' ? 'selected' : '' }}>NGN</option>
                        <option value="USD" {{ request('currency')=='USD' ? 'selected' : '' }}>USD</option>
                        </ul>
                    </select>
                </div>

                <div>
                    <label for="wallet_number"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Wallet
                        Number</label>
                    <input type="text" name="wallet_number" id="wallet_number"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Filter by Wallet Number" value="{{ request('wallet_number') }}">
                </div>

                <div>
                    <label for="bank_code" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Bank
                        Code</label>
                    <select name="bank_code" id="bank_code"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->bankcode }}" {{ request('bank_code')==$bank->bankcode ? 'selected' : ''
                            }}>
                            {{ $bank->bank }} ({{ $bank->bankcode }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="cnc:mt-4">
                <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                <a href="{{ route('admin.reports.wallets') }}" class="btn btn-secondary">Reset Filters</a>
            </div>
        </form>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.wallets.download.excel', request()->all()) }}"
                class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.wallets.download.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="walletsReportTable" style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Wallet Number</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Unclaimed Amount</th>
                            <th>Account Number</th>
                            <th>Bank Name</th>
                            <th>Bank Code</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $index => $wallet)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $wallet->user->name ?? 'N/A' }}</td>
                            <td>{{ $wallet->wallet_number ?? 'N/A' }}</td>
                            <td>{{ $wallet->currency ?? 'N/A' }}</td>
                            <td>{{ number_format($wallet->amount, 2) }}</td>
                            <td>{{ number_format($wallet->unclaimed_amount, 2) }}</td>
                            <td>{{ $wallet->account_number ?? 'N/A' }}</td>
                            <td>{{ $wallet->bank->bank ?? 'N/A' }}</td>
                            <td>{{ $wallet->bank_code ?? 'N/A' }}</td>
                            <td>{{ $wallet->created_at ? $wallet->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No wallets found based on the applied filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#walletsReportTable').DataTable({
            "order": [[9, "desc"]], // Sort by Creation Date
            "pageLength": 15
        });
    });
</script>
@endsection