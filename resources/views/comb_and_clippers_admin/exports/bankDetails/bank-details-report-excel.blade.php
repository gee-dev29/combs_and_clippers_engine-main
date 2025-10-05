<table>
    <thead>
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
            <td>{{ $bankDetail->user->email_verified ? 'Yes' : 'No' }}</td>
            <td>{{ $bankDetail->created_at ? $bankDetail->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>