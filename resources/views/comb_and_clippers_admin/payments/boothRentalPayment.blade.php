@extends('layouts.app')

@section('content')
<div class="cnc:container cnc:mx-auto cnc:p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Booth Rental Payments</h4>

    <!-- 📊 Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
        <!-- 💰 Total Revenue -->
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

        <!-- ⏳ Pending Payments -->
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

        <!-- ⚠️ Overdue Payments -->
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

        <!-- 🧾 Processing Fees -->
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

    <!-- 🔍 Filters -->
    <form method="GET" action="{{ route('boothrent.payments') }}"
        class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow cnc:mb-6">
        <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4">
            <input type="text" name="tenant" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded"
                placeholder="Search by Tenant" value="{{ request('tenant') }}">

            <select name="store_id" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded">
                <option value="">Filter by Store</option>
                @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id')==$store->id ? 'selected' : '' }}>
                    {{ $store->store_name }}
                </option>
                @endforeach
            </select>

            <select name="payment_status" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded">
                <option value="">Filter by Status</option>
                <option value="paid" {{ request('payment_status')=='paid' ? 'selected' : '' }}>Paid</option>
                <option value="upcoming" {{ request('payment_status')=='upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="due" {{ request('payment_status')=='due' ? 'selected' : '' }}>Due</option>
                <option value="overdue" {{ request('payment_status')=='overdue' ? 'selected' : '' }}>Overdue</option>
            </select>

            <input type="date" name="from_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded"
                value="{{ request('from_date') }}">
            <input type="date" name="to_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded"
                value="{{ request('to_date') }}">
        </div>
        <button type="submit" class="btn btn-primary mt-3 mr-3">
            Apply Filters
        </button>

        <a href="{{ route('boothrent.payments') }}" class="btn btn-secondary mt-3">Clear Filters</a>
    </form>

    <!-- 📋 Payments Table -->
    <div class="cnc:bg-white cnc:p-4 cnc:rounded cnc:shadow">
        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width:100%">
            <thead class="cnc:bg-gray-100">
                <tr>
                    <th>#</th>
                    <th>Tenant</th>
                    <th>Store</th>
                    <th>Amount</th>
                    <th>Next Payment Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($boothPayments as $index => $rental)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $rental->boothRental->user->name ?? 'N/A' }}</td>
                    <td>{{ $rental->boothRental->store->store_name ?? 'N/A' }}</td>
                    <td>₦{{ number_format($rental->amount, 2) }}</td>
                    <td>{{ optional($rental->next_payment_date)->format('M d, Y') }}</td>
                    <td>
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded cnc:text-white btn 
                            {{ $rental->payment_status == 'paid' ? 'btn-success' : 
                            ($rental->payment_status == 'overdue' ? 'btn-danger' : 'btn-warning') }}">
                            {{ ucfirst($rental->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <div class="cnc:flex cnc:justify-evenly">
                            <form action="{{ route('admin.markBoothRentAsPaid', $rental->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="button" id="alert-confirm" class="{{ $rental->payment_status == 'due' || $rental->payment_status == 'overdue' 
                                    ? 'btn btn-success mark-as-paid' 
                                    : 'cnc:invisible cnc:px-2 cnc:py-1 cnc:rounded!' }}">
                                    Mark As Paid
                                </button>
                            </form>
                            <form action="{{ route('admin.sendBoothRentReminder', $rental->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button class="btn-warning btn reminder">
                                    Send Reminder
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready( function () {
        $('#boothRentalsTable').DataTable({
            "order": [[4, "asc"]], // Sort by Next Payment Date
            "pageLength": 10
        });
    });

    function markAsPaid(id) {
        if (confirm("Mark this payment as paid?")) {
            // Send AJAX request (Implement backend)
            alert("Payment marked as paid for ID: " + id);
        }
    }

    function sendReminder(id) {
        // Implement reminder logic
        alert("Reminder sent for Payment ID: " + id);
    }

    document.querySelectorAll('.mark-as-paid').forEach(button => {
    button.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent default form submission
    
    // Get the form associated with the clicked button button
    let form = this.closest('form');
    
    Swal.fire({
    title: 'Are you sure?',
    text: "This Booth Rent Payment will Marked as Paid",
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Yes, Mark as Paid',
    cancelButtonText: 'Cancel',
    reverseButtons: true
    }).then((result) => {
    if (result.isConfirmed) {
    // Submit the form if confirmed
    form.submit();
    }
    });
    });
    });


    document.querySelectorAll('.reminder').forEach(button => {
    button.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent default form submission
    
    // Get the form associated with the clicked button button
    let form = this.closest('form');
    
    Swal.fire({
    title: 'Are you sure?',
    text: "A reminder notification will be sent to the tenant of this booth",
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Yes, send reminder',
    cancelButtonText: 'Cancel',
    reverseButtons: true
    }).then((result) => {
    if (result.isConfirmed) {
    // Submit the form if confirmed
    form.submit();
    }
    });
    });
    });
</script>
@endsection