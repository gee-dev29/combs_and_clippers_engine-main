<!DOCTYPE html>
<html>

<head>
    <title>Store Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 8pt;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 7pt;
        }

        .table th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .status-yes {
            background-color: #28a745 !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }

        .status-no {
            background-color: #6c757d !important;
            color: white !important;
            padding: 0.2rem 0.4rem;
            border-radius: 0.2rem;
            font-size: 7pt;
        }
    </style>
</head>

<body>
    <div class="container">
        <h4 style="font-size: 1.1rem; font-weight: bold; margin-bottom: 12px;">Store Report</h4>

        <div
            style="background-color: #fff; padding: 10px; border-radius: 0.5rem; border: 1px solid #ddd; overflow: auto;">
            <table class="table" style="width: 100%;">
                <thead style="background-color: #f7fafc;">
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
                        <td>{{ Str::limit($store->store_name, 30) }}</td>
                        <td>{{ Str::limit($store->owner->name ?? 'N/A', 25) }}</td>
                        <td>{{ Str::limit($store->category->name ?? 'N/A', 20) }}</td>
                        <td style="text-align: center;">{{ $store->renters_count }}</td>
                        <td style="text-align: center;">{{ $store->booth_rent_count }}</td>
                        <td style="text-align: center;">{{ $store->bookings_count }}</td>
                        <td style="text-align: center;">
                            @if ($store->featured)
                            <span class="status-yes">Yes</span>
                            @else
                            <span class="status-no">No</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($store->approved)
                            <span class="status-yes">Yes</span>
                            @else
                            <span class="status-no">No</span>
                            @endif
                        </td>
                        <td>{{ $store->created_at ? $store->created_at->format('M d, Y H:i:s') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>