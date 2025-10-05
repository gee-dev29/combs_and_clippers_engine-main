<!DOCTYPE html>
<html>

<head>
    <title>Booth Rental Payment Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .grid-lg-4 {
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 0.5rem;
            margin-bottom: 16px;
            width: 23.5%;
            /* Roughly quarter width with spacing */
            float: left;
            margin-right: 2%;
            border: 1px solid #ddd;
        }

        .card:last-child {
            margin-right: 0;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .card-header {
            padding: 16px;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            color: white;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
            display: block;
        }

        .card-body {
            padding: 16px;
        }

        .text-2xl {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .bg-green-100 {
            background-color: #38a169;
        }

        .bg-yellow-100 {
            background-color: #f6ad55;
        }

        .bg-red-100 {
            background-color: #e53e3e;
        }

        .bg-blue-100 {
            background-color: #4299e1;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            /* font-size: 9pt; */
            /* Remove the general table font size */
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 8pt;
            /* Reduce font size for table cells */
        }

        .table th {
            background-color: #f7fafc;
            font-weight: bold;
            font-size: 9pt;
            /* Slightly larger font for headers */
        }

        .text-center {
            text-align: center;
        }

        .status-paid {
            background-color: #38a169;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .status-warning {
            background-color: #f6ad55;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .status-danger {
            background-color: #e53e3e;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        /* Consider this alternative for overflow, might have limited support */
        /*.table { table-layout: auto; width: auto; }*/
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 16px;">Booth Rental Payment Report</h4>

        <div class="grid-lg-4 clearfix">
            <div class="card">
                <div class="card-header bg-green-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Total Revenue</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-yellow-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Pending Payments</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($pendingPayments, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-red-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Overdue Payments</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($overduePayments, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-blue-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Processing Fees</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($processingFees, 2) }}</h3>
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 16px; border-radius: 0.5rem; border: 1px solid #ddd;">
            <div>
                <table class="table" style="width: 100%;">
                    <thead style="background-color: #f7fafc;">
                        <tr>
                            <th>#</th>
                            <th>Tenant</th>
                            <th>Store</th>
                            <th>Amount</th>
                            <th>Processing Fee</th>
                            <th>Last Payment Date</th>
                            <th>Next Payment Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boothPayments as $index => $rental)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rental->boothRental->user->name ?? 'N/A' }}</td>
                            <td>{{ $rental->boothRental->store->store_name ?? 'N/A' }}</td>
                            <td>₦{{ number_format($rental->amount, 2) }}</td>
                            <td>₦{{ number_format($rental->processing_fee, 2) }}</td>
                            <td>{{ optional($rental->last_payment_date)->format('M d, Y') }}</td>
                            <td>{{ optional($rental->next_payment_date)->format('M d, Y') }}</td>
                            <td>
                                <span class="status-{{ strtolower($rental->payment_status) }}">
                                    {{ ucfirst($rental->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>