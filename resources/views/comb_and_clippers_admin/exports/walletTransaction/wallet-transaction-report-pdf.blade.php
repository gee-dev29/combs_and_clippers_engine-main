<!DOCTYPE html>
<html>

<head>
    <title>Wallet Transactions Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 8pt;
            /* Further reduce body font size */
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
            padding: 3px;
            /* Reduce padding */
            text-align: left;
            font-size: 7pt;
            /* Further reduce cell font size */
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
        <h4 style="font-size: 1.1rem; font-weight: bold; margin-bottom: 12px;">Wallet Transactions Report</h4>

        <div
            style="background-color: #fff; padding: 10px; border-radius: 0.5rem; border: 1px solid #ddd; overflow: auto;">
            <table class="table" style="width: 100%;">
                <thead style="background-color: #f7fafc;">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Wallet Number</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>From Acct</th>
                        <th>To Acct</th>
                        <th>Status</th>
                        <th>Currency</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($walletTransactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ Str::limit($transaction->wallet->user->name ?? 'N/A', 15) }}</td> {{-- Limit user name
                        length --}}
                        <td>{{ Str::limit($transaction->wallet->wallet_number ?? 'N/A', 15) }}</td> {{-- Limit wallet
                        number --}}
                        <td>{{ Str::limit(ucfirst($transaction->type ?? 'N/A'), 10) }}</td> {{-- Limit type --}}
                        <td style="text-align: right;">{{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ Str::limit($transaction->from_account_no ?? 'N/A', 15) }}</td> {{-- Limit from acct --}}
                        <td>{{ Str::limit($transaction->to_account_no ?? 'N/A', 15) }}</td> {{-- Limit to acct --}}
                        <td>{{ Str::limit($transaction->status ?? 'N/A', 8) }}</td> {{-- Limit status --}}
                        <td>{{ Str::limit($transaction->currency ?? 'N/A', 5) }}</td> {{-- Limit currency --}}
                        <td>{{ Str::limit($transaction->transaction_ref ?? 'N/A', 15) }}</td> {{-- Limit reference --}}
                        <td>{{ Str::limit($transaction->narration ?? 'N/A', 20) }}</td> {{-- Limit description --}}
                        <td>{{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i:s') : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>