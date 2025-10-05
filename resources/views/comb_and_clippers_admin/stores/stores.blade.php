@extends('layouts.app')

@section('content')
<div class="cnc:container mx-auto p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Stores Management</h4>

    <!-- Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-4 cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
        <!-- 🏬 Total Stores -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Shop cnc:text-3xl"></i> Total Stores
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalStores) }}</h3>
            </div>
        </div>

        <!-- ✅ Approved Stores -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Yes cnc:text-3xl"></i> Approved Stores
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($approvedStores) }}</h3>
            </div>
        </div>

        <!-- ⌛ Pending Approval -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Clock cnc:text-3xl"></i> Pending Approval
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($pendingStores) }}</h3>
            </div>
        </div>

        <!-- 🌟 Featured Stores -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-red-50 cnc:text-red-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Favorites cnc:text-3xl"></i> Featured Stores
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($featuredStores) }}</h3>
            </div>
        </div>
    </div>

    <!-- Additional Renters Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-2 cnc:md:grid-cols-2 cnc:gap-4 cnc:mb-6">
        <!-- 👥 Total Booth Renters -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-purple-50 cnc:text-purple-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Conference cnc:text-3xl"></i> Total Booth Renters
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalRenters) }}</h3>
            </div>
        </div>

        <!-- 🏆 Store with Most Renters -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-indigo-50 cnc:text-indigo-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Conference cnc:text-3xl"></i> Store with Most Renters
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">
                    {{ $storeWithMostRenters ? $storeWithMostRenters->store_name : 'N/A' }}
                </h3>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('stores') }}" class="cnc:mb-6">
        <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4">
            <input type="text" name="store_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Store Name" value="{{ request('store_name') }}">
            <input type="text" name="merchant_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Merchant Name" value="{{ request('merchant_name') }}">
            <input type="text" name="renter_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Renter Name" value="{{ request('renter_name') }}">
            <input type="text" name="renter_email" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Renter Email" value="{{ request('renter_email') }}">
        </div>

        <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4">
            <select name="approval_status" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                <option value="">Select Status</option>
                <option value="1" {{ request('approval_status')=='1' ? 'selected' : '' }}>Approved</option>
                <option value="0" {{ request('approval_status')=='0' ? 'selected' : '' }}>Pending</option>
            </select>
            <select name="featured" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                <option value="">Select Featured</option>
                <option value="1" {{ request('featured')=='1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ request('featured')=='0' ? 'selected' : '' }}>No</option>
            </select>
            <input type="number" name="min_renters" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Min Renters" value="{{ request('min_renters') }}">
            <select name="only_booth_renters" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md">
                <option value="">Filter Booth Renters</option>
                <option value="1" {{ request('only_booth_renters')=='1' ? 'selected' : '' }}>Only Stores with Renters
                </option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3 mr-3">
            Apply Filters
        </button>

        <a href="{{ route('stores') }}" class="btn btn-secondary mt-3">Clear Filters</a>
    </form>

    <!-- Stores Table -->
    <div class="cnc:overflow-x-auto cnc:mt-6">
        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width:100%">
            <thead>
                <tr>
                    <th>Store</th>
                    <th>Merchant</th>
                    <th>Category</th>
                    <th>Approval</th>
                    <th>Featured</th>
                    <th>Booth Renters</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                <tr>
                    <td>{{ $store->store_name }}</td>
                   <td>{{ $store->owner->name ?? 'No Owner' }}</td>
                    <td>{{ $store->category->categoryname ?? "N/A" }}</td>
                    <td>
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-white btn 
                            {{ $store->approved ? 'btn-success' : 'btn-warning' }}">
                            {{ $store->approved ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                    <td class="cnc:p-3">
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-black btn 
                            {{ $store->featured ? 'cnc:btn cnc:btn-blue' : 'btn-light' }}">
                            {{ $store->featured ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="cnc:p-3">
                        <span class="cnc:px-2 cnc:py-1 cnc:rounded-md cnc:text-black btn btn-info">

                            {{$store->renters->count()}}

                        </span>
                    </td>
                    <td class="cnc:p-3 cnc:flex cnc:gap-4">
                        <a href="{{ route('admin.stores.show', $store->id)}}" class="btn btn-primary">View</a>
                        <form id="delete-form" action="{{ route('admin.stores.destroy', $store->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" id="alert-confirm" class="btn btn-danger">
                                <i class="i-Folder-Trash"></i> Delete store
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- DataTables Script -->
<script>
    document.querySelectorAll('.btn-danger').forEach(button => {
button.addEventListener('click', function (e) {
e.preventDefault(); // Prevent default form submission

// Get the form associated with the delete button
let form = this.closest('form');

Swal.fire({
title: 'Are you sure?',
text: "This Store will be deleted permanently!",
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