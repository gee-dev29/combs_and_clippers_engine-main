@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">User Report</h4>


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

        <form method="GET" action="{{ route('admin.reports.users') }}"
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
                    <label for="accountstatus"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Account Status</label>
                    <select name="accountstatus" id="accountstatus"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('accountstatus')=='active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ request('accountstatus')=='inactive' ? 'selected' : '' }}>Inactive
                        </option>
                        <option value="pending" {{ request('accountstatus')=='pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="suspended" {{ request('accountstatus')=='suspended' ? 'selected' : '' }}>
                            Suspended
                        </option>
                    </select>
                </div>

                <div>
                    <label for="account_type"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Account
                        Type</label>
                    <select name="account_type" id="account_type"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Types</option>
                        <option value="user" {{ request('account_type')=='user' ? 'selected' : '' }}>User</option>
                        <option value="merchant" {{ request('account_type')=='merchant' ? 'selected' : '' }}>Merchant
                        </option>
                    </select>
                </div>

                <div>
                    <label for="bank"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Bank</label>
                    <input type="text" name="bank" id="bank"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Filter by Bank Name" value="{{ request('bank') }}">
                </div>

                <div>
                    <label for="specialization"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Specialization</label>
                    <input type="text" name="specialization" id="specialization"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Filter by Specialization" value="{{ request('specialization') }}">
                </div>

                <div>
                    <label for="has_store" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Has
                        Store</label>
                    <select name="has_store" id="has_store"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All</option>
                        <option value="1" {{ request('has_store')=='1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('has_store')=='0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            <div class="cnc:mt-4">
                <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                <a href="{{ route('admin.reports.users') }}" class="btn btn-secondary">Reset Filters</a>
            </div>
        </form>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.users.download.excel', request()->all()) }}" class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.users.download.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="usersReportTable" style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Status</th>
                            <th>Account Type</th>
                            <th>Phone</th>
                            <th>Bank</th>
                            <th>Account Number</th>
                            <th>Account Name</th>
                            <th>Specialization</th>
                            <th>Has Store</th>
                            <th>Email Verified At</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->accountstatus == 'active')
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-success">Active</span>
                                @elseif ($user->accountstatus == 'inactive')
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-secondary">Inactive</span>
                                @elseif ($user->accountstatus == 'pending')
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-warning">Pending</span>
                                @elseif ($user->accountstatus == 'suspended')
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-danger">Suspended</span>
                                @else
                                {{ ucfirst($user->accountstatus) }}
                                @endif
                            </td>
                            <td>{{ ucfirst($user->account_type) }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>{{ $user->bank_details->bank_name ?? $user->bank ?? 'N/A' }}</td>
                            <td>{{ $user->bank_details->account_number ?? $user->accountno ?? 'N/A' }}</td>
                            <td>{{ $user->accountname ?? 'N/A' }}</td>
                            <td>{{ $user->specialization ?? 'N/A' }}</td>
                            <td>{{ $user->store ? 'Yes' : 'No' }}</td>
                            <td>{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i:s') : 'N/A'
                                }}
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center">No users found based on the applied filters.</td>
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
        $('#usersReportTable').DataTable({
            "order": [[12, "desc"]], // Sort by Creation Date
            "pageLength": 15
        });
    });
</script>
@endsection