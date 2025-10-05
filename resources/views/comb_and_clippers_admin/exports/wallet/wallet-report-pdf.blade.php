<!DOCTYPE html>
<html>

<head>
    <title>Wallet Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 9pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 8pt;
        }

        .table th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 16px;">Wallet Report</h4>

        <div style="background-color: #fff; padding: 16px; border-radius: 0.5rem; border: 1px solid #ddd;">
            <div style="overflow: auto;">
                <table class="table" style="width: 100%;">
                    <thead style="background-color: #f7fafc;">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Wallet Number</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Unclaimed Amount</th>
                            <th>Account Number</th>
                            <th>Bank Name</th>
                            <th>Bank Code</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wallets as $index => $wallet)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $wallet->user->name ?? 'N/A' }}</td>
                            <td>{{ $wallet->wallet_number ?? 'N/A' }}</td>
                            <td>{{ $wallet->currency ?? 'N/A' }}</td>
                            <td>{{ number_format($wallet->amount, 2) }}</td>
                            <td>{{ number_format($wallet->unclaimed_amount, 2) }}</td>
                            <td>{{ $wallet->account_number ?? 'N/A' }}</td>
                            <td>{{ $wallet->bank->bank ?? 'N/A' }}</td>
                            <td>{{ $wallet->bank_code ?? 'N/A' }}</td>
                            <td>{{ $wallet->created_at ? $wallet->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>