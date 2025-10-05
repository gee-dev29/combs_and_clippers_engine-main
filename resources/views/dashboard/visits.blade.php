@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h2 class="card-title mb-5 font-weight-bold text-capitalize">Store Visits</h2>
                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <form class="mb-3 row" method="post"
                                            action="{{ route('store.visits') }}">
                                            @include('dashboard.flash')
                                            @csrf
                                            <div class="form-group mb-4 col-md-4">
                                                <label class="mb-0">Date</label>
                                                <input type="text" id="daterange" name="date"
                                                    value="{{ request('date') }}" class="form-control"
                                                    placeholder="Date Range" data-toggle="flatpickr"
                                                    data-options='{"mode": "range"}'>
                                            </div>

                                            <div class="form-group mb-4 col-md-4">
                                                <br>
                                                <button class="btn btn-primary mr-2" type="submit" name="action"
                                                    value="filter">Filter</button>
                                                <a href="{{ route('store.visits') }}"
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
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 29px;">Date</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Store Name</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Total Visits</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($collection as $item)
                                                    <tr role="row">
                                                        <td>{{ $item->Date }}</td>
                                                        <td class="text-capitalize">{{ $item->store_name }}</td>
                                                        <td class="text-capitalize">{{ $item->total_visits }}</td>
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
