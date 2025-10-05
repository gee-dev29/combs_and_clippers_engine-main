@extends('layouts.app')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Bulk Upload Merchant</h3>
                <div class="card mb-5">
                    <div class="card-header">
                        <a class="mt-3 btn btn-link" href="{{ route('sample.download') }}">Download Sample Sheet</a>
                    </div>
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('customer.bulk.add') }}" enctype="multipart/form-data">
                            @include('dashboard.flash')
                            @csrf
                            <div class="form-group mb-4">
                                <label class="mb-0">Excel file</label>
                                <input 
                                    name="bulk" 
                                    class="form-control" 
                                    id="bulk"
                                    type="file" 
                                    accept='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel' 
                                    required/>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Upload</button>
                            </div>

                            <a class="mt-3 btn btn-link" href="{{ route('customers', ['type' => 'Merchant']) }}">Customers</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div><!-- Footer Start -->


    </div>
@endsection
