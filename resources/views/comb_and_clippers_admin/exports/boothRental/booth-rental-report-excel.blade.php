<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Store</th>
            <th>Renter</th>
            <th>Payment Timeline</th>
            <th>Amount</th>
            <th>Service Type</th>
            <th>Payment Days</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @forelse($boothRentals as $rental)
        <tr>
            <td>{{ $rental->id }}</td>
            <td>{{ $rental->store->store_name ?? 'N/A' }}</td>
            <td>{{ $rental->user->name ?? 'N/A' }}</td>
            <td>{{ ucfirst($rental->payment_timeline) }}</td>
            <td>₦{{ number_format($rental->amount, 2) }}</td>
            <td>{{ $rental->serviceType->name ?? 'N/A' }}</td>
            <td>{{ $rental->payment_days }}</td>
            <td>{{ $rental->created_at->format('M d, Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8">No booth rentals found based on the applied filters.</td>
        </tr>
        @endforelse
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
            <td>Total Booth Rentals</td>
            <td>{{ number_format($totalRentals) }}</td>
        </tr>
        <tr>
            <td>Total Rental Amount</td>
            <td>₦{{ number_format($totalAmount, 2) }}</td>
        </tr>
    </tbody>
</table>