@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Admin Dashboard</h1>
        <span class="ml-2">Manage Accounts</span>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <!-- Search Bar -->
    <form action="{{ route('accounts') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search by Name, Email, Phone"
                value="{{ request('search') }}">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('accounts') }}" class="btn btn-secondary">Clear</a>
            </div>
        </div>
    </form>

    <!-- Filters (Collapsible) -->
    <button class="btn btn-light mb-3" type="button" data-toggle="collapse" data-target="#filters">
        <i class="fas fa-filter"></i> Filters
    </button>

    <div class="collapse" id="filters">
        <div class="card card-body">
            <form action="{{ route('accounts') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- General Info -->
                    <div class="col-md-3">
                        <label>Account Type</label>
                        <select class="form-control" name="account_type">
                            <option value="">All</option>
                            <option value="merchant" {{ request('account_type')=='merchant' ? 'selected' : '' }}>
                                Merchant</option>
                            <option value="client" {{ request('account_type')=='client' ? 'selected' : '' }}>Client
                            </option>
                            <option value="owner" {{ request('account_type')=='owner' ? 'selected' : '' }}>Owner
                            </option>
                            <option value="stylist" {{ request('account_type')=='stylist' ? 'selected' : '' }}>Stylist
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Account Status</label>
                        <select class="form-control" name="status">
                            <option value="">All</option>
                            <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>Suspended
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Email Verified</label>
                        <select class="form-control" name="email_verified">
                            <option value="">All</option>
                            <option value="yes" {{ request('email_verified')=='yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('email_verified')=='no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Date Created</label>
                        <input type="date" class="form-control" name="created_at" value="{{ request('created_at') }}">
                    </div>

                    <!-- Subscription & Billing -->
                    <div class="col-md-3">
                        <label>Has Active Subscription?</label>
                        <select class="form-control" name="has_subscription">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_subscription')=='yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_subscription')=='no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Auto-Renewal</label>
                        <select class="form-control" name="auto_renewal">
                            <option value="">All</option>
                            <option value="yes" {{ request('auto_renewal')=='yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('auto_renewal')=='no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Wallet & Transactions -->
                    <div class="col-md-3">
                        <label>Wallet ID</label>
                        <input type="text" class="form-control" name="wallet_id" value="{{ request('wallet_id') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Apply Filters</button>
                <a href="{{ route('accounts') }}" class="btn btn-secondary mt-3">Clear Filters</a>
            </form>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Account List</h5>
            <div class="table-responsive">
                <table class="display table table-striped table-bordered" id="zero_configuration_table"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name <a href="?sort=name"><i class="fas fa-sort"></i></a></th>
                            <th>Email <a href="?sort=email"><i class="fas fa-sort"></i></a></th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Subscription</th>
                            <th>Wallet Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $index => $account)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->email }}</td>
                            <td>{{ $account->phone }}</td>
                            <td>{{ $account->account_type }}</td>
                            <td>
                                <span class="badge badge-{{ $account->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td>
                                @if($account->has_subscription)
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-secondary">None</span>
                                @endif
                            </td>
                            <td>₦{{ number_format($account->wallet_balance, 2) }}</td>
                            <td class="cnc:flex cnc:gap-4">
                                <a href="{{ route('account.show',$account->id)}}" class="btn btn-sm btn-info">View</a>
                                <form id="delete-form" action="{{ route('admin.accounts.destroy', $account->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" id="alert-confirm" class="btn btn-sm btn-danger">
                                        Delete Account
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- <h4 class="font-bold"> hello World</h4> --}}
<script>
    document.querySelectorAll('.btn-danger').forEach(button => {
    button.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent default form submission
    
    // Get the form associated with the delete button
    let form = this.closest('form');
    
    Swal.fire({
    title: 'Are you sure?',
    text: "This Account will be deleted permanently!",
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