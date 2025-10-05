@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 mb-4">
            <div class="card text-left">
                <div class="card-body">
                    <h4 class="card-title mb-5 font-weight-bold">Transactions</h4>
                   <div class="table-responsive">
                        <div id="zero_configuration_table_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                          
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <form class="mb-3" method="post" action="{{ route('transactions.filter') }}">
                                                        @if ($errors->any())
                                                            @foreach ($errors->all() as $error)
                                                                <div class="alert alert-danger small text-center">{{ $error }}</div>
                                                            @endforeach
                                                        @endif
                                                        @csrf
                                                        <div class="input-group mb-3">
                                                            <input type="hidden" name="action">
                                                            <input type="text" id="daterange"
                                                            name="date"
                                                            value="{{ request()->input('date') }}"
                                                            class="form-control"
                                                            placeholder="Date Range"
                                                            data-toggle="flatpickr"
                                                            data-options='{"mode": "range"}'>
                                                            <div class="input-group-append">
                                                              <button class="btn btn-primary mr-2" type="submit" name="action" value="filter">Filter</button>
                                                              <a href="{{ route('transactions') }}" class="btn btn-danger mr-2">Clear Filter</a>
                                                              <button class="btn btn-success" type="submit" name="action" value="export"> <i class="fa fa-file-excel-o"></i> Export</button>
                                                            </div>
                                                          </div>
                                                    </form>
                                                </div>  
                                                <div class="col-sm-12">

<table class="display table table-striped table-bordered dataTable" id="zero_configuration_table" style="width: 100%;" role="grid" aria-describedby="zero_configuration_table_info">
    <thead>
        <tr role="row">
            <th class="sorting" tabindex="0" rowspan="1" colspan="1"  style="width: 30px;">Customer</th>
            <th class="sorting" tabindex="0"  rowspan="1" colspan="1"  style="width: 39px;">Merchant</th>
            <th class="sorting" tabindex="0"  rowspan="1" colspan="1"  style="width: 39px;">Amount</th>
            <th class="sorting" tabindex="0"  rowspan="1" colspan="1"  style="width: 39px;">Description</th>
            <th class="sorting" tabindex="0"  rowspan="1" colspan="1"  style="width: 39px;">Date</th>
            <th class="sorting" tabindex="0"  rowspan="1" colspan="1"  style="width: 26px;">Details</th>
        </tr>
    </thead>
    <tbody>
        
        @foreach ($records as $item)
        <tr role="row" class="odd">
            <td>{{ $item->customer_email }}</td>
            <td>{{ $item->merchant_email }}</td>
            <td>{{ number_format($item->amount,2) }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->posting_date }}</td>
          
            <td><a href="{{ route('details', ['id' => $item->id]) }}" class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>
      
        </tr> 
        @endforeach
  
    
    </tbody>
   
</table>
</div></div>
                
</div>
</div>
</div>
</div>
</div>

</div>

</div>


@endsection