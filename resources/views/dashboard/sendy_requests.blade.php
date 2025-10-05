@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">
                            Sendy Requests</h4>
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
                                                        colspan="1" style="width: 46px;">Fulfilment ID</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Request Type</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Request Status</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer Name</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer Phone</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="3"
                                                        style="width: 46px;">Buyer Address</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="3"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Products</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Created</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 36px;">Updated</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($orders['fulfilment_requests'] as $order)
                                                    <tr role="row">
                                                        <td>{{ $order['fulfilment_request_id'] }}</td>
                                                        <td>{{ $order['fulfilment_request_type'] }}</td>
                                                        <td>{{ $order['fulfilment_request_status'] }}</td>
                                                        <td>{{ $order['destination']['name'] }}</td>
                                                        <td>{{ $order['destination']['phone_number'] }}</td>
                                                        <td colspan="3">{{ $order['destination']['house_location'] }}
                                                        </td>
                                                        <td colspan="3">
                                                            @foreach ($order['products'] as $product)
                                                                <img src="{{ $product['product_variant_image_link'] }}"
                                                                        alt="product_image" width="50" height="50">
                                                                        {{ $product['product_name'] }} x {{ $product['quantity'] }}
                                                            @endforeach
                                                        </td>
                                                        {{-- <td>{{ Carbon\Carbon::createFromTimestamp($order['created_date'])->toDateTimeString() }}</td>
                                                        <td>{{ Carbon\Carbon::createFromTimestamp($order['updated_date'])->toDateTimeString() }}</td> --}}
                                                        <td>{{ Carbon\Carbon::parse($order['created_date'])->format('d M Y') }}</td>
                                                        <td>{{ Carbon\Carbon::parse($order['updated_date'])->format('d M Y') }}</td>
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
