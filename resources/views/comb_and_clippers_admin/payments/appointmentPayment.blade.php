@extends('layouts.app')

@section('content')
<div class="cnc:container mx-auto p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Appointment Payments</h4>

    <!-- Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-5 cnc:md:grid-cols-3 cnc:gap-4">
        <!-- 💰 Total Revenue -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-6 cnc:py-4">
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
            <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Wallet cnc:text-3xl"></i> Pending Payments
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($pendingPayments, 2) }}</h3>
            </div>
        </div>

        <!-- ❌ Cancelled (Paid) -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-red-50 cnc:text-red-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Close-Window cnc:text-3xl"></i> Cancelled (Paid)
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($cancelledPaid, 2) }}</h3>
            </div>
        </div>

        <!-- ⚪ Cancelled (Unpaid) -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-gray-100 cnc:text-gray-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Close cnc:text-3xl"></i> Cancelled (Unpaid)
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($cancelledUnpaid, 2) }}</h3>
            </div>
        </div>

        <!-- 🧾 Processing Fees -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Receipt-3 cnc:text-3xl"></i> Processing Fees
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($processingFees, 2) }}</h3>
            </div>
        </div>
    </div>
    <!-- Filters -->
    <form method="GET" action="{{ route('appointment.payments') }}" class="cnc:my-6">
        <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4 ">
            <input type="text" name="customer" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Search by Customer" value="{{ request('customer') }}">

            <select name="payment_status" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                <option value="" {{ request('payment_status')===null ? 'selected' : '' }}>Filter by Payment Status
                </option>
                <option value="1" {{ request('payment_status')==='1' ? 'selected' : '' }}>Paid</option>
                <option value="0" {{ request('payment_status')==='0' ? 'selected' : '' }}>Not Paid</option>
            </select>

            <select name="status" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                <option value="" {{ request('status')===null ? 'selected' : '' }}>Filter by Status
                </option>
                <option value="Pending" {{ request('status')==='Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Paid" {{ request('status')==='Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Accepted" {{ request('status')==='Accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="Completed" {{ request('status')==='Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Cancelled" {{ request('status')==='Cancelled' ? 'selected' : '' }}>Cancelled</option>

            </select>

            <input type="date" name="from_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                value="{{ request('from_date') }}">
            <input type="date" name="to_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                value="{{ request('to_date') }}">
        </div>

        <button type="submit" class="btn btn-primary mt-3 mr-3">Apply
            Filters</button>
        <a href="{{ route('appointment.payments') }}" class="btn btn-secondary mt-3">Clear Filters</a>

    </form>
    <!-- Payments Table -->
    <div class="cnc:overflow-x-auto cnc:mt-6">
        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Appointment Ref</th>
                    <th>Customer</th>
                    <th>Service Provider</th>
                    <th>Store</th>
                    <th>Total Amount</th>
                    <th>Processing Fee</th>
                    <th>Payment Status</th>
                    <th>Status</th>
                    {{-- <th>Actions</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $index => $appointment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $appointment->appointment_ref }}</td>
                    <td>{{ optional($appointment->customer)->name ?? 'No Name' }}</td>
                    <td>{{ optional($appointment->serviceProvider)->name ?? 'No Provider' }}</td>
                    <td>{{ optional($appointment->store)->name ?? 'No Store' }}</td>
                    <td>₦{{ number_format($appointment->total_amount, 2) }}</td>
                    <td>₦{{ number_format($appointment->processing_fee, 2) }}</td>
                    <td>
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-white btn
                            {{ $appointment->payment_status == 1 ? 'btn-success' : 'btn-warning' }}">
                            {{ $appointment->payment_status == 1 ? 'Paid' : 'Pending' }}
                        </span>
                    </td>
                    <td class="cnc:p-3">
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-white btn
                            {{ $appointment->status == 'Cancelled' ? 'btn-danger' : 'btn-primary' }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    {{-- <td class="cnc:p-3">
                        @if($appointment->status == 'Cancelled' && $appointment->payment_status == 1)
                        <button class="btn btn-danger">Refund</button>
                        @endif
                    </td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- DataTables Script -->


@endsection