@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold"> Product Requests</h4>
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
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">ID</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Product Name</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Product Category</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer Email</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Product Link</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Additional Info</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1"
                                                        aria-label="Office: activate to sort column ascending"
                                                        style="width: 39px;">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($productRequests as $productRequest)
                                                    <tr role="row">
                                                        <td>#{{ $productRequest->id }}</td>
                                                        <td>{{ $productRequest->product_name }}</td>
                                                        <td>{{ $productRequest->product_category }}</td>
                                                        <td>{{ $productRequest->email }}</td>
                                                        <td>{{ $productRequest->product_link }}</td>
                                                        <td>{{ $productRequest->additional_info }}</td>
                                                        <td>{{ $productRequest->created_at }}</td>
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
