<table>
    <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Wallet Number</th>
            <th>Transaction Type</th>
            <th>Amount</th>
            <th>From Account</th>
            <th>To Account</th>
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
            <td>{{ $transaction->wallet->user->name ?? 'N/A' }}</td>
            <td>{{ $transaction->wallet->wallet_number ?? 'N/A' }}</td>
            <td>{{ ucfirst($transaction->type ?? 'N/A') }}</td>
            <td>{{ number_format($transaction->amount, 2) }}</td>
            <td>{{ $transaction->from_account_no ?? 'N/A' }}</td>
            <td>{{ $transaction->to_account_no ?? 'N/A' }}</td>
            <td>{{ $transaction->status ?? 'N/A' }}</td>
            <td>{{ $transaction->currency ?? 'N/A' }}</td>
            <td>{{ $transaction->transaction_ref ?? 'N/A' }}</td>
            <td>{{ $transaction->narration ?? 'N/A' }}</td>
            <td>{{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>