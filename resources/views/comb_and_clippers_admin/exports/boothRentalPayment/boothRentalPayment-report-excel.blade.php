<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Tenant</th>
            <th>Store</th>
            <th>Amount</th>
            <th>Processing Fee</th>
            <th>Last Payment Date</th>
            <th>Next Payment Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($boothPayments as $index => $rental)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $rental->boothRental->user->name ?? 'N/A' }}</td>
            <td>{{ $rental->boothRental->store->store_name ?? 'N/A' }}</td>
            <td>₦{{ number_format($rental->amount, 2) }}</td>
            <td>₦{{ number_format($rental->processing_fee, 2) }}</td>
            <td>{{ optional($rental->last_payment_date)->format('M d, Y') }}</td>
            <td>{{ optional($rental->next_payment_date)->format('M d, Y') }}</td>
            <td>{{ ucfirst($rental->payment_status) }}</td>
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
            <td>Total Revenue</td>
            <td>₦{{ number_format($totalRevenue, 2) }}</td>
        </tr>
        <tr>
            <td>Pending Payments</td>
            <td>₦{{ number_format($pendingPayments, 2) }}</td>
        </tr>
        <tr>
            <td>Overdue Payments</td>
            <td>₦{{ number_format($overduePayments, 2) }}</td>
        </tr>
        <tr>
            <td>Total Processing Fees</td>
            <td>₦{{ number_format($processingFees, 2) }}</td>
        </tr>
    </tbody>
</table>