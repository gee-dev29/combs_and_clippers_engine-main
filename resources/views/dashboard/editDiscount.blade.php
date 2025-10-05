@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h3>Edit Discount</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('discount.update') }}">
                            @include('dashboard.flash')
                            @csrf
                            <input name="discountID" type="hidden" id="discountID" value="{{ $discount->id }}">
                            <input name="merchantID" type="hidden" id="merchantID" value="{{ $discount->merchant_id }}">
                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Name</label>
                                <input name="discount_name" class="form-control" id="discount_name" type="text"
                                    placeholder="Enter discount name" required value="{{ $discount->discount_name }}" />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Type</label>
                                <select name="discount_type" class="custom-select" required>
                                    <option value="F" {{ $discount->discount_type == 'F' ? 'selected' : '' }}>Fixed
                                        Amount</option>
                                    <option value="P" {{ $discount->discount_type == 'P' ? 'selected' : '' }}>
                                        Percentage</option>
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Discount Amount</label>
                                <input class="form-control" id="discount" name="discount" type="number" autocomplete="off"
                                    placeholder="Enter discount amount" required value="{{ $discount->discount }}" />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Valid From</label>
                                <input class="form-control" id="start_date" name="start_date" type="text"
                                    value="{{ $discount->start_date }}" autocomplete="off" required />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Valid To</label>
                                <input class="form-control" id="end_date" name="end_date" type="text" autocomplete="off"
                                    value="{{ $discount->end_date }}" required />
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Merchant</label>
                                <select id="discountMerchant" name="merchantID" required>
                                    <option value="{{ $discount->merchant->id }}">{{ $discount->merchant->name }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label class="mb-0">Apply To Products</label>
                                <select id="product_ids" name="product_ids[]" required multiple>
                                    @foreach ($discount->products as $product)
                                        <option value="{{ $product->id }}" selected>{{ $product->productname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Update Discount</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('discounts') }}"><i
                                    class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Footer Start -->
    <script>
        function fetchMerchantProducts() {
            var selectedMerchant = $('#discountMerchant').val();
            var existingOptions = $('#product_ids').val();
            // Make an AJAX GET request
            $.ajax({
                url: '/admin/fetchMerchantProducts/' + selectedMerchant,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Populate the select field with new options
                    $.each(data, function(key, value) {
                        if (existingOptions.indexOf(key) == -1) {
                            $('#product_ids').append($('<option>').text(value).attr('value', key));
                        }
                    });
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                }
            });
        }

        $(document).ready(function() {
            fetchMerchantProducts();
        });
    </script>

    <script>
        new SlimSelect({
            select: '#discountMerchant'
        });
        new SlimSelect({
            select: '#product_ids'
        });
    </script>

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
