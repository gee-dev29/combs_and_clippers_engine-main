@extends('layouts.app')
@section('content')

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12 mx-auto mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold text-capitalize"><i
                                class="fa fa-long-arrow-right    "></i> {{ $collection->get('name') }}'s details</h4>
                        <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('customers', ['type' => 'Merchant']) }}"> <i
                                class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                        @if (Auth::user()->accounttype == 'Admin')
                            @if ($collection->get('accountstatus') == 1)
                                <button data-c="danger" data-v="block" id="block" type="button"
                                    class="btn btn-danger pl-4 pr-4 mb-3 bl">Block Customer <i class="fa fa-lock"
                                        aria-hidden="true"></i> </button>
                            @else
                                <button id="unblock" data-c="success" data-v="unblock" type="button"
                                    class="btn btn-success pl-4 pr-4 mb-3 bl">Unblock Customer <i class="fa fa-unlock"
                                        aria-hidden="true"></i> </button>
                            @endif
                        @endif

                        @include('dashboard.flash')

                        <table class="table table-bordered">
                            @foreach ($collection as $key => $detail)
                                <tr>
                                    @if ($key == 'accountstatus')
                                        <td class="text-uppercase font-weight-bold">Account Status</td>
                                        <td>
                                            @if ($detail == 1)
                                                <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Active</span>
                                            @else
                                                <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Deactivated</span>
                                            @endif
                                        </td>
                                </tr>
                            @elseif ($key == 'active_subscriptions_count')
                                <tr>
                                    <td class="text-uppercase font-weight-bold">Subscription Status</td>
                                    <td>
                                        @if ($detail > 0)
                                            <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Active</span>
                                        @else
                                            <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @elseif ($key == 'email_verified')
                                <tr>
                                    <td class="text-uppercase font-weight-bold">Email Verified</td>
                                    <td>
                                        @if ($detail)
                                            <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Yes</span>
                                        @else
                                            <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td id="{{ $key }}" data-{{ $key }}="{{ $detail }}"
                                        class="text-uppercase font-weight-bold"> {{ str_replace('_', ' ', $key) }}
                                    </td>
                                    <td>{{ $detail }}</td>
                                </tr>
                            @endif
                            @endforeach
                        </table>
                        <h6 class="text-uppercase mt-4 text-center">Store Details</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th>Store ID</th>
                                    <th>Store Name</th>
                                    <th>Store Category</th>
                                    <th style="width: 20%;">Store Description</th>
                                    <th>Store Icon</th>
                                    <th>Store Banner</th>
                                    <th>Website</th>
                                    <th>Featured</th>
                                    <th>Approved</th>
                                    <th>Refund Allowed</th>
                                    <th>Replacement Allowed</th>
                                    <th colspan="2">Action</th>
                                </thead>
                                <tbody>
                                    @if (!empty($store))
                                        <tr>
                                            <td>#{{ $store->id }}</td>
                                            <td>{{ $store->store_name }}</td>
                                            <td>{{ $store->category->categoryname }}</td>
                                            <td>{{ $store->store_description }}</td>
                                            <td>
                                                <a href="#" data-img="{{ $store->store_icon }}" class="icon"> <img
                                                        class="img-fluid rounded mb-3"
                                                        src="{{ $store->store_icon }}" /></a>
                                            </td>
                                            <td>
                                                <a href="#" data-img="{{ $store->store_banner }}" class="banner">
                                                    <img class="img-fluid rounded mb-3"
                                                        src="{{ $store->store_banner }}" /></a>
                                            </td>
                                            <td>{{ $store->website }}</td>
                                            <td>
                                                @if ($store->featured == 1)
                                                    <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Yes</span>
                                                @else
                                                    <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($store->approved == 1)
                                                    <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Yes</span>
                                                @else
                                                    <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($store->refund_allowed == 1)
                                                    <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Yes</span>
                                                @else
                                                    <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">No</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($store->replacement_allowed == 1)
                                                    <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Yes</span>
                                                @else
                                                    <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($store->approved == 1)
                                                    <button id="disapprove" data-c="danger" data-v="disapprove"
                                                        type="button"
                                                        class="btn btn-danger pl-4 pr-4 mb-3 store-action">Disapprove
                                                        <i class="fa fa-window-close" aria-hidden="true"></i> </button>
                                                @else
                                                    <button id="approve" data-c="success" data-v="approve" type="button"
                                                        class="btn btn-success pl-4 pr-4 mb-3 store-action">Approve <i
                                                            class="fa fa-check" aria-hidden="true"></i> </button>
                                                @endif

                                            </td>
                                            <td><a href="{{ route('store.edit', ['id' => $store->id]) }}"
                                                    class="btn btn-warning pl-3 pr-3">Edit</a></td>
                                        </tr>

                                        <div class="modal" tabindex="-1" id="store-modal">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Store Approval</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-center"> You are about to <span
                                                                class="action"></span>
                                                            <b>{{ $store->store_name }} <br />Are you sure you want to do
                                                                this
                                                                ?
                                                        </p>

                                                        <form action="{{ route('store.approval') }}" method="post"
                                                            class="text-center">
                                                            @csrf
                                                            <input name="store_id" type="hidden" id="store_id"
                                                                value="{{ $store->id }}">
                                                            <button type="submit" id="bn"
                                                                class="btn pl-3 pr-3">Yes,
                                                                <span class="action"></span> store</button>
                                                            <button type="button" class="btn btn-dark pl-4 pr-4"
                                                                data-dismiss="modal">No <i
                                                                    class="fa fa-remove"></i></button>

                                                        </form>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <br>
                        <h6 class="text-uppercase mt-4 text-center">Pickup Address</h6>
                        <div class="table-responsive">
                            @if (empty($pickupAddress))
                                <a class="btn btn-primary pl-4 pr-4 mb-3"
                                    href="{{ route('pickupAddress.create', ['merchantID' => $collection->get('id')]) }}"><i
                                        class="fa fa-plus" aria-hidden="true"></i> Add Pickup Address</a>
                            @endif
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th>ID</th>
                                    <th>Street</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th style="width: 20%;">Address</th>
                                    <th style="width: 20%;">Formatted Address</th>
                                    <th>Longitude</th>
                                    <th>Latitude</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @if (!empty($pickupAddress))
                                        <tr>
                                            <td>#{{ $pickupAddress->id }}</td>
                                            <td>{{ $pickupAddress->street }}</td>
                                            <td>{{ $pickupAddress->city }}</td>
                                            <td>{{ $pickupAddress->state }}</td>
                                            <td>{{ $pickupAddress->address }}</td>
                                            <td>{{ $pickupAddress->formatted_address }}</td>
                                            <td>{{ $pickupAddress->longitude }}</td>
                                            <td>{{ $pickupAddress->latitude }}</td>
                                            <td><a href="{{ route('pickupAddress.create', ['merchantID' => $collection->get('id')]) }}"
                                                    class="btn btn-warning pl-3 pr-3">Edit</a></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <br>
                        <h6 class="text-uppercase mt-4 text-center">Store Address</h6>
                        <div class="table-responsive">
                            @if (empty($storeAddress))
                                <a class="btn btn-primary pl-4 pr-4 mb-3"
                                    href="{{ route('storeAddress.create', ['merchantID' => $collection->get('id')]) }}"><i
                                        class="fa fa-plus" aria-hidden="true"></i> Add Store Address</a>
                            @endif
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <th>ID</th>
                                    <th>Street</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th style="width: 20%;">Address</th>
                                    <th style="width: 20%;">Formatted Address</th>
                                    <th>Longitude</th>
                                    <th>Latitude</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @if (!empty($storeAddress))
                                        <tr>
                                            <td>#{{ $storeAddress->id }}</td>
                                            <td>{{ $storeAddress->street }}</td>
                                            <td>{{ $storeAddress->city }}</td>
                                            <td>{{ $storeAddress->state }}</td>
                                            <td>{{ $storeAddress->address }}</td>
                                            <td>{{ $storeAddress->formatted_address }}</td>
                                            <td>{{ $storeAddress->longitude }}</td>
                                            <td>{{ $storeAddress->latitude }}</td>
                                            <td><a href="{{ route('storeAddress.create', ['merchantID' => $collection->get('id')]) }}"
                                                    class="btn btn-warning pl-3 pr-3">Edit</a></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <br>
                        <h6 class="text-uppercase mt-4 text-center"><strong>Total Products: {{ $product_count }}</strong>
                        </h6>

                        <div class="table-responsive">
                            <a class="btn btn-primary pl-4 pr-4 mb-3"
                                href="{{ route('product.create', ['merchantID' => $collection->get('id')]) }}"><i
                                    class="fa fa-plus" aria-hidden="true"></i> Add Product</a>
                            <div id="zero_configuration_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="multiple table table-striped table-bordered dataTable"
                                            id="zero_configuration" style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">

                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="zero_configuration" rowspan="1" colspan="1">
                                                        Product ID</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Name: activate to sort column ascending">
                                                        Product Name</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Code: activate to sort column ascending">
                                                        Product Code</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1" style="width: 25%;"
                                                        aria-label="Description: activate to sort column ascending">Product
                                                        Description</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Price: activate to sort column ascending">Product Price
                                                    </th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Period: activate to sort column ascending">Delivery
                                                        Period</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1">Product Images</th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Qty: activate to sort column ascending">Available Qty
                                                    </th>
                                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Type: activate to sort column ascending">Product Type
                                                    </th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($products as $product)
                                                    <tr>
                                                        <td>#{{ $product->id }}</td>
                                                        <td>{{ $product->productname }}</td>
                                                        <td>{{ $product->product_code }}</td>
                                                        <td>{{ $product->description }}</td>
                                                        <td>{{ $product->currency }}{{ $product->price }}</td>
                                                        <td>{{ $product->deliveryperiod }} days</td>
                                                        <td>
                                                            <a href="#" data-img="{{ $product->image_url }}"
                                                                class="prod"> <img class="img-fluid rounded mb-3"
                                                                    src="{{ $product->image_url }}" /></a>
                                                            @foreach ($product->photos as $image)
                                                                <a href="#" data-img="{{ $image->image_link }}"
                                                                    class="prod"> <img class="img-fluid rounded mb-3"
                                                                        src="{{ $image->image_link }}" /></a>
                                                            @endforeach
                                                        </td>
                                                        <td>{{ $product->quantity }}</td>
                                                        <td>{{ $product->product_type }}</td>
                                                        <td>
                                                            <a href="{{ route('product.edit', ['id' => $product->id]) }}"
                                                                class="btn btn-warning pl-3 pr-3">View/Edit</a>
                                                            <button data-product_id="{{ $product->id }}" type="button"
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
                        <br>
                        <br>
                        <h6 class="text-uppercase mt-4 text-center"><strong>Total Orders: {{ $order_count }}</strong>
                        </h6>
                        <div class="table-responsive">
                            <div id="language_option_table_wrapper"
                                class="dataTables_wrapper container-fluid dt-bootstrap4">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="multiple table table-striped table-bordered dataTable "
                                            id="language_option" style="width: 100%;" role="grid"
                                            aria-describedby="language_option_table_info">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="language_option" rowspan="1" colspan="1"
                                                        style="width: 46px;">Order ID</th>

                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="language_option" rowspan="1" colspan="1"
                                                        style="width: 46px;">Order Ref</th>

                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Delivery Type</th>

                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Buyer</th>
                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Order Amount(GBP)</th>

                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Delivery Fee(GBP)</th>

                                                    <th class="sorting_asc" rowspan="1" colspan="1"
                                                        style="width: 46px;">Total(GBP)</th>

                                                    <th class="sorting_asc" tabindex="0"
                                                        aria-controls="language_option" rowspan="1" colspan="1"
                                                        style="width: 46px;">Payment Status</th>

                                                    <th class="sorting" tabindex="0" aria-controls="language_option"
                                                        rowspan="1" colspan="1"
                                                        aria-label="Date: activate to sort column ascending"
                                                        style="width: 39px;">Order Date</th>

                                                    <th class="sorting" tabindex="0" aria-controls="language_option"
                                                        rowspan="1" colspan="1" style="width: 20px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $order)
                                                    <tr role="row">
                                                        <td>#{{ $order->id }}</td>
                                                        <td>{{ $order->orderRef }}</td>
                                                        <td>{{ $order->delivery_type }}</td>
                                                        <td>{{ $order->buyer->name }}</td>
                                                        <td class="font-weight-bold">
                                                            {{ number_format($order->totalprice, 2) }}</td>

                                                        <td class="font-weight-bold">
                                                            {{ number_format($order->shipping, 2) }}</td>

                                                        <td class="font-weight-bold">{{ number_format($order->total, 2) }}
                                                        </td>


                                                        <td>
                                                            @if ($order->payment_status == 1)
                                                                <span
                                                                    class="badge badge-success pl-3 pr-3 pt-2 pb-2">Paid</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Unpaid</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-capitalize">{{ $order->created_at }}</td>
                                                        <td><a href="{{ route('order.details', ['id' => $order->id]) }}"
                                                                class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>

                                                    </tr>
                                                @endforeach


                                            </tbody>

                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <a class="btn btn-primary pl-4 pr-4 mb-3"
                            href="{{ route('customers', ['type' => 'Merchant']) }}"><i class="fa fa-long-arrow-left"
                                aria-hidden="true"></i> Go back</a>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="blm">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Block {{ $collection->get('name') }}'s account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center"> You about to <span class="f"></span>
                        <b>{{ $collection->get('name') }}'s</b> account. <br />Are you sure you want to do this?
                    </p>



                    <form action="{{ route('customer.block', $collection->get('id')) }}" method="post"
                        class="text-center">
                        @csrf
                        <input name="id" type="hidden" id="idx" value="{{ $collection->get('id') }}">
                        <input type="hidden" id="customerName" value="{{ $collection->get('name') }}">
                        <button type="submit" class="btn pl-3 pr-3">Yes, <span class="f"></span>
                            the
                            account </button>
                        <button type="button" class="btn btn-dark pl-4 pr-4">No <i class="fa fa-remove"></i></button>

                    </form>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="trash">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center">Are you sure you want to delete this product?</p>
                    <form action="{{ route('product.delete') }}" method="post" class="text-center">
                        @csrf
                        <input type="hidden" id="productID" name="productID">
                        <button type="submit" class="btn pl-3 pr-3">Yes, delete product</button>
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

    <div class="modal fade" id="img" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">View Image File</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="v" class="text-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    <script>
        $(".icon").click(function(e) {
            const img = $(this).data('img');
            $("#v").html('<img class="img-fluid" src="' + img + '"/>');
            $("#img").modal('show');

        })

        $(".banner").click(function(e) {
            const img = $(this).data('img');
            $("#v").html('<img class="img-fluid" src="' + img + '"/>');
            $("#img").modal('show');

        })

        $(".prod").click(function(e) {
            const img = $(this).data('img');
            $("#v").html('<img class="img-fluid" src="' + img + '"/>');
            $("#img").modal('show');

        })
    </script>

    <script>
        $(".bl").click(function() {
            var cl = $(this).data('v');
            $(".f").html($(this).data('v'));

            $("#blm").modal('show');
        });
    </script>

    <script>
        $(".trash").click(function() {
            var productID = $(this).data('product_id');
            $("#productID").val(productID);

            $("#trash").modal('show');
        });
    </script>

    <script>
        $(".store-action").click(function() {
            var cl = $(this).data('v');
            $(".action").html($(this).data('v'));

            $("#store-modal").modal('show');
        });
    </script>
@endsection
