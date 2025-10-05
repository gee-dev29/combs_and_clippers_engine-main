@extends('layouts.app')

@section('content')
<style>
    .content {
        min-height: 120px;
    }

    #revenueChart {
        width: 100% !important;
        /* Ensures full width */
        height: 400px !important;
        /* Adjust the height as needed */
    }
</style>

<div class="main-content">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <h1>Dashboard</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <!-- Revenue Overview Cards -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Wallet cnc:text-2xl"></i> Total Revenue
                    </h5>
                </div>
                <div class="cnc:card-body mt-1 px-4">
                    <h2 class="cnc:text-primary">₦{{ number_format($totalRevenue, 2) }}</h2>
                    <p class="cnc:text-muted">From Wallet & Direct Payments</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Calendar cnc:text-2xl"></i> Monthly Revenue
                    </h5>
                </div>
                <div class="cnc:card-body mt-1 px-4">
                    <h2 class="cnc:text-success">₦{{ number_format($monthlyRevenue, 2) }}</h2>
                    <p class="cnc:text-muted">Revenue for this month</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-blue-100 cnc:text-blue-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Calendar cnc:text-2xl"></i> Yearly Revenue
                    </h5>
                </div>
                <div class="cnc:card-body mt-1 px-4">
                    <h2 class="cnc:text-info">₦{{ number_format($yearlyRevenue, 2) }}</h2>
                    <p class="cnc:text-muted">Revenue for this year</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Shop cnc:text-2xl"></i> Weekly Revenue
                    </h5>
                </div>
                <div class="cnc:card-body mt-1 px-4">
                    <h2 class="cnc:text-warning">₦{{ number_format($weeklyRevenue, 2) }}</h2>
                    <p class="cnc:text-muted">Revenue for this week</p>
                </div>
            </div>
        </div>
        <!-- Revenue Trends -->
        <!-- Revenue Trends Card -->
        <div class="col-lg-2">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-orange-50 cnc:text-orange-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Statistic cnc:text-2xl"></i> Revenue Trends
                    </h5>
                </div>
                <div class="cnc:card-body mt-1 px-4">
                    <p>Monthly Change:
                        <span class="{{ $monthlyChange >= 0 ? 'cnc:text-success' : 'cnc:text-danger' }}">
                            {{ number_format($monthlyChange, 2) }}%
                        </span>
                    </p>
                    <p>Weekly Change:
                        <span class="{{ $weeklyChange >= 0 ? 'cnc:text-success' : 'cnc:text-danger' }}">
                            {{ number_format($weeklyChange, 2) }}%
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Weekly Revenue Trends Graph -->
        <div class="col-lg-5 mb-4">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Bar-Chart-2 cnc:text-2xl"></i> Weekly Revenue Trends
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <canvas id="weeklyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Trends Graph -->
        <div class="col-lg-5 mb-4">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Bar-Chart-3 cnc:text-2xl"></i> Monthly Revenue Trends
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown Graph -->
        <div class="col-lg-7 mb-4 justify-content-center align-middle align-self-center mx-auto">
            <div class="card mb-4">
                <div class="cnc:card-header cnc:bg-purple-50 cnc:text-purple-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Pie-Chart-2 cnc:text-2xl"></i> Revenue Breakdown
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Withdrawals Section -->
        <div class="row">
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-4 cnc:py-2">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                            <i class="i-Clock cnc:text-2xl"></i> Pending Withdrawals
                        </h5>
                    </div>
                    <div class="cnc:card-body mt-1 px-4">
                        <h2 class="cnc:text-danger">₦{{ number_format($pendingWithdrawalsToday, 2) }}</h2>
                        <p class="cnc:text-muted">Awaiting Approval (Today)</p>
                        <p class="cnc:text-muted">This Week: ₦{{ number_format($pendingWithdrawalsThisWeek, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-4 cnc:py-2">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                            <i class="i-Check cnc:text-2xl"></i> Successful Withdrawals
                        </h5>
                    </div>
                    <div class="cnc:card-body mt-1 px-4">
                        <h2 class="cnc:text-success">₦{{ number_format($successfulWithdrawalsToday, 2) }}</h2>
                        <p class="cnc:text-muted">Processed Successfully (Today)</p>
                        <p class="cnc:text-muted">This Week: ₦{{ number_format($successfulWithdrawalsThisWeek, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="cnc:card-header cnc:bg-red-50 cnc:text-red-700 cnc:px-4 cnc:py-2">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                            <i class="i-Close cnc:text-2xl"></i> Failed Withdrawals
                        </h5>
                    </div>
                    <div class="cnc:card-body mt-1 px-4">
                        <h2 class="cnc:text-warning">₦{{ number_format($failedWithdrawalsToday, 2) }}</h2>
                        <p class="cnc:text-muted">Failed Transactions (Today)</p>
                        <p class="cnc:text-muted">This Week: ₦{{ number_format($failedWithdrawalsThisWeek, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="cnc:card-header cnc:bg-indigo-50 cnc:text-indigo-700 cnc:px-4 cnc:py-2">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                            <i class="i-Arrow-Around cnc:text-2xl"></i> Processing Withdrawals
                        </h5>
                    </div>
                    <div class="cnc:card-body mt-1 px-4">
                        <h2 class="cnc:text-primary">₦{{ number_format($processingWithdrawalsToday, 2) }}</h2>
                        <p class="cnc:text-muted">Currently Being Processed (Today)</p>
                        <p class="cnc:text-muted">This Week: ₦{{ number_format($processingWithdrawalsThisWeek, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Internal Withdrawals -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="cnc:card-header cnc:bg-blue-50 cnc:text-blue-700 cnc:px-4 cnc:py-2">
                        <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                            <i class="i-Inbox-Into cnc:text-2xl"></i> Internal Withdrawals
                        </h5>
                    </div>
                    <div class="cnc:card-body mt-1 px-4">
                        <h2 class="cnc:text-info">₦{{ number_format($internalWithdrawalsToday, 2) }}</h2>
                        <p class="cnc:text-muted">Internal Transfers (Today)</p>
                        <p class="cnc:text-muted">This Week: ₦{{ number_format($internalWithdrawalsThisWeek, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments & Merchants -->
        <div class="col-lg-6">
            <div class="card">
                <div class="cnc:card-header cnc:bg-yellow-50 cnc:text-yellow-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Calendar cnc:text-2xl"></i> Upcoming Appointments
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <ul class="list-group">
                        @foreach ($upcomingAppointments as $appointment)
                        <li class="list-group-item">
                            {{ $appointment->customer->name }} -
                            {{ \Carbon\Carbon::parse($appointment->date)->format('M d') }},
                            {{ \Carbon\Carbon::parse($appointment->time)->format('h:i A') }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="cnc:card-header cnc:bg-green-50 cnc:text-green-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Medal-2 cnc:text-2xl"></i> Top Merchants (Most Appointments)
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <ul class="list-group">
                        @foreach ($topMerchants as $merchant)
                        <li class="list-group-item">
                            👑 {{ $merchant->name }} - {{ $merchant->bookings_count }} bookings
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 cnc:mt-6 ">
            <div class="card">
                <div class="cnc:card-header cnc:bg-purple-50 cnc:text-purple-700 cnc:px-4 cnc:py-2">
                    <h5 class="cnc:card-title cnc:flex cnc:items-center cnc:gap-2">
                        <i class="i-Bar-Chart cnc:text-2xl"></i> Appointment Trends
                    </h5>
                </div>
                <div class="cnc:card-body">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6"></div>



        @php
        $totalUsers = \App\Models\User::count(); // Total registered users
        $totalAppointments = \App\Models\Appointment::count(); // Total number of appointments
        $boothRentals = \App\Models\BoothRental::count(); // Total number of booths rented
        $totalStores = \App\Models\Store::count(); // Total number of stores

        $stats = [
        ['icon' => 'i-Wallet', 'label' => 'Total Users', 'value' => $totalUsers],
        ['icon' => 'i-Calendar', 'label' => 'Total Appointments', 'value' => $totalAppointments],
        ['icon' => 'i-Shop', 'label' => 'Booth Rentals', 'value' => $boothRentals],
        ['icon' => 'i-Home1', 'label' => 'Total Stores', 'value' => $totalStores],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="#">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center">
                        <i class="{{ $stat['icon'] }}"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">{{ $stat['label'] }}</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
<!-- Include Chart.js for Graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Appointments Chart
    var ctx1 = document.getElementById('appointmentChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: @json($appointmentTrendslabels),
            datasets: [{
                label: 'Appointments',
                data: @json($appointmentTrendsdata),
                borderColor: 'blue',
                borderWidth: 2,
                fill: false
            }]
        }
    });

    // Revenue Chart (Pie)
    var ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Appointments', 'Booth Rentals', 'Internal Transactions', 'Wallet Transactions', 'Billing'],
            datasets: [{
                data: [
                    {{ $totalRevenueData['appointments'] }},
                    {{ $totalRevenueData['booth_rentals'] }},
                    {{ $totalRevenueData['internal_transactions'] }},
                    {{ $totalRevenueData['wallet_transactions'] }},
                    {{ $totalRevenueData['billing'] }}
                ],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d']
            }]
        }
    });

    // Weekly Revenue Chart (Bar)
    var ctxWeekly = document.getElementById('weeklyRevenueChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: {!! json_encode($weeklyLabels) !!},  // X-axis (Weeks)
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode($weeklyValues) !!},  // Y-axis (Revenue)
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { title: { display: true, text: 'Week' } },
                y: { title: { display: true, text: 'Revenue ($)' } }
            }
        }
    });

    // Monthly Revenue Chart (Bar)
    var ctxMonthly = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},  // X-axis (Months)
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode($monthlyValues) !!},  // Y-axis (Revenue)
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { title: { display: true, text: 'Month' } },
                y: { title: { display: true, text: 'Revenue ($)' } }
            }
        }
    });
</script>
@endsection