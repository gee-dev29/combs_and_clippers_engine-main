@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto mb-4">
                <div class="card text-left">
                    <div class="card-header bg-primary text-white mb-0">
                        <h4 class="card-title text-white font-weight-bold">Order details</h4>
                    </div>

                    <?php
                    $details->forget(['deleted_at', 'confirmation_pin', 'confirmation_pin_expires_at']);
                    ?>
                    <div class="card-body">
                        @include('dashboard.flash')
                        <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('orders') }}">Go back</a>

                        <table class="display table table-striped table-bordered">
                            @foreach ($details as $key => $detail)
                                <tr>
                                    @if ($key == 'status')
                                        @php
                                            $statusArray = cc('transaction.status');
                                            $status = $statusArray[$detail];
                                        @endphp
                                        <td class="text-uppercase font-weight-bold">Status</td>
                                        <td><span class="badge badge-dark pl-3 pr-3 pt-2 pb-2">{{ $status }}</span>
                                        </td>
                                    @elseif ($key == 'payment_status')
                                        <td class="text-uppercase font-weight-bold">Payment Status</td>
                                        <td>
                                            @if ($detail == 1)
                                                <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Paid</span>
                                            @else
                                                <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Unpaid</span>
                                            @endif
                                        </td>
                                    @elseif ($key == 'disbursement_status')
                                        <td class="text-uppercase font-weight-bold">Disbursement Status</td>
                                        <td>
                                            @if ($detail == 1)
                                                <span class="badge badge-success pl-3 pr-3 pt-2 pb-2">Disbursed</span>
                                            @else
                                                <span class="badge badge-danger pl-3 pr-3 pt-2 pb-2">Undisbursed</span>
                                            @endif
                                        </td>
                                    @else
                                        <td class="text-uppercase font-weight-bold"> {{ str_replace('_', ' ', $key) }}</td>
                                        <td>{{ $detail }}</td>
                                    @endif
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
                        <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('orders') }}">Go back</a>

                        @if ($details->get('payment_status') == 1)
                            <p>
                                <a href="{{ route('notification.trigger', ['id' => $details->get('id')]) }}"
                                    class="btn btn-primary btn-sm pl-3 pr-3">Trigger
                                    Notification</a>
                            </p>
                        @endif
                        <button data-c="cancel" data-v="cancel" id="cancel" type="button"
                            class="btn btn-outline-danger pl-4 pr-4 mb-3">Cancel Order <i class="fa fa-times"
                                aria-hidden="true"></i></button>

                        <form action="{{ route('order.track') }}" method="post">
                            @csrf
                            <input type="hidden" name="orderID" value="{{ $details->get('id') }}">
                            <button type="submit" class="btn btn-outline-success pl-4 pr-4 mb-3">
                                <i class="fa fa-map" aria-hidden="true"> Track Order</i>
                            </button>
                        </form>

                        <form action="{{ route('order.requestPickup') }}" method="post">
                            @csrf
                            <input type="hidden" name="orderID" value="{{ $details->get('id') }}">
                            <button type="submit" class="btn btn-outline-success pl-4 pr-4 mb-3">
                                <i class="fa fa-truck" aria-hidden="true"> Request Pickup</i>
                            </button>
                        </form>

                        <form action="{{ route('order.requestDelivery') }}" method="post">
                            @csrf
                            <input type="hidden" name="orderID" value="{{ $details->get('id') }}">
                            <button type="submit" class="btn btn-outline-success pl-4 pr-4 mb-3">
                                <i class="fa fa-plane" aria-hidden="true"> Request Delivery</i>
                            </button>
                        </form>

                        <form action="{{ route('order.markAsDelivered') }}" method="post">
                            @csrf
                            <input type="hidden" name="orderID" value="{{ $details->get('id') }}">
                            <button type="submit" class="btn btn-outline-success pl-4 pr-4 mb-3">
                                <i class="fa fa-check" aria-hidden="true"> Mark as Delivered</i>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" id="blm">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('order.cancel') }}" method="post">
                        @csrf
                        <input name="orderID" type="hidden" id="orderID" value="{{ $details->get('id') }}">
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea class="form-control" id="reason" rows="3" name="reason" required></textarea>
                        </div>
                        <button type="submit" id="bn" class="btn pl-3 pr-3">Submit</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footer')
    <script>
        $("#cancel").click(function() {
            $("#blm").modal('show');
        });
    </script>
@endsection
