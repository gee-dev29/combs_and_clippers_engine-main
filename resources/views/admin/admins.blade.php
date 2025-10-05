@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Manage Admin</h4>
                        @if (session()->has('delete'))
                            <div class="alert alert-success">{{ session()->get('delete') }}</div>
                        @endif
                        <div class="table-responsive">
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="display table table-striped table-bordered dataTable"
                                            id="zero_configuration_table" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Account type</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">Name</th>
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" style="width: 46px;">email</th>
                                                    <th class="sorting" tabindex="0"
                                                        aria-controls="zero_configuration_table" rowspan="1"
                                                        colspan="1" aria-label="Age: activate to sort column ascending"
                                                        style="width: 26px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($admins as $item)
                                                    <tr role="row">
                                                        <td class="text-capitalize">{{ $item->accounttype }}</td>
                                                        <td class="text-capitalize">{{ $item->name }}</td>
                                                        <td>{{ $item->email }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.show', ['id' => $item->id]) }}"
                                                                class="btn btn-primary btn-sm pl-3 pr-3">Details</a>
                                                            <a href="{{ route('admin.delete', ['id' => $item->id]) }}"
                                                                onclick="return confirm('Are you sure you want to delete this item?');"
                                                                class="btn btn-danger btn-sm">Delete</a>

                                                        </td>

                                                    </tr>
                                                @endforeach


                                            </tbody>

                                        </table>
                                        <a href="{{ route('admins') }}" class="btn btn-primary">Register new admin</a>
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
