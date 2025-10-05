@extends('layouts.app')

@section('content')
<div class="cnc:container mx-auto p-6">
    <h4 class="cnc:text-lg cnc:font-semibold cnc:mb-4">Service Types Management</h4>

    <!-- Summary Statistics -->
    <div class="cnc:grid cnc:lg:grid-cols-4  cnc:md:grid-cols-3 cnc:gap-4 cnc:mb-6">
        <!-- Total Service Types -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Library cnc:text-3xl"></i> Total Service Types
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($totalServiceTypes) }}</h3>
            </div>
        </div>

        <!-- Most Booked Service Type -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Calendar cnc:text-3xl"></i> Most Booked Service Type
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ ucwords($mostBookedServiceType->name) ?? 'N/A' }}</h3>
            </div>
        </div>

        <!-- Most Rendered Service Type -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Medal cnc:text-3xl"></i> Most Rendered Service Type
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ ucwords($mostRenderedServiceType->name) ?? 'N/A' }}</h3>
            </div>
        </div>

        <!-- Service Types with Renters -->
        <div class="card">
            <div class="cnc:card-header cnc:bg-red-50 cnc:text-red-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Business-Mens cnc:text-3xl"></i> Service Types with Renters
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <h3 class="cnc:text-2xl cnc:font-bold">{{ number_format($serviceTypesWithRenters->count()) }}</h3>
            </div>
        </div>
    </div>

    <!-- Service Type Popularity by Interests -->
    <div class="cnc:grid cnc:gap-4 cnc:mb-6">
        <!-- Interest Popularity -->
        <div class="card w-full">
            <div class="cnc:card-header cnc:bg-purple-50 cnc:text-purple-700 cnc:px-6 cnc:py-4">
                <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-3 cnc:text-lg">
                    <i class="i-Heart cnc:text-3xl"></i> Most Interested Service Types
                </h5>
            </div>
            <div class="cnc:card-body cnc:px-6 cnc:py-5">
                <ul class="cnc:space-y-3">
                    @forelse($serviceTypesWithMostInterests as $serviceType)
                    <li
                        class="cnc:flex cnc:items-center cnc:justify-between cnc:bg-purple-50 cnc:px-4 cnc:py-2 cnc:rounded-md hover:cnc:bg-purple-200 transition">
                        <div class="cnc:flex cnc:items-center cnc:gap-3">
                            <i class="i-Right-3 cnc:text-purple-500"></i>
                            <span class="cnc:font-semibold cnc:text-black">{{ ucwords($serviceType->name) }}</span>
                        </div>
                        <span class="btn btn-primary">
                            {{ $serviceType->interests->count() }} interests
                        </span>
                    </li>
                    @empty
                    <li class="cnc:text-gray-500">No interests recorded.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('service.types') }}" class="cnc:mb-6">
        <div class="cnc:grid cnc:grid-cols-4 cnc:gap-4 cnc:mb-4">
            <input type="text" name="service_type_name" class="cnc:w-full cnc:p-2 cnc:border cnc:rounded-md"
                placeholder="Service Type Name" value="{{ request('service_type_name') }}">
        </div>

        <button type="submit" class="btn btn-primary mt-3 mr-3">
            Apply Filters
        </button>
        <a href="{{ route('service.types') }}" class="btn btn-secondary mt-3">Clear Filters</a>
    </form>
    <div class="cnc:flex cnc:justify-end cnc:mb-4">
        <button data-toggle="modal" data-target="#createServiceTypeModal" class="btn btn-success">
            <i class="i-Add cnc:mr-1"></i> Create New Service Type
        </button>
    </div>

    <!-- Service Types Table -->
    <div class="cnc:overflow-x-auto cnc:mt-6">
        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width:100%">
            <thead>
                <tr>
                    <th>Service Type</th>
                    <th>Store</th>
                    <th>Booth Rentals</th>
                    <th>Interests</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceTypes as $serviceType)
                <tr>
                    <td>{{ucwords($serviceType->name) }}</td>
                    <td class="cnc:whitespace-nowrap">
                        <span class="cnc:inline-block cnc:max-w-[140px]  group cnc:cursor-pointer">
                            {{$serviceType->storeServiceTypes->count() }} stores offers this Service
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($serviceType->storeServiceTypes as $storeServiceType)
                                <div>
                                    {{ $storeServiceType->store->store_name ?? null}}
                                </div>
                                @endforeach
                            </div>
                        </span>
                    </td>
                    <td class="cnc:whitespace-nowrap">
                        <span class="cnc:inline-block cnc:max-w-[140px]  group cnc:cursor-pointer">
                            {{ $serviceType->boothRentals->count() }} booth(s) offer(s) this service
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($serviceType->boothRentals as $boothRental)
                                <div>
                                    {{ $boothRental->store->store_name ?? null}}
                                </div>
                                @endforeach
                            </div>
                        </span>
                    </td>
                    <td>
                        {{ $serviceType->interests->count() }}
                    </td>
                    <td class="cnc:flex cnc:gap-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#editServiceTypeModal-{{$serviceType->id}}">
                            Edit
                        </button>

                        <form id="delete-form" action="{{ route('admin.storeServiceType.destroy',$serviceType->id) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" id="alert-confirm" class="btn btn-danger">
                                <i class="i-Folder-Trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editServiceTypeModal-{{$serviceType->id}}" tabindex="-1" role="dialog"
                    aria-labelledby="editServiceTypeModalTitle-{{$serviceType->id}}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="i-Edit cnc:text-blue-500 mr-2"></i> Edit Service Type
                                </h5>
                                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body p-4">
                                <form action="{{ route('admin.serviceType.update', $serviceType->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="card mb-5">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <input class="form-control" type="text" name="serviceType_name"
                                                    value="{{ old('serviceType_name', $serviceType->name) }}"
                                                    placeholder="Enter Service Type Name" />
                                            </div>
                                            <button class="btn btn-primary" type="submit">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

{{-- Modals --}}

{{-- Create modal --}}

<div class="modal fade" id="createServiceTypeModal" tabindex="-1" role="dialog"
    aria-labelledby="createServiceTypeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered cnc:max-w-4xl! cnc:w-full!" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createServiceTypeModalTitle">
                    <i class="i-Home cnc:text-blue-500 mr-2"></i> Create New Service Type
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.serviceType.create') }}" method="POST">
                    @method('POST')
                    @csrf
                    <div class="card mb-5">
                        <div class="card-body">
                            <h5 class="modal-title" id="createServiceTypeModalTitle">
                                <i class="i-Home cnc:text-blue-500 mr-2"></i> Create New Service Type
                            </h5>
                            <div class="d-flex flex-column">
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Enter Service Type Name"
                                        name="serviceType_name" />
                                </div>
                                <button class="btn btn-primary pd-x-20" type="submit">Create </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<script>
    document.querySelectorAll('.btn-danger').forEach(button => {
button.addEventListener('click', function (e) {
e.preventDefault(); // Prevent default form submission

// Get the form associated with the delete button
let form = this.closest('form');

Swal.fire({
title: 'Are you sure?',
text: "This ServiceType will be deleted permanently!",
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

    // Add this script for mobile tap/focus support
                document.querySelectorAll('.group').forEach(item => {
                item.addEventListener('click', (e) => {
                const tooltip = e.target.querySelector('.tooltip-content');
                console.log("somethong",tooltip)
                if (tooltip) {
                tooltip.classList.toggle('cnc:hidden'); // Toggle the hidden class to show/hide tooltip
                }
                });
    
                item.addEventListener('mouseover', (e) => {
                const tooltip = e.target.querySelector('.tooltip-content');
                console.log("somethong",tooltip)
                if (tooltip) {
                tooltip.classList.remove('cnc:hidden'); // Show tooltip on focus
                }
                });
    
                item.addEventListener('mouseleave', (e) => {
                const tooltip = e.target.querySelector('.tooltip-content');
                console.log("somethong")
                if (tooltip) {
                tooltip.classList.add('cnc:hidden'); // Hide tooltip when focus is lost
                }
                });
    
                item.addEventListener('blur', (e) => {
                const tooltip = e.target.querySelector('.tooltip-content');
                console.log("somethong")
                if (tooltip) {
                tooltip.classList.add('cnc:hidden'); // Hide tooltip when focus is lost
                }
                });
                });

</script>

@endsection