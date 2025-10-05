@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Shipment details</h4>
                        <?php
                        $details->forget(['confirmation_pin','buyer_id','merchant_id','payurl','address_id','cart_id','confirmation_pin_expires_at','deleted_at']);
                        ?>

                        <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('shipment.all') }}">Go back</a>
                        <table class="display table table-striped table-bordered">
                            @foreach ($details as $key => $detail)
                                <tr>
                                    <td class="text-uppercase font-weight-bold"> {{ str_replace('_', ' ', $key) }}</td>
                                    <td>{{ $detail }}</td>
                                </tr>
                            @endforeach

                        </table>
                        <h4 class="text-uppercase mt-4">Items</h4>
                        <table class="display table table-striped table-bordered">
                            <thead>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->productname }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->price }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('shipment.all') }}">Go back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
