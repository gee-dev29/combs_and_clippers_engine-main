@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">
                            Shipments::{{ request()->segment(count(request()->segments())) }}</h4>
                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="display table table-striped table-bordered dataTable "
                                            id="zero_configuration_table" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Order Ref</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Fulfilment ID</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer Email</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer Phone</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Merchant</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Merchant Email</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Merchant Phone</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Store Name</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Delivery Fee(GBP)</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Payment Status</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Delivery Type</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 36px;">Delivery Status</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Created</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 36px;">Last Updated</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 20px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($data as $item)
                                                    <tr role="row">
                                                        <td>{{ $item->orderRef }}</td>
                                                        <td>{{ $item->fulfilment_id }}</td>
                                                        <td>{{ $item->buyer }}</td>
                                                        <td>{{ $item->buyer_email }}</td>
                                                        <td>{{ $item->buyer_phone }}</td>
                                                        <td class="text-capitalize">{{ $item->merchant }}</td>
                                                        <td>{{ $item->merchant_email }}</td>
                                                        <td>{{ $item->merchant_phone }}</td>
                                                        <td>{{ $item->store_name }}</td>
                                                        <td class="font-weight-bold">
                                                            {{ number_format($item->shipping, 2) }}</td>
                                                        <td>{{ $item->payment_status == 1 ? 'Paid' : 'Not Paid' }}</td>
                                                        <td class="text-capitalize">{{ $item->delivery_type }}</td>
                                                        <td>{{ $item->delivery_status }}</td>
                                                        <td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                                                        </td>
                                                        <td>{{ Carbon\Carbon::parse($item->updated_at)->format('d M Y') }}
                                                        </td>
                                                        <td><a href="{{ route('shipment.details', ['id' => $item->id]) }}"
                                                                class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
