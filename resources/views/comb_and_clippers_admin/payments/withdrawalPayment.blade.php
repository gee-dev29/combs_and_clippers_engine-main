{{-- Create View for withdrawal payments --}}


@extends('layouts.app')

@section('content')
<div class="cnc:container mx-auto p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Withdrawal Payments</h4>

    <!-- Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
        <div class="card">

            <div class="cnc:card-header cnc:bg-green-100 cnc:text-green-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg"><i
                        class="i-Money-Bag cnc:text-3xl"></i> Total Withdrawals</h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">₦{{ number_format($totalWithdrawals, 2) }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="cnc:card-header cnc:bg-yellow-100 cnc:text-yellow-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Clock cnc:text-3xl"></i> Pending Withdrawals
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3>₦{{ number_format($pendingWithdrawals, 2) }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Receipt-3 cnc:text-3xl"></i>Successful Withdrawals
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3>₦{{ number_format($successfulWithdrawals, 2) }}</h3>
            </div>
        </div>
        <div class="card">
            <div class="cnc:card-header cnc:bg-red-100 cnc:text-red-700 cnc:px-6 cnc:py-4 cnc:rounded-t-xl">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Close-Window cnc:text-3xl"></i>Failed Withdrawals
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3>₦{{ number_format($failedWithdrawals, 2) }}</h3>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Filters -->
<form method="GET" action="{{ route('withdrawal.payments') }}" class="cnc:mb-6">
    <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4">
        <input type="text" name="user_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md" placeholder="User Name"
            value="{{ request('user_name') }}">
        <input type="text" name="bank_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md" placeholder="Bank Name"
            value="{{ request('bank_name') }}">
        <input type="text" name="account_number" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
            placeholder="Account Number" value="{{ request('account_number') }}">
        <select name="withdrawal_status" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
            <option value="">Select Status</option>
            <option value="0" {{ request('withdrawal_status')=='0' ? 'selected' : '' }}>Pending</option>
            <option value="3" {{ request('withdrawal_status')=='3' ? 'selected' : '' }}>Processing</option>
            <option value="1" {{ request('withdrawal_status')=='1' ? 'selected' : '' }}>Successful</option>
            <option value="2" {{ request('withdrawal_status')=='2' ? 'selected' : '' }}>Failed</option>
        </select>
        <input type="date" name="from_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
            value="{{ request('from_date') }}">
        <input type="date" name="to_date" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
            value="{{ request('to_date') }}">
    </div>
    <button type="submit" class="btn btn-primary mt-3 mr-3">
        Apply Filters
    </button>
    <a href="{{ route('withdrawal.payments') }}" class="btn btn-secondary mt-3">Clear Filters</a>
</form>

<!-- Withdrawals Table -->
<div class="cnc:overflow-x-auto cnc:mt-6">
    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width:100%">
        <thead>
            <tr>
                <th>User</th>
                <th>Amount</th>
                <th>Account</th>
                <th>Bank</th>
                <th>Status</th>
                <th>Date</th>
                {{-- <th>Actions</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($withdrawals as $withdrawal)
            <tr>
                <td>{{ $withdrawal->user->name }}</td>
                <td>₦{{ number_format($withdrawal->amount, 2) }}</td>
                <td>{{ $withdrawal->account_number }}</td>
                <td>{{ $withdrawal->bank_name }}</td>
                <td>
                    <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-white 
                            {{ $withdrawal->withdrawal_status == 0 ? ' btn-warning' : '' }}
                            {{ $withdrawal->withdrawal_status == 3 ? 'btn-info' : '' }}
                            {{ $withdrawal->withdrawal_status == 1 ? 'btn-success' : '' }}
                            {{ $withdrawal->withdrawal_status == 2 ? 'btn-danger' : '' }}">
                        @if($withdrawal->withdrawal_status == 0) Pending
                        @elseif($withdrawal->withdrawal_status == 3) Processing
                        @elseif($withdrawal->withdrawal_status == 1) Successful
                        @else Failed
                        @endif
                    </span>
                </td>
                <td class="cnc:p-3">{{ $withdrawal->created_at->format('d M Y') }}</td>
                {{-- <td class="cnc:p-3">
                    <a href="#" class="btn btn-primary">View</a>
                </td> --}}
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>

<!-- DataTables Script -->
<script>
    $(document).ready(function() {
        $('#withdrawals_table').DataTable();
    });
</script>

@endsection