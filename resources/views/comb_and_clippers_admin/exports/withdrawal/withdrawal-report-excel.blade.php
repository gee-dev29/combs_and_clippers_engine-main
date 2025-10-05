<table>
    <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Wallet Number</th>
            <th>Amount Requested</th>
            <th>Fee</th>
            <th>Amount</th>
            <th>Account Name</th>
            <th>Account Number</th>
            <th>Bank Name</th>
            <th>Transfer Ref</th>
            <th>Status</th>
            <th>Narration</th>
            <th>Is Internal</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($withdrawals as $index => $withdrawal)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $withdrawal->user->name ?? 'N/A' }}</td>
            <td>{{ $withdrawal->wallet->wallet_number ?? 'N/A' }}</td>
            <td>{{ number_format($withdrawal->amount_requested, 2) }}</td>
            <td>{{ number_format($withdrawal->fee, 2) }}</td>
            <td>{{ number_format($withdrawal->amount, 2) }}</td>
            <td>{{ $withdrawal->account_name ?? 'N/A' }}</td>
            <td>{{ $withdrawal->account_number ?? 'N/A' }}</td>
            <td>{{ $withdrawal->bank_name ?? 'N/A' }}</td>
            <td>{{ $withdrawal->transferRef ?? 'N/A' }}</td>
            <td>
                @if ($withdrawal->withdrawal_status == \App\Models\Withdrawal::SUCCESSFUL)
                Successful
                @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::PENDING)
                Pending
                @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::FAILED)
                Failed
                @elseif ($withdrawal->withdrawal_status == \App\Models\Withdrawal::PROCESSING)
                Processing
                @else
                N/A
                @endif
            </td>
            <td>{{ $withdrawal->narration ?? 'N/A' }}</td>
            <td>{{ $withdrawal->is_internal ? 'Yes' : 'No' }}</td>
            <td>{{ $withdrawal->created_at ? $withdrawal->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>