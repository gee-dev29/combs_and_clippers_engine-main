@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Discounts</h4>
                        <div class="table-responsive">
                            <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('discount.create') }}"><i
                                    class="fa fa-plus" aria-hidden="true"></i> Create Discount</a>
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">
                                @include('dashboard.flash')
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="display table table-striped table-bordered dataTable"
                                            id="zero_configuration_table" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th rowspan="1" colspan="1">Merchant</th>
                                                    <th rowspan="1" colspan="1">Discount Name</th>
                                                    <th rowspan="1" colspan="1">Discount Type</th>
                                                    <th rowspan="1" colspan="1">Discount Value</th>
                                                    <th rowspan="1" colspan="1">Validity</th>
                                                    <th rowspan="1" colspan="1">Discount Products</th>
                                                    <th rowspan="1" colspan="1">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($discounts as $discount)
                                                    <tr role="row">
                                                        <td>{{ $discount->merchant->name }}</td>
                                                        <td>{{ $discount->discount_name }}</td>
                                                        <td>{{ $discount->discount_type == 'F' ? 'Fixed' : 'Percent' }}</td>
                                                        <td>{{ $discount->discount_type == 'F' ? cc('default_currency') . $discount->discount : $discount->discount . '% off' }}
                                                        </td>
                                                        <td>{{ Carbon\Carbon::parse($discount->start_date)->format('d M, Y') }}
                                                            -
                                                            {{ Carbon\Carbon::parse($discount->end_date)->format('d M, Y') }}
                                                        </td>
                                                        <td>
                                                            @foreach ($discount->products as $product)
                                                                {{ $loop->first ? '' : ', ' }}{{ $product->productname }}
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('discount.edit', ['id' => $discount->id]) }}"
                                                                class="btn btn-warning pl-3 pr-3">Edit</a>
                                                            <button data-discount_id="{{ $discount->id }}" type="button"
                                                                class="btn btn-danger pl-4 pr-4 mb-3 trash"> Delete <i
                                                                    class="fa fa-trash" aria-hidden="true"></i> </button> 
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
        <div class="modal" tabindex="-1" id="trash">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Discount</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">Are you sure you want to delete this discount ?</p>
                        <form action="{{ route('discount.delete') }}" method="post" class="text-center">
                            @csrf
                            <input type="hidden" id="discountID" name="discountID">
                            <button type="submit" class="btn pl-3 pr-3">Yes, delete discount</button>
                            <button type="button" class="btn btn-dark pl-3 pr-3" data-dismiss="modal">No, don't
                                delete</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(".trash").click(function() {
            var discountID = $(this).data('discount_id');
            $("#discountID").val(discountID);

            $("#trash").modal('show');
        });
    </script>
@endsection
