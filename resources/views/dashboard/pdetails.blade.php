@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto mb-4">
            <div class="card text-left">
                <div class="card-body">
                    <h4 class="card-title mb-5 font-weight-bold">Payment details</h4>
                    <a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('payments')}}">Go back</a>

<?php 
$details->forget('cust_auth_code');
?>

                    <table class="display table table-striped table-bordered">
    @foreach ($details as $key => $detail)
 <tr>
     <td class="text-uppercase font-weight-bold">   {{ str_replace('_',' ',$key) }}</td> <td data-v="{{$detail}}" id="{{ str_replace('_',' ',$key) }}">{{ $detail }}</td>
 </tr>
@endforeach

</table>
<a class="btn btn-primary pl-4 pr-4 mb-3" href="{{ route('payments')}}"> <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Go back</a>
                </div>
                
            </div>
        </div>
    </div>
</div>


@endsection