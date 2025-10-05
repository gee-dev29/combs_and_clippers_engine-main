@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h3>Edit Coupon</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('coupon.update') }}">
                            @include('dashboard.flash')
                            @csrf
                            <input name="couponID" type="hidden" id="couponID" value="{{ $coupon->id }}">
                            <div class="form-group mb-4">
                                <label class="mb-0">Coupon Code</label>
                                <input name="code" class="form-control" id="code" type="text"
                                    placeholder="Enter coupon code" required value="{{ $coupon->code }}" />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Type</label>
                                <select name="discount_type" class="custom-select" required>
                                    <option value="F" {{ $coupon->discount_type == 'F' ? 'selected' : '' }}>Fixed
                                        Amount</option>
                                    <option value="P" {{ $coupon->discount_type == 'P' ? 'selected' : '' }}>
                                        Percentage</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Amount</label>
                                <input class="form-control" id="discount" name="discount" type="number" autocomplete="off"
                                    placeholder="Enter discount amount" required value="{{ $coupon->discount }}" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Limit</label>
                                <input class="form-control" id="limit" name="limit" type="number" autocomplete="off"
                                    placeholder="Enter discount limit"  min="1" value="{{ $coupon->limit }}" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Valid From</label>
                                <input class="form-control" id="start_date" name="start_date" type="text"
                                    value="{{ $coupon->start_date }}" autocomplete="off" required />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Valid To</label>
                                <input class="form-control" id="end_date" name="end_date" type="text" autocomplete="off"
                                    value="{{ $coupon->end_date }}" required />
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Update Coupon</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('coupons') }}"><i
                                    class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Footer Start -->

    <script>
        $(function() {
            $('#start_date').datepicker({
                format: 'yyyy-mm-dd',
                startDate: '+0d',
                todayHighlight: true,
                autoclose: true
            });
            $('#end_date').datepicker({
                format: 'yyyy-mm-dd',
                startDate: '+0d',
                todayHighlight: true,
                autoclose: true
            });
        });
    </script>
@endsection
