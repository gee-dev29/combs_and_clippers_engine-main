@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto mb-4">
            <div class="card text-left">
                <div class="card-body">
              
                    <h4 class="card-title mb-5 font-weight-bold">Transaction details</h4>
                    <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('transactions')}}">Go back</a>
<table class="display table table-striped table-bordered">
    @foreach ($details as $key => $detail)
 <tr>
     <td class="text-uppercase font-weight-bold">   {{ str_replace('_',' ',$key) }}</td> <td>{{ $detail }}</td>
 </tr>
@endforeach

</table>
<a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('transactions')}}"> <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
<a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('order.details',['id' => $details->get('order_id')])}}">View order details</a>

                </div>
                
            </div>
        </div>
    </div>
</div>


@endsection