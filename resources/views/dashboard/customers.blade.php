@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="row">
                    <div class="col-md-4 mt-2">
                        Customer type
                        <select onchange="if (this.value) window.location.href=this.value" class="custom-select">
                            <option value="{{ route('customers', ['type' => 'Buyer']) }}"
                                {{ $type == 'Buyer' ? 'selected' : '' }}>Buyers</option>
                            <option value="{{ route('customers', ['type' => 'Merchant']) }}"
                                {{ $type == 'Merchant' ? 'selected' : '' }}>Merchants</option>
                        </select>
                    </div>

                    <div class="col-md-6 mt-2">
                        <a href="{{ route('customer.add.get') }}" class="btn btn-primary">Register New Merchant</a>
                        <a href="{{ route('customer.bulk.form') }}" class="btn btn-info">Bulk Upload Merchant</a>
                    </div>
                </div>
                <div class="card text-left">
                    <div class="card-body">
                        <h2 class="card-title mb-5 font-weight-bold text-capitalize">Customers:
                            {{ request()->segment(count(request()->segments())) }}</h2>


                        <pre>
                
            </pre>

                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <form class="mb-3 row" method="post"
                                            action="{{ route('customers', ['type' => $type]) }}">
                                            @include('flash')
                                            @csrf
                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Has Product</label>
                                                <select name="has_product" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="1"
                                                        {{ request('has_product') == '1' ? 'selected' : '' }}>Exists
                                                    </option>
                                                    <option value="0"
                                                        {{ request('has_product') == '0' ? 'selected' : '' }}>No Product
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Subscription Status</label>
                                                <select name="has_sub" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="1"
                                                        {{ request('has_sub') == '1' ? 'selected' : '' }}>Paid Subscription</option>
                                                    <option value="2"
                                                        {{ request('has_sub') == '2' ? 'selected' : '' }}>Free Trial</option>
                                                    <option value="0"
                                                        {{ request('has_sub') == '0' ? 'selected' : '' }}>No Active Subscription
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Store Approval</label>
                                                <select name="store_status" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="1"
                                                        {{ request('store_status') == '1' ? 'selected' : '' }}>Approved</option>
                                                    <option value="0"
                                                        {{ request('store_status') == '0' ? 'selected' : '' }}>Disapproved
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Has Pickup Address</label>
                                                <select name="has_pickup_address" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="1"
                                                        {{ request('has_pickup_address') == '1' ? 'selected' : '' }}>Exists
                                                    </option>
                                                    <option value="0"
                                                        {{ request('has_pickup_address') == '0' ? 'selected' : '' }}>No Pickup Address
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Has Store Address</label>
                                                <select name="has_store_address" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    <option value="1"
                                                        {{ request('has_store_address') == '1' ? 'selected' : '' }}>Exists
                                                    </option>
                                                    <option value="0"
                                                        {{ request('has_store_address') == '0' ? 'selected' : '' }}>No Store Address
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Registration Date</label>
                                                <input type="text" id="daterange" name="date"
                                                    value="{{ request('date') }}" class="form-control"
                                                    placeholder="Date Range" data-toggle="flatpickr"
                                                    data-options='{"mode": "range"}'>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <br>
                                                <button class="btn btn-primary mr-2" type="submit" name="action"
                                                    value="filter">Filter</button>
                                                <a href="{{ route('customers', ['type' => $type]) }}"
                                                    class="btn btn-danger mr-2">Clear Filter</a>
                                                <button class="btn btn-success" type="submit" name="action"
                                                    value="export"> <i class="fa fa-file-excel-o"></i> Export</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-sm-12">
                                        <table class="display table table-striped table-bordered dataTable"
                                            id="zero_configuration_table" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">ID</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Account type</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Name</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Store Name</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Phone</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 29px;">Email address</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 29px;">Registration Date</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" aria-label="Age: activate to sort column ascending"
                                                        style="width: 26px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($collection as $item)
                                                    <tr role="row">
                                                        <td>#{{ $item->id }}</td>
                                                        <td class="text-capitalize">{{ $item->account_type }}</td>
                                                        <td class="text-capitalize">{{ $item->name }}</td>
                                                        <td class="text-capitalize">{{ $item->store_name }}</td>
                                                        <td class="text-capitalize"><a title="click to call"
                                                                href="tel:{{ $item->phone }}">{{ $item->phone }}</a>
                                                        </td>
                                                        <td class="text-lowercase">{{ $item->email }}</td>
                                                        <td class="text-lowercase">{{ $item->created_at }}</td>

                                                        <td><a href="{{ route('cdetails', ['id' => $item->id]) }}"
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
