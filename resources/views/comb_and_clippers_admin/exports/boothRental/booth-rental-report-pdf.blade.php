<!DOCTYPE html>
<html>

<head>
    <title>Booth Rental Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Grid for summary cards (using floats) */
        .grid-lg-2 {
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 0.5rem;
            margin-bottom: 16px;
            width: 48%;
            /* Roughly half width */
            float: left;
            margin-right: 4%;
            /* Add some space between cards */
            border: 1px solid #ddd;
            /* Add border as alternative to shadow */
        }

        .card:last-child {
            margin-right: 0;
            /* Remove margin from the last card in the row */
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .card-header {
            background-color: #f2f7ff;
            color: #4299e1;
            padding: 16px;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            display: block;
        }

        .card-title i {
            font-size: 1.5rem;
            vertical-align: middle;
            margin-right: 8px;
        }

        .card-body {
            padding: 16px;
        }

        .text-2xl {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .bg-blue-100 {
            background-color: #f2f7ff;
            color: #4299e1;
        }

        .bg-green-100 {
            background-color: #e6f7ed;
            color: #38a169;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            /* Adjust font size for better fit */
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .overflow-x-auto {
            /* Consider removing or finding alternative */
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 16px;">Booth Rental Report</h4>

        <div class="grid-lg-2 clearfix">
            <div class="card">
                <div class="card-header bg-blue-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> <span>Total Booth Rentals</span></h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">{{ number_format($totalRentals) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-green-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> <span>Total Rental Amount</span></h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 16px; border-radius: 0.5rem; border: 1px solid #ddd;">
            <div style="/* overflow-x: auto; */">
                <table class="table" style="width: 100%;">
                    <thead style="background-color: #f7fafc;">
                        <tr>
                            <th>ID</th>
                            <th>Store</th>
                            <th>Renter</th>
                            <th>Payment Timeline</th>
                            <th>Amount</th>
                            <th>Service Type</th>
                            <th>Payment Days</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boothRentals as $rental)
                        <tr>
                            <td>{{ $rental->id }}</td>
                            <td>{{ $rental->store->store_name ?? 'N/A' }}</td>
                            <td>{{ $rental->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($rental->payment_timeline) }}</td>
                            <td>₦{{ number_format($rental->amount, 2) }}</td>
                            <td>{{ $rental->serviceType->name ?? 'N/A' }}</td>
                            <td>{{ $rental->payment_days }}</td>
                            <td>{{ $rental->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No booth rentals found based on the applied filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>