@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">All Categories</h4>
                        @include('dashboard.flash')
                        <div class="table-responsive">
                            <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('blog.category.create') }}"><i
                                class="fa fa-plus" aria-hidden="true"></i> Create Category</a>
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
                                                        colspan="1" style="width: 46px;"> Name</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;"> Date Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($data as $item)
                                                    <tr role="row">
                                                        <td>{{ $item->name }}</td>
                                                        <td>{{ Carbon\Carbon::parse($item->updated_at)->format('d M Y') }}</td>
                                                        <td>
                                                            <a href="{{ route('blog.category.show', ['id' => $item->id]) }}"
                                                                class="btn btn-primary btn-sm pl-3 pr-3">Edit</a>
                                                            <a href="{{ route('blog.category.delete', ['id' => $item->id]) }}"
                                                                onclick="return confirm('Are you sure you want to delete this item?');"
                                                                class="btn btn-danger btn-sm">Delete</a>

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
