<table>
    <thead>
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
            <td>{{ ucfirst($transaction->payment_status) }}</td>
            <td>{{ $transaction->created_at->format('M d, Y H:i:s') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>

<table>
    <thead>
        <tr>
            <th>Summary</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Transactions</td>
            <td>{{ number_format($totalTransactions) }}</td>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>₦{{ number_format($totalAmount, 2) }}</td>
        </tr>
        <tr>
            <td>Average Amount</td>
            <td>₦{{ number_format($averageAmount, 2) }}</td>
        </tr>
    </tbody>
</table>