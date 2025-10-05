@extends('layouts.app')
@section('content')
    
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto mb-4">
            <div class="card text-left">
                <div class="card-header bg-primary text-white mb-0">
                    <h4 class="card-title text-white font-weight-bold">Invoice details</h4>
                </div>
                <div class="card-body">
                

                    <table class="display table table-striped table-bordered">
    @foreach ($details as $key => $detail)
 <tr>
     <td class="text-uppercase font-weight-bold">   {{ str_replace('_',' ',$key) }}</td> <td>{{ $detail }}</td>
 </tr>
@endforeach

</table>
<a class="btn btn-outline-primary pl-4 pr-4 mb-3" href="{{ route('invoices')}}">Go back</a>
                </div>
                
            </div>
        </div>
    </div>
</div>

@endsection