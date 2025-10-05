<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Account Status</th>
            <th>Account Type</th>
            <th>Phone</th>
            <th>Bank</th>
            <th>Account Number</th>
            <th>Account Name</th>
            <th>Specialization</th>
            <th>Has Store</th>
            <th>Email Verified At</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $index => $user)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->accountstatus) }}</td>
            <td>{{ ucfirst($user->account_type) }}</td>
            <td>{{ $user->phone ?? 'N/A' }}</td>
            <td>{{ $user->bank_details->bank_name ?? $user->bank ?? 'N/A' }}</td>
            <td>{{ $user->bank_details->account_number ?? $user->accountno ?? 'N/A' }}</td>
            <td>{{ $user->accountname ?? 'N/A' }}</td>
            <td>{{ $user->specialization ?? 'N/A' }}</td>
            <td>{{ $user->store ? 'Yes' : 'No' }}</td>
            <td>{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i:s') : 'N/A' }}</td>
            <td>{{ $user->created_at ? $user->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>