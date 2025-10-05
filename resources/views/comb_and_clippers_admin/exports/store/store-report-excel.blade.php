<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Store Name</th>
            <th>Owner Name</th>
            <th>Category</th>
            <th>User Count</th>
            <th>Booth Count</th>
            <th>Appointment Count</th>
            <th>Featured</th>
            <th>Approved</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stores as $index => $store)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $store->store_name }}</td>
            <td>{{ $store->owner->name ?? 'N/A' }}</td>
            <td>{{ $store->category->name ?? 'N/A' }}</td>
            <td>{{ $store->renters_count }}</td>
            <td>{{ $store->booth_rent_count }}</td>
            <td>{{ $store->bookings_count }}</td>
            <td>{{ $store->featured ? 'Yes' : 'No' }}</td>
            <td>{{ $store->approved ? 'Yes' : 'No' }}</td>
            <td>{{ $store->created_at ? $store->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>