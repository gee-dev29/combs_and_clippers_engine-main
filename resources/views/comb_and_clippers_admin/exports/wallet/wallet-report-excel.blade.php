<table>
    <thead>
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