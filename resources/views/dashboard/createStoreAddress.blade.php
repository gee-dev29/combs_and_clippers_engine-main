@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>{{ is_null($storeAddress) ? 'Add' : 'Edit' }} Store Address</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('storeAddress.add') }}">
                            @include('dashboard.flash')
                            @csrf
                            <input name="merchantID" type="hidden" id="merchantID" value="{{ $merchantID }}">
                            <div class="form-group mb-4">
                                <label class="mb-0">Address</label>
                                <input name="street" class="form-control" id="street" type="text"
                                    placeholder="Ex: Plot 1 Entebbe Rd, Clanson House, 23495" required 
                                    value = "{{ is_null($storeAddress) ? '' : removeLastPartAfterLastThreeCommas($storeAddress->address) }}" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">City</label>
                                <input name="city" class="form-control" id="city" type="text"
                                    placeholder="Ex: Kampala" required 
                                    value = "{{ is_null($storeAddress) ? '' : $storeAddress->city }}" />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">District</label>
                                <input name="state" class="form-control" id="state" type="text"
                                    placeholder="Ex: Kampala" required 
                                    value = "{{ is_null($storeAddress) ? '' : $storeAddress->state }}" />
                            </div>
                            <br>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">{{ is_null($storeAddress) ? 'Add' : 'Update' }} Address</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3"
                                href="{{ route('cdetails', ['id' => $merchantID]) }}"><i class="fa fa-long-arrow-left"
                                    aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Footer Start -->
    </div>
@endsection
