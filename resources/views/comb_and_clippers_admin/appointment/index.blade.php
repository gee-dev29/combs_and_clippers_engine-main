@extends('layouts.app')

@section('content')

<div class="main-content">
    <div class="breadcrumb">
        <h1>Appointments</h1>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <!-- Filters -->
    <form action="{{ route('appointments') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control" name="status">
                    <option value="">Filter by Status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                    <option value="canceled" {{ request('status')=='canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Search by Customer/Merchant"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <!-- Appointments Table -->
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
                            <th>Date & Time</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $index => $appointment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $appointment->customer->name ?? null }}</td>
                            <td>{{ $appointment->serviceProvider->name ?? null }}</td>
                            <td>{{ $appointment->date }} - {{ $appointment->time }}</td>
                            <td>₦{{ $appointment->total_amount }}</td>
                            <td>
                                <span
                                    class="badge badge-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'canceled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $appointment->payment_status === 'paid' ? 'success' : 'danger' }}">
                                    {{ ucfirst($appointment->payment_status) }}
                                </span>
                            </td>
                            <td class="cnc:flex cnc:gap-4">
                                <a href="{{ route('appointment.show',$appointment->id)}}"
                                    class="btn btn-sm btn-info">View</a>
                                @if ($appointment->payment_status != 1)
                                <form id="delete-form"
                                    action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" id="alert-confirm" class="btn btn-sm btn-danger">
                                        Delete Appointment
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // Attach SweetAlert to all delete buttons
document.querySelectorAll('.btn-danger').forEach(button => {
button.addEventListener('click', function (e) {
e.preventDefault(); // Prevent default form submission

// Get the form associated with the delete button
let form = this.closest('form');

Swal.fire({
title: 'Are you sure?',
text: "This appointment will be deleted permanently!",
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Yes, delete it!',
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