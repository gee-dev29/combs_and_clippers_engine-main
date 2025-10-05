@extends('layouts.app')

@section('content')

<style>
    input,
    select {
        height: 40px;
        font-size: 12px;
        padding: 10px;
    }
</style>
<div class="main-content cnc:px-6 cnc:py-8">

    <!-- Breadcrumb -->
    <div class="breadcrumb cnc:mb-10">
        <i class="i-Calendar-2"></i>
        <h1 class="cnc:text-4xl cnc:font-extrabold cnc:text-gray-900 cnc:tracking-tight">Appointment Details</h1>
    </div>

    <!-- Separator -->
    <div class="cnc:border-t cnc:border-gray-300 cnc:mb-10"></div>

    <!-- Appointment Cards Grid -->
    <div class="cnc:grid cnc:grid-cols-1 cnc:md:grid-cols-2 cnc:gap-8">

        <!-- Customer Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <div class="cnc:bg-blue-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-blue-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Add-UserStar"></i> Customer Info
                </h2>
            </div>
            <div class="cnc:bg-white cnc:p-6 cnc:flex">
                <img src="{{ $appointment->customer->profile_picture }}" alt="Customer Image"
                    class="cnc:w-[250px]  cnc:square cnc:mr-4">
                <div class="cnc:space-y-3">
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Name:</strong> {{ $appointment->customer->name }}
                    </p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Phone:</strong> {{ $appointment->phone_number }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Merchant Card -->
        <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
            <div class="cnc:bg-indigo-50 cnc:px-6 cnc:py-4">
                <h2 class="cnc:text-xl cnc:font-semibold cnc:text-indigo-700 cnc:flex cnc:items-center cnc:gap-2">
                    <i class="i-Shop"></i> Service Provider Info
                </h2>
            </div>
            <div class="cnc:bg-white cnc:p-6 cnc:flex">
                <img src="{{ $appointment->serviceProvider->profile_picture }}" alt="Merchant Image"
                    class="cnc:w-[250px] cnc:square cnc:mr-4">
                <div class="cnc:space-y-3">
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Name:</strong> {{
                        $appointment->serviceProvider->name }}</p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Store:</strong> {{
                        $appointment->store->store_name }}</p>
                    <p class="cnc:text-base cnc:text-gray-600"><strong>Address:</strong> {{
                        $appointment->store->storeAddress->address }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Info Card -->
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-yellow-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-yellow-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Calendar-4"></i> Appointment Info
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6 cnc:space-y-3">
            <p class="cnc:text-base cnc:text-gray-600"><strong>Date & Time:</strong> {{ $appointment->date }} - {{
                $appointment->time }}</p>
            <p class="cnc:text-base cnc:text-gray-600">
                <strong>Status:</strong>
                <span
                    class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $appointment->status == 'completed' ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-yellow-100 cnc:text-yellow-700' }}">
                    {{ ucfirst($appointment->status) }}
                </span>
            </p>
        </div>
    </div>

    {{-- Services --}}
    <div class="cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100 cnc:mt-6">
        <!-- Header -->
        <div class="cnc:bg-purple-100 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-purple-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Receipt cnc:text-2xl"></i> Services ({{ $appointment->appointmentService->count() }})
            </h2>
        </div>

        <!-- Body -->
        <div class="cnc:bg-white cnc:divide-y divide-gray-100 cnc:p-6">
            @foreach($appointment->appointmentService as $appointmentService)
            <div
                class="cnc:flex cnc:flex-col cnc:md:flex-row cnc:md:items-center cnc:gap-4 cnc:py-4 hover:bg-gray-50 transition">
                <!-- Image + Name -->
                <div class="cnc:flex cnc:items-start cnc:gap-3 cnc:flex-1 cnc:md:max-w-[250px]">
                    @if($appointmentService->service->photos->first())
                    <img src="{{ $appointmentService->service->photos->first()->image_url }}"
                        class="cnc:w-16 cnc:h-16 cnc:rounded cnc:object-cover cnc:border" alt="">
                    @else
                    <div class="cnc:w-16 cnc:h-16 cnc:bg-gray-200 cnc:rounded"></div>
                    @endif

                    <div class="cnc:min-w-0">
                        <p class="cnc:font-medium cnc:text-sm cnc:text-gray-800 cnc:truncate">{{
                            $appointmentService->service->name }}</p>

                        <span class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                            {!!
                            $appointmentService->service->description
                            !!}
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                <div class="cnc:text-wrap">
                                    {!!
                                    $appointmentService->service->description
                                    !!}

                                </div>
                            </div>
                        </span>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="cnc:flex-1 cnc:grid cnc:md:grid-cols-4 cnc:gap-4 cnc:mt-4 cnc:md:mt-0">
                    <!-- Duration -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Duration</p>
                        <p class="cnc:text-gray-700">{{ $appointmentService->service->duration ?? 'N/A' }}</p>
                    </div>

                    <!-- Price -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Price</p>
                        <p class="cnc:text-green-600 cnc:font-semibold">₦{{ number_format($appointmentService->price, 2)
                            }}
                        </p>
                    </div>

                    <!-- Availability -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Availability</p>
                        @if($appointmentService->service->availabilityHours->count())
                        <span class="cnc:inline-block cnc:max-w-[140px] cnc:truncate group cnc:cursor-pointer">
                            {{ $appointmentService->service->availabilityHours->count() }} day(s)
                            <span class="cnc:text-gray-400">...</span>
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($appointmentService->service->availabilityHours as $hour)
                                <div>
                                    {{ ucfirst($hour->day) }}:
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $hour->start_time)->format('g:i A') }}
                                    -
                                    {{ \Carbon\Carbon::createFromFormat('H:i:s', $hour->end_time)->format('g:i A') }}
                                </div>
                                @endforeach
                            </div>
                        </span>
                        @else
                        <span class="cnc:text-gray-500">No hours</span>
                        @endif
                    </div>

                    <!-- Promotions -->
                    <div class="cnc:text-sm">
                        <p class="cnc:text-gray-400">Promotions</p>
                        @if($appointmentService->service->promotions->count())
                        <span class="group relative">
                            {{ $appointmentService->service->promotions->count() }} promo{{
                            $appointmentService->service->promotions->count() > 1 ? 's' : '' }}
                            <div
                                class="cnc:absolute cnc:hidden cnc:px-4 cnc:py-2 cnc:text-sm cnc:text-white cnc:bg-gray-700 cnc:rounded-lg cnc:shadow-md tooltip-content">
                                @foreach($appointmentService->service->promotions as $p)
                                <div>{{ $p->title }}</div>
                                @endforeach
                            </div>
                        </span>
                        @else
                        <span class="cnc:text-gray-500">No promo</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            @if($appointment->appointmentService->count() > 3)
            <div class="cnc:pt-4 cnc:text-right">
                <button class="btn-primary btn" data-toggle="modal" data-target="#allServicesModal">
                    See all services
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Payment Info Card -->
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-gray-100">
        <div class="cnc:bg-green-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-green-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Money-Bag"></i> Payment Details
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6 cnc:space-y-3">
            <p class="cnc:text-base cnc:text-gray-600"><strong>Total:</strong> ₦{{ $appointment->total_amount }}</p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>Tip:</strong> ₦{{ $appointment->tip }}</p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>Fee:</strong> ₦{{ $appointment->processing_fee }}</p>
            <p class="cnc:text-base cnc:text-gray-600">
                <strong>Status:</strong>
                <span
                    class="cnc:inline-block cnc:px-2 cnc:py-1 cnc:rounded-full cnc:ml-2
                    {{ $appointment->payment_status === 'paid' ? 'cnc:bg-green-100 cnc:text-green-700' : 'cnc:bg-red-100 cnc:text-red-700' }}">
                    {{ ucfirst($appointment->payment_status) }}
                </span>
            </p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>Gateway:</strong> {{
                ucfirst($appointment->payment_gateway) }}</p>
            <p class="cnc:text-base cnc:text-gray-600"><strong>Ref:</strong> {{ $appointment->payment_ref }}</p>
        </div>
    </div>

    <!-- Cancellation -->
    @if($appointment->status == 'canceled')
    <div class="cnc:mt-8 cnc:rounded-2xl cnc:overflow-hidden cnc:shadow-md cnc:border cnc:border-red-300">
        <div class="cnc:bg-red-50 cnc:px-6 cnc:py-4">
            <h2 class="cnc:text-xl cnc:font-semibold cnc:text-red-700 cnc:flex cnc:items-center cnc:gap-2">
                <i class="i-Close-Window"></i> Cancellation Reason
            </h2>
        </div>
        <div class="cnc:bg-white cnc:p-6">
            <p class="cnc:text-base cnc:text-red-700">{{ $appointment->reason_for_cancelation }}</p>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="cnc:mt-12 cnc:flex cnc:gap-4 cnc:rounded-2xl">
        @if ($appointment->payment_status != 1)
        <form id="delete-form" action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" id="alert-confirm" class="btn btn-danger">
                <i class="i-Folder-Trash"></i> Delete Appointment
            </button>
        </form>
        @endif
    </div>
</div>
<!-- Edit Appointment Modal -->
{{-- <div id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle-2" aria-hidden="true"
    class="modal fade cnc:fixed cnc:inset-0 cnc:bg-black/30 cnc:flex cnc:items-center cnc:justify-center cnc:z-50 cnc:hidden"
    role="dialog" aria-modal="true">

    <div
        class="modal-dialog modal-dialog-centered cnc:bg-white cnc:rounded-xl cnc:shadow-lg cnc:max-w-4xl! cnc:w-full! cnc:mx-4">
        <div class="modal-content">
            <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header cnc:p-6 cnc:border-b cnc:rounded-t-xl">
                    <h2 class="cnc:text-2xl cnc:font-bold cnc:text-gray-800 nav-icon i-Edit"> Edit Appointment</h2>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="cnc:p-6 cnc:space-y-6 cnc:max-h-[70vh] cnc:overflow-y-auto">

                    <!-- Customer Info -->
                    <div>
                        <h4 class="cnc:font-semibold cnc:text-gray-700 mb-2"> 👤 Customer Info</h4>
                        <div class="cnc:grid cnc:grid-cols-1 md:cnc:grid-cols-2 cnc:gap-4">
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Customer Name</label>
                                <input type="text" name="customer_name" disabled
                                    value="{{ $appointment->customer->name }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Phone Number</label>
                                <input type="text" name="phone_number" value="{{ $appointment->phone_number }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Merchant Info -->
                    <div>
                        <h4 class="cnc:font-semibold cnc:text-gray-700 mb-2">🛃 Merchant Info</h4>
                        <div class="cnc:grid cnc:grid-cols-1 md:cnc:grid-cols-2 cnc:gap-4">
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Merchant Name</label>
                                <input type="text" disabled name="merchant_name"
                                    value="{{ $appointment->serviceProvider->name }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Store Name</label>
                                <input type="text" disabled name="store_name"
                                    value="{{ $appointment->store->store_name }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div class="md:cnc:col-span-2">
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Store Address</label>
                                <input type="text" disabled name="store_address"
                                    value="{{ $appointment->store->storeAddress->address }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Info -->
                    <div>
                        <h4 class="cnc:font-semibold cnc:text-gray-700 mb-2"> 🗓️ Appointment Info</h4>
                        <div class="cnc:grid cnc:grid-cols-1 md:cnc:grid-cols-2 cnc:gap-4">
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Date</label>
                                <input type="date" name="date" value="{{ $appointment->date }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Time</label>
                                <input type="time" name="time" value="{{ $appointment->time }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div class="md:cnc:col-span-2">
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Status</label>
                                <select name="status" id="statusSelect"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full">
                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : ''
                                        }}>
                                        Completed</option>
                                    <option value="canceled" {{ $appointment->status == 'canceled' ? 'selected' : '' }}>
                                        Canceled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Services -->
                    <div>
                        <h4 class="cnc:font-semibold cnc:text-gray-700 mb-2">Services</h4>
                        <div class="cnc:mb-2">
                            <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Select Services</label>
                            <select disabled id="services-select" name="services[]"
                                class="cnc:input cnc:border cnc:rounded-md cnc:w-full" multiple="multiple">
                                <!-- Already selected services as options -->
                                @foreach($appointment->appointmentService as $index => $service)
                                <option value="{{ $service->service->id }}" selected>
                                    {{ $service->service->name ?? 'No Service Name' }} - ₦{{ $service->price }}
                                </option>
                                @endforeach

                                <!-- Available services from the service provider -->
                                @foreach($appointment->serviceProvider->services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->name }} - ₦{{ $service->price }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Payment Info -->
                    <div>
                        <h4 class="cnc:font-semibold cnc:text-gray-700 mb-2">Payment Info</h4>
                        <div class="cnc:grid cnc:grid-cols-1 md:cnc:grid-cols-2 cnc:gap-4">
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Total Amount</label>
                                <input type="number" step="0.01" name="total_amount"
                                    value="{{ $appointment->total_amount }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Tip</label>
                                <input type="number" step="0.01" name="tip" value="{{ $appointment->tip }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Processing Fee</label>
                                <input type="number" step="0.01" name="processing_fee"
                                    value="{{ $appointment->processing_fee }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Payment Status</label>
                                <select name="payment_status" class="cnc:input cnc:border cnc:rounded-md cnc:w-full">
                                    <option value="paid" {{ $appointment->payment_status === 'paid' ? 'selected' : ''
                                        }}>
                                        Paid</option>
                                    <option value="unpaid" {{ $appointment->payment_status === 'unpaid' ? 'selected' :
                                        '' }}>
                                        Unpaid</option>
                                </select>
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Payment Gateway</label>
                                <input type="text" name="payment_gateway" value="{{ $appointment->payment_gateway }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                            <div>
                                <label class="cnc:text-sm cnc:font-medium cnc:text-gray-700">Payment Reference</label>
                                <input type="text" name="payment_ref" value="{{ $appointment->payment_ref }}"
                                    class="cnc:input cnc:border cnc:rounded-md cnc:w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Cancelation (conditional) -->
                    <div id="cancelationReasonWrapper"
                        class="{{ $appointment->status == 'canceled' ? '' : 'cnc:hidden' }}">
                        <h4 class="cnc:font-semibold cnc:text-red-700 mb-2">Cancellation</h4>
                        <label class="cnc:text-sm cnc:font-medium cnc:text-red-700 mb-1">Reason</label>
                        <textarea name="reason_for_cancelation"
                            class="cnc:input cnc:border cnc:rounded-md cnc:w-full cnc:min-h-[100px]">{{ $appointment->reason_for_cancelation }}</textarea>
                    </div>
                </div>

                <div class="cnc:p-4 cnc:flex cnc:justify-end cnc:gap-3 cnc:border-t">
                    <button type="button"
                        class="cnc:px-4 cnc:py-2 cnc:bg-gray-200 cnc:text-gray-800 cnc:rounded cnc:hover:bg-gray-300"
                        data-dismiss="modal">Cancel</button>
                    <button type="submit"
                        class="cnc:px-4 cnc:py-2 cnc:bg-blue-600 cnc:text-white cnc:rounded cnc:hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<!-- JavaScript to toggle cancel reason -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const statusSelect = document.getElementById("statusSelect");
        const cancelReasonWrapper = document.getElementById("cancelationReasonWrapper");

        function toggleCancelReason() {
            if (statusSelect.value === "canceled") {
                cancelReasonWrapper.classList.remove("cnc:hidden");
            } else {
                cancelReasonWrapper.classList.add("cnc:hidden");
            }
        }

        statusSelect.addEventListener("change", toggleCancelReason);
        toggleCancelReason();
    });

        $(document).ready(function() {
            $('#services-select').select2({
                placeholder: 'Select Services',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });
        });


        document.getElementById('alert-confirm').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            
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
            document.getElementById('delete-form').submit();
            }
            });
            });
</script>
<script>
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