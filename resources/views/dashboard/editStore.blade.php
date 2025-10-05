@extends('layouts.app')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Edit Store</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('store.update') }}" enctype="multipart/form-data">
                            @include('dashboard.flash')
                            @csrf
                            <input name="merchantID" type="hidden" id="merchantID" value="{{ $store->merchant_id }}">
                            <div class="form-group mb-4">
                                <label class="mb-0">Store Name</label>
                                <input name="store_name" value="{{ $store->store_name }}" class="form-control"
                                    id="store_name" type="text" placeholder="Enter store name" required />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Description</label>
                                <textarea class="form-control" id="store_description" rows="10" name="store_description"
                                    placeholder="Enter store description" required> {{ $store->store_description }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Category</label>
                                <select name="store_category" class="custom-select" required>
                                    @foreach (\App\Models\StoreCategory::all() as $category)
                                        <option value="{{ $category->id }}"
                                            {{ !is_null($store->store_category) && $store->store_category == $category->id ? 'selected' : '' }}>
                                            {{ $category->categoryname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Website</label>
                                <input name="website" value="{{ $store->website }}" class="form-control" id="website"
                                    type="text" placeholder="Enter store website" />

                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Icon</label>
                                <input type="file" class="form-control-file" id="store_icon" name="store_icon">
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Banner</label>
                                <input type="file" class="form-control-file" id="store_banner" name="store_banner">
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="featured"
                                    name="featured" {{ $store->featured == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    Featured
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="refund_allowed"
                                    name="refund_allowed" {{ $store->refund_allowed == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="refund_allowed">
                                    Refund Allowed
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="replacement_allowed"
                                    name="replacement_allowed" {{ $store->replacement_allowed == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="replacement_allowed">
                                    Replacement Allowed
                                </label>
                            </div>
                            <br>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Update Store</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3"
                                href="{{ route('cdetails', ['id' => $store->merchant_id]) }}"><i
                                    class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div><!-- Footer Start -->


    </div>
@endsection
