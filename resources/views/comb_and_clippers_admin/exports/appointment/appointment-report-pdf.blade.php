<!DOCTYPE html>
<html>

<head>
    <title>Appointment Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        .summary-table {
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px;
            text-align: left;
        }

        .summary-table td:first-child {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Appointment Report</h2>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Merchant</th>
                <th>Store</th>
                <th>Date &amp; Time</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $index => $appointment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $appointment->customer->name ?? 'N/A' }}</td>
                <td>{{ $appointment->serviceProvider->name ?? 'N/A' }}</td>
                <td>{{ $appointment->store->store_name ?? 'N/A' }}</td>
                <td>{{ $appointment->date }} - {{ $appointment->time }}</td>
                <td>₦{{ number_format($appointment->total_amount, 2) }}</td>
                <td>{{ ucfirst($appointment->status) }}</td>
                <td>{{ $appointment->payment_status == 1 ? 'Paid' : 'Unpaid' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">No appointments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Summary</h3>
    <table class="summary-table">
        <tr>
            <td>Total Appointments:</td>
            <td>{{ number_format($totalAppointments) }}</td>
        </tr>
        <tr>
            <td>Total Amount:</td>
            <td>₦{{ number_format($totalAmount, 2) }}</td>
        </tr>
        <tr>
            <td>Total Tips:</td>
            <td>₦{{ number_format($totalTips, 2) }}</td>
        </tr>
        <tr>
            <td>Total Processing Fees:</td>
            <td>₦{{ number_format($totalProcessingFees, 2) }}</td>
        </tr>
    </table>
</body>

</html>