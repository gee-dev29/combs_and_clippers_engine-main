<!DOCTYPE html>
<html>

<head>
    <title>General Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="title">General Report</div>
    @if(isset($reportData['startDate']) && isset($reportData['endDate']))
    <p>Date Range: {{ \Carbon\Carbon::parse($reportData['startDate'])->format('M d, Y') }} - {{
        \Carbon\Carbon::parse($reportData['endDate'])->format('M d, Y') }}</p>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Metric</th>
                <th>Total Value</th>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <th>Value ({{ \Carbon\Carbon::parse($reportData['startDate'])->format('M d, Y') }} - {{
                    \Carbon\Carbon::parse($reportData['endDate'])->format('M d, Y') }})</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Users</td>
                <td>{{ $reportData['totalUsers'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newUsersInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Customers</td>
                <td>{{ $reportData['totalCustomers'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newCustomersInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Merchants</td>
                <td>{{ $reportData['totalMerchants'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newMerchantsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Stores</td>
                <td>{{ $reportData['totalStores'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newStoresInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Featured Stores</td>
                <td>{{ $reportData['featuredStores'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newFeaturedStoresInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Services</td>
                <td>{{ $reportData['totalServices'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['newServicesInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Appointments</td>
                <td>{{ $reportData['totalAppointments'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['totalAppointmentsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Completed Appointments</td>
                <td>{{ $reportData['totalCompletedAppointments'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['completedAppointmentsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Revenue</td>
                <td>₦{{ number_format($reportData['totalRevenue'], 2) }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>₦{{ number_format($reportData['totalRevenueInRange'], 2) }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Booth Rentals</td>
                <td>{{ $reportData['totalBoothRentals'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['boothRentalsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Active Booth Rentals</td>
                <td>{{ $reportData['activeBoothRentals'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['activeBoothRentalsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Booth Rental Revenue</td>
                <td>₦{{ number_format($reportData['boothRentalRevenue'], 2) }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>₦{{ number_format($reportData['boothRentalRevenueInRange'], 2) }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Wallet Balance</td>
                <td>₦{{ number_format($reportData['totalWalletBalance'], 2) }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>₦{{ number_format($reportData['walletBalanceInRange'], 2) }}</td>
                @endif
            </tr>
            <tr>
                <td>Total Withdrawals</td>
                <td>{{ $reportData['totalWithdrawals'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['withdrawalsInRange'] }}</td>
                @endif
            </tr>
            <tr>
                <td>Pending Withdrawals</td>
                <td>{{ $reportData['pendingWithdrawals'] }}</td>
                @if(isset($reportData['startDate']) && isset($reportData['endDate']))
                <td>{{ $reportData['pendingWithdrawalsInRange'] }}</td>
                @endif
            </tr>
        </tbody>
    </table>
</body>

</html>