@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Disputes</h4>
                        <?php
                        $disputes = cc('disputes.status');
                        ?>
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
                                                    <th rowspan="1" colspan="1">Order ID</th>
                                                    <th rowspan="1" colspan="1">Merchant Email</th>
                                                    <th rowspan="1" colspan="1">Customer Email</th>
                                                    <th tabindex="0" rowspan="1" colspan="1">Dispute Reference ID
                                                    </th>
                                                    <th rowspan="1" colspan="1">Dispute Category</th>
                                                    <th rowspan="1" colspan="1">Dispute Description</th>
                                                    <th rowspan="1" colspan="1">Dispute Option</th>
                                                    <th rowspan="1" colspan="1">Dispute status</th>
                                                    {{-- <th rowspan="1" colspan="1">Arbitrator Name</th> --}}
                                                    <th rowspan="1" colspan="1">Date</th>
                                                    <th rowspan="1" colspan="1"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data as $item)
                                                @php
                                                    $dispute_status = $disputes[$item->dispute_status];
                                                    $class = $item->dispute_status > 1 ? 'badge badge-success pr-3 pl-3 pt-2 pb-2'
                                                    : 'badge badge-danger pr-3 pl-3 pt-2 pb-2';
                                                @endphp
                                                    <tr role="row">
                                                        <td class="text-lowercase">{{ $item->order_id }}</td>
                                                        <td class="text-lowercase">{{ $item->merchant_email }}</td>
                                                        <td class="text-lowercase">{{ $item->customer_email }}</td>
                                                        <td>{{ $item->dispute_referenceid }}</td>
                                                        <td>{{ $item->dispute_category }}</td>
                                                        <td>{{ $item->dispute_description }}</td>
                                                        <td>{{ $item->dispute_option }}</td>
                                                        <td>
                                                            <span class="{{ $class }}">{{ $dispute_status }}</span>
                                                        </td>
                                                        {{-- <td class="text-lowercase">{{ $item->arbitrator_name }}</td> --}}
                                                        <td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                                                        </td>
                                                        <td><a href="{{ route('dispute.details', ['id' => $item->id]) }}"
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
