<!DOCTYPE html>
<html>

<head>
    <title>User Report</title>
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

        .status-active {
            background-color: #28a745 !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }

        .status-inactive {
            background-color: #6c757d !important;
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

        .status-suspended {
            background-color: #dc3545 !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.1rem; font-weight: bold; margin-bottom: 12px;">User Report</h4>

        <div
            style="background-color: #fff; padding: 10px; border-radius: 0.5rem; border: 1px solid #ddd; overflow: auto;">
            <table class="table" style="width: 100%;">
                <thead style="background-color: #f7fafc;">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Bank</th>
                        <th>Acct No</th>
                        <th>Acct Name</th>
                        <th>Specialization</th>
                        <th>Has Store</th>
                        <th>Verified At</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ Str::limit($user->name, 20) }}</td>
                        <td>{{ Str::limit($user->email, 25) }}</td>
                        <td>
                            @if ($user->accountstatus == 'active')
                            <span class="status-active">Active</span>
                            @elseif ($user->accountstatus == 'inactive')
                            <span class="status-inactive">Inactive</span>
                            @elseif ($user->accountstatus == 'pending')
                            <span class="status-pending">Pending</span>
                            @elseif ($user->accountstatus == 'suspended')
                            <span class="status-suspended">Suspended</span>
                            @else
                            {{ ucfirst($user->accountstatus) }}
                            @endif
                        </td>
                        <td>{{ Str::limit(ucfirst($user->account_type), 10) }}</td>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                        <td>{{ Str::limit($user->bank_details->bank_name ?? $user->bank ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($user->bank_details->account_number ?? $user->accountno ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($user->accountname ?? 'N/A', 15) }}</td>
                        <td>{{ Str::limit($user->specialization ?? 'N/A', 15) }}</td>
                        <td style="text-align: center;">{{ $user->store ? 'Yes' : 'No' }}</td>
                        <td>{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $user->created_at ? $user->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>