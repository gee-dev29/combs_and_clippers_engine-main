@extends('layouts.app')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Edit Product</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('product.update') }}"
                            enctype="multipart/form-data">
                            @include('dashboard.flash')
                            @csrf
                            <input name="productID" type="hidden" id="productID" value="{{ $product->id }}">
                            <input name="merchantID" type="hidden" id="merchantID" value="{{ $product->merchant_id }}">
                            <div class="form-group mb-4">
                                <label class="mb-0">Product Name</label>
                                <input name="productname" value="{{ $product->productname }}" class="form-control"
                                    id="productname" type="text" placeholder="Enter product name" required />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Description</label>
                                <textarea class="form-control" id="description" rows="10" name="description"
                                    placeholder="Enter product description" required> {{ $product->description }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Quantity</label>
                                <input name="quantity" value="{{ $product->quantity }}" class="form-control" id="quantity"
                                    type="number" min="0" placeholder="Enter available quantity" required />

                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Price</label>
                                <input class="form-control" value="{{ $product->price }}" id="price" name="price"
                                    type="number" autocomplete="off" placeholder="Enter price" required />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Delivery Period (days)</label>
                                <input class="form-control" value="{{ $product->deliveryperiod }}" id="deliveryperiod"
                                    name="deliveryperiod" type="number" min="1" autocomplete="off"
                                    placeholder="Enter delivery period" required />
                            </div>


                            <div class="form-group mb-4">
                                <label class="mb-0">Product Category</label>
                                <select name="category_id" class="custom-select" required>
                                    @foreach (\App\Models\Category::all() as $category)
                                        <option value="{{ $category->id }}"
                                            {{ !is_null($product->category_id) && $product->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->categoryname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <img src="{{ $product->image_url }}" alt="Image" class="img-fluid">
                                </div>
                                @foreach ($product->photos as $image)
                                    <div class="col-md-4 mb-4">
                                        <img src="{{ $image->image_link }}" alt="Image" class="img-fluid">
                                    </div>
                                @endforeach
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Product Image</label>
                                <input type="file" class="form-control-file" id="product_image" name="product_image">
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Optional Photos</label>
                                <input type="file" class="form-control-file" id="optional_images" name="optional_images"
                                    multiple>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Update Product</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3"
                                href="{{ route('cdetails', ['id' => $product->merchant_id]) }}"><i
                                    class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div><!-- Footer Start -->


    </div>
@endsection
