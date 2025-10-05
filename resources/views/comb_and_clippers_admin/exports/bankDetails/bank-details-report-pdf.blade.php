<!DOCTYPE html>
<html>

<head>
    <title>User Bank Details Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
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

        .status-yes {
            background-color: #38a169;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .status-no {
            background-color: #e53e3e;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 16px;">User Bank Details Report</h4>

        <div style="background-color: #fff; padding: 16px; border-radius: 0.5rem; border: 1px solid #ddd;">
            <div style="auto;">
                <table class="table" style="width: 100%;">
                    <thead style="background-color: #f7fafc;">
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Account Type</th>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>Routing Number</th>
                            <th>Bank Code</th>
                            <th>Email Verified</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userBankDetails as $index => $bankDetail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $bankDetail->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($bankDetail->user->account_type ?? 'N/A') }}</td>
                            <td>{{ $bankDetail->bank_name ?? 'N/A' }}</td>
                            <td>{{ $bankDetail->account_number ?? 'N/A' }}</td>
                            <td>{{ $bankDetail->routing_number ?? 'N/A' }}</td>
                            <td>{{ $bankDetail->bank_code ?? 'N/A' }}</td>
                            <td>
                                @if($bankDetail->user->email_verified)
                                <span class="status-yes">Yes</span>
                                @else
                                <span class="status-no">No</span>
                                @endif
                            </td>
                            <td>{{ $bankDetail->created_at ? $bankDetail->created_at->format('M d, Y H:i:s') : 'N/A' }}
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