@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold"> Orders</h4>
                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <form class="mb-3 row" method="post" action="{{ route('orders') }}">
                                            @include('flash')
                                            @csrf
                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Delivery Type</label>
                                                <select name="delivery_type" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="Delivery"
                                                        {{ request('delivery_type') == 'Delivery' ? 'selected' : '' }}>
                                                        Delivery</option>
                                                    <option value="Pickup"
                                                        {{ request('delivery_type') == 'Pickup' ? 'selected' : '' }}>
                                                        Pickup</option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Order Status</label>
                                                <select name="status" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    @foreach (cc('transaction.indexed_status') as $key => $value)
                                                        <option value="{{ $key }}"
                                                            {{ !is_null(request('status')) && request('status') == $key ? 'selected' : '' }}>
                                                            {{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Order Date</label>
                                                <input type="text" id="daterange" name="date"
                                                    value="{{ request('date') }}" class="form-control"
                                                    placeholder="Date Range" data-toggle="flatpickr"
                                                    data-options='{"mode": "range"}'>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <button class="btn btn-primary mr-2" type="submit" name="action"
                                                    value="filter">Filter</button>
                                                <a href="{{ route('orders') }}" class="btn btn-danger mr-2">Clear Filter</a>
                                                <button class="btn btn-success" type="submit" name="action"
                                                    value="export"> <i class="fa fa-file-excel-o"></i> Export</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <table class="display table table-striped table-bordered dataTable "
                                            id="zero_configuration_table" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">ID</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Order Ref</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Delivery Type</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Merchant</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Order Amount(GBP)</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Shipping Fee(GBP)</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Total Amount(GBP)</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Payment Status</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Date</th>
                                                    <th class="sorting text-center" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 20px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($collection as $item)
                                                    <tr role="row">
                                                        <td>#{{ $item->id }}</td>
                                                        <td>{{ $item->orderRef }}</td>
                                                        <td>{{ $item->delivery_type }}</td>
                                                        <td>{{ $item->buyer }}</td>
                                                        <td class="text-capitalize">{{ $item->merchant }}</td>
                                                        <td class="font-weight-bold">
                                                            {{ number_format($item->totalprice, 2) }}</td>
                                                        <td class="font-weight-bold">
                                                            {{ number_format($item->delivery_fee, 2) }}</td>
                                                        <td class="font-weight-bold">{{ number_format($item->total, 2) }}
                                                        </td>
                                                        <td>
                                                            @if ($item->payment_status == 1)
                                                                <span
                                                                    class="badge badge-success pl-3 pr-3 pt-2 pb-2">Paid</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Unpaid</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-capitalize">{{ $item->created_at }}</td>
                                                        <td><a href="{{ route('order.details', ['id' => $item->id]) }}"
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
