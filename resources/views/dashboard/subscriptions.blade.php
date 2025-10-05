@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Subscriptions</h4>
                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <form class="mb-3 row" method="post" action="{{ route('subscriptions') }}">
                                            @include('flash')
                                            @csrf
                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Subscription Plan</label>
                                                <select name="sub_plan" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    @foreach (\App\Models\Subscription::all() as $sub)
                                                        <option value="{{ $sub->id }}"
                                                            {{ !is_null(request('sub_plan')) && request('sub_plan') == $sub->id ? 'selected' : '' }}>
                                                            {{ $sub->plan }} -
                                                            {{ $sub->invoice_period . ' ' . $sub->invoice_interval }}
                                                            {{ $sub->id == \App\Models\Subscription::TRIAL ? '(Free Trial)' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Subscription Status</label>
                                                <select name="status" class="custom-select">
                                                    <option value="">--Any--</option>
                                                    @foreach (\App\Models\Subscription::STATUS as $key => $value)
                                                        <option value="{{ $key }}"
                                                            {{ !is_null(request('status')) && request('status') == $key ? 'selected' : '' }}>
                                                            {{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Subscription Date</label>
                                                <input type="text" id="daterange" name="date"
                                                    value="{{ request('date') }}" class="form-control"
                                                    placeholder="Date Range" data-toggle="flatpickr"
                                                    data-options='{"mode": "range"}'>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <button class="btn btn-primary mr-2" type="submit" name="action"
                                                    value="filter">Filter</button>
                                                <a href="{{ route('subscriptions') }}" class="btn btn-danger mr-2">Clear
                                                    Filter</a>
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
                                                    <th rowspan="1" colspan="1">Merchant ID</th>
                                                    <th rowspan="1" colspan="1">Merchant Name</th>
                                                    <th rowspan="1" colspan="1">Merchant Email</th>
                                                    <th rowspan="1" colspan="1">Merchant Phone</th>
                                                    <th rowspan="1" colspan="1">Plan</th>
                                                    <th rowspan="1" colspan="1">Price</th>
                                                    <th rowspan="1" colspan="1">Duration</th>
                                                    <th rowspan="1" colspan="1">Status</th>
                                                    <th rowspan="1" colspan="1">Active</th>
                                                    <th rowspan="1" colspan="1">Auto Renew</th>
                                                    <th rowspan="1" colspan="1">Subscription Date</th>
                                                    <th rowspan="1" colspan="1">Expiry Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($subs as $sub)
                                                    <tr role="row">
                                                        <td>#{{ $sub->id }}</td>
                                                        <td>{{ $sub->name }}</td>
                                                        <td class="text-lowercase">{{ $sub->email }}</td>
                                                        <td>{{ $sub->phone }}</td>
                                                        <td class="text-lowercase">{{ $sub->plan }}</td>
                                                        <td>{{ $sub->price }}</td>
                                                        <td>{{ $sub->duration }}</td>
                                                        <td class="text-lowercase">{{ $sub->status }}</td>
                                                        <td>
                                                            @if ($sub->active == 0)
                                                                <span
                                                                    class="badge badge-danger pr-3 pl-3 pt-2 pb-2">False</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-success pr-3 pl-3 pt-2 pb-2">True</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($sub->auto_renew == 0)
                                                                <span
                                                                    class="badge badge-danger pr-3 pl-3 pt-2 pb-2">False</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-success pr-3 pl-3 pt-2 pb-2">True</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ Carbon\Carbon::parse($sub->created_at)->format('d M Y') }}
                                                        </td>
                                                        <td>{{ Carbon\Carbon::parse($sub->expires_at)->format('d M Y') }}
                                                        </td>
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
