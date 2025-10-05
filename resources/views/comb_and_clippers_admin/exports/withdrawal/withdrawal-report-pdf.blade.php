<!DOCTYPE html>
<html>

<head>
    <title>Withdrawal Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 8pt;
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
            font-size: 7pt;
        }

        .table th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .status-successful {
            background-color: #28a745 !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }

        .status-pending {
            background-color: #ffc107 !important;
            color: black !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }

        .status-failed {
            background-color: #dc3545 !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }

        .status-processing {
            background-color: #007bff !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.1rem; font-weight: bold; margin-bottom: 12px;">Withdrawal Report</h4>

        <div
            style="background-color: #fff; padding: 10px; border-radius: 0.5rem; border: 1px solid #ddd; overflow: auto;">
            <table class="table" style="width: 100%;">
                <thead style="background-color: #f7fafc;">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Wallet No</th>
                        <th>Req. Amt</th>
                        <th>Fee</th>
                        <th>Amt</th>
                        <th>Acct Name</th>
                        <th>Acct No</th>
                        <th>Bank</th>
                        <th>Ref</th>
                        <th>Status</th>
                        <th>Narration</th>
                        <th>Internal</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawals as $index => $withdrawal)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ Str::limit($withdrawal->user->name ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($withdrawal->wallet->wallet_number ?? 'N/A', 15) }}</td>
                        <td style="text-align: right;">{{ number_format($withdrawal->amount_requested, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($withdrawal->fee, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($withdrawal->amount, 2) }}</td>
                        <td>{{ Str::limit($withdrawal->account_name ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($withdrawal->account_number ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($withdrawal->bank_name ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($withdrawal->transferRef ?? 'N/A', 15) }}</td>
                        <td>
                            @if ($withdrawal->withdrawal_status == \App\Models\Withdrawal::SUCCESSFUL)
                            <span class="status-successful">Successful</span>
                            @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::PENDING)
                            <span class="status-pending">Pending</span>
                            @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::FAILED)
                            <span class="status-failed">Failed</span>
                            @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::PROCESSING)
                            <span class="status-processing">Processing</span>
                            @else
                            {{ 'N/A' }}
                            @endif
                        </td>
                        <td>{{ Str::limit($withdrawal->narration ?? 'N/A', 20) }}</td>
                        <td>{{ $withdrawal->is_internal ? 'Yes' : 'No' }}</td>
                        <td>{{ $withdrawal->created_at ? $withdrawal->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>