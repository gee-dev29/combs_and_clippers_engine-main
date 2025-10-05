@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Store Report</h4>


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

        <form method="GET" action="{{ route('admin.reports.stores') }}"
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
                    <label for="store_name" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Store
                        Name</label>
                    <input type="text" name="store_name" id="store_name"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Filter by Store Name" value="{{ request('store_name') }}">
                </div>

                <div>
                    <label for="owner_name" class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Owner
                        Name</label>
                    <input type="text" name="owner_name" id="owner_name"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Filter by Owner Name" value="{{ request('owner_name') }}">
                </div>

                <div>
                    <label for="store_category"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Category</label>
                    <select name="store_category" id="store_category"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All Categories</option>
                        @foreach ($storeCategories as $category)
                        <option value="{{ $category->id }}" {{ request('store_category')==$category->id ? 'selected' :
                            ''
                            }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="approved"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Approved
                    </label>
                    <select name="approved" id="approved"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All</option>
                        <option value="1" {{ request('approved')=='1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('approved')=='0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div>
                    <label for="featured"
                        class="cnc:block cnc:text-gray-700 cnc:text-sm cnc:font-bold cnc:mb-2">Featured
                    </label>
                    <select name="featured" id="featured"
                        class="cnc:shadow appearance-none cnc:border cnc:rounded cnc:w-full cnc:py-2 cnc:px-3 cnc:text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">All</option>
                        <option value="1" {{ request('featured')=='1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('featured')=='0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            <div class="cnc:mt-4">
                <button type="submit" class="btn btn-primary mr-3">Apply Filters</button>
                <a href="{{ route('admin.reports.stores') }}" class="btn btn-secondary">Reset Filters</a>
            </div>
        </form>

        <div class="cnc:mb-4">
            <a href="{{ route('admin.reports.stores.download.excel', request()->all()) }}" class="btn btn-success mr-2">
                <i class="i-File-Excel"></i> Download as Excel
            </a>
            <a href="{{ route('admin.reports.stores.download.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="i-File-PDF"></i> Download as PDF
            </a>
        </div>

        <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
            <div class="cnc:overflow-x-auto">
                <table class="display table table-striped table-bordered" id="storesReportTable" style="width:100%">
                    <thead class="cnc:bg-gray-100">
                        <tr>
                            <th>#</th>
                            <th>Store Name</th>
                            <th>Owner Name</th>
                            <th>Category</th>
                            <th>User Count</th>
                            <th>Booth Count</th>
                            <th>Appointment Count</th>
                            <th>Featured</th>
                            <th>Approved</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $index => $store)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $store->store_name }}</td>
                            <td>{{ $store->owner->name ?? 'N/A' }}</td>
                            <td>{{ $store->category->name ?? 'N/A' }}</td>
                            <td>{{ $store->renters_count }}</td>
                            <td>{{ $store->booth_rent_count }}</td>
                            <td>{{ $store->bookings_count }}</td>
                            <td>
                                @if ($store->featured)
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-success">Yes</span>
                                @else
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($store->approved)
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-success">Yes</span>
                                @else
                                <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn-secondary">No</span>
                                @endif
                            </td>
                            <td>{{ $store->created_at ? $store->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No stores found based on the applied filters.</td>
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
        $('#storesReportTable').DataTable({
            "order": [[9, "desc"]], // Sort by Creation Date
            "pageLength": 15
        });
    });
</script>
@endsection