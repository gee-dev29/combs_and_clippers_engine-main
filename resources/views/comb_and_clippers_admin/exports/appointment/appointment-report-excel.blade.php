<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Merchant</th>
            <th>Store</th>
            <th>Date &amp; Time</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
        @if($appointments->count() > 0)
        @foreach($appointments as $index => $appointment)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $appointment->customer->name ?? 'N/A' }}</td>
            <td>{{ $appointment->serviceProvider->name ?? 'N/A' }}</td>
            <td>{{ $appointment->store->store_name ?? 'N/A' }}</td>
            <td>{{ $appointment->date }} - {{ $appointment->time }}</td>
            <td>₦{{ number_format($appointment->total_amount, 2) }}</td>
            <td>{{ ucfirst($appointment->status) }}</td>
            <td>{{ $appointment->payment_status == 1 ? 'Paid' : 'Unpaid' }}</td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="8">No appointments found</td>
        </tr>
        @endif
    </tbody>
</table>

<table style="margin-top: 20px;">
    <thead>
        <tr>
            <th>Summary</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Appointments</td>
            <td>{{ $totalAppointments }}</td>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td>₦{{ number_format($totalAmount, 2) }}</td>
        </tr>
        <tr>
            <td>Total Tips</td>
            <td>₦{{ number_format($totalTips, 2) }}</td>
        </tr>
        <tr>
            <td>Total Processing Fees</td>
            <td>₦{{ number_format($totalProcessingFees, 2) }}</td>
        </tr>
    </tbody>
</table>