<!DOCTYPE html>
<html>

<head>
    <title>Internal Transaction Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .grid-lg-3 {
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 0.5rem;
            margin-bottom: 16px;
            width: 32%;
            /* Roughly one-third width with spacing */
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

        .bg-blue-100 {
            background-color: #4299e1;
        }

        .bg-green-100 {
            background-color: #38a169;
        }

        .bg-yellow-100 {
            background-color: #f6ad55;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
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

        .status-successful {
            background-color: #38a169;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .status-failed {
            background-color: #e53e3e;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .status-pending {
            background-color: #f6ad55;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 16px;">Internal Transaction Report</h4>

        <div class="grid-lg-3 clearfix">
            <div class="card">
                <div class="card-header bg-blue-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Total Transactions</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">{{ number_format($totalTransactions) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-green-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Total Amount</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-yellow-100">
                    <h5 class="card-title"><i style="font-size: 1.5rem;"></i> Average Transaction Amount</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-2xl">₦{{ number_format($averageAmount, 2) }}</h3>
                </div>
            </div>
        </div>

        <div style="background-color: #fff; padding: 16px; border-radius: 0.5rem; border: 1px solid #ddd;">
            <div style=" auto;">
                <table class="table" style="width: 100%;">
                    <thead style="background-color: #f7fafc;">
                        <tr>
                            <th>ID</th>
                            <th>Merchant</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Transaction Ref</th>
                            <th>Narration</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($internalTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->merchant->name ?? 'N/A' }}</td>
                            <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td>{{ $transaction->transaction_ref }}</td>
                            <td>{{ $transaction->narration }}</td>
                            <td>{{ strtoupper($transaction->currency) }}</td>
                            <td>₦{{ number_format($transaction->amount, 2) }}</td>
                            <td>
                                <span class="status-{{ strtolower($transaction->payment_status) }}">
                                    {{ ucfirst($transaction->payment_status) }}
                                </span>
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>