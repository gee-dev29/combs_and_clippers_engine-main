@extends('layouts.app')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Create Merchant Account</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('customer.add') }}">
                            @include('dashboard.flash')
                            @csrf
                            <div class="form-group mb-4">
                                <label class="mb-0">Name</label>
                                <input name="name" value="{{ old('name') }}" class="form-control" id="name"
                                    type="text" placeholder="Arya Stark" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Email address</label>
                                <input name="email" value="{{ old('email') }}" class="form-control" id="email"
                                    type="text" placeholder="aryastark@pepperest.com" />

                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Phone Number</label>
                                <input class="form-control" value="{{ old('phone') }}" id="phone" name="phone"
                                    type="text" autocomplete="off" placeholder="0779999707" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Account type</label>
                                <select required name="acct_type" class="custom-select">
                                    <option value="Merchant" selected>Merchant</option>
                                    {{-- <option value="Buyer">Buyer</option> --}}
                                    {{-- <option value="Merchant">Merchant</option> --}}
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Name</label>
                                <input name="store_name" value="{{ old('store_name') }}" class="form-control"
                                    id="store_name" type="text" placeholder="{{env("APP_NAME")}} store" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Store Category</label>
                                <select required name="store_category" class="custom-select">
                                    @foreach (\App\Models\StoreCategory::all() as $category)
                                        <option value="{{ $category->id }}">{{ $category->categoryname }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Create Account</button>
                            </div>

                            <a class="mt-5" href="{{ route('customers', ['type' => 'Merchant']) }}">Customers</a>

                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div><!-- Footer Start -->


    </div>
@endsection
