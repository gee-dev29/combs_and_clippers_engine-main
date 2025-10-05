@extends('layouts.app')
@section('content')
    
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 mb-4">
            <div class="card text-left">
                <div class="card-body">
                    <h4 class="card-title mb-5 font-weight-bold">Invoices</h4>
      
                   <div class="table-responsive">
                        <div id="zero_configuration_table_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                          
                                            <div class="row"><div class="col-sm-12">
                                      
                                                
                                        
                                                <table class="display table table-striped table-bordered dataTable" id="zero_configuration_table" style="width: 100%;" role="grid" aria-describedby="zero_configuration_table_info">
                            <thead>
                                <tr role="row">
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" style="width: 46px;">VAT (GBP)</th>

                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Total (GBP)</th>
                                    <th class="sorting_asc" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" style="width: 46px;">Merchant</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Customer Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Date</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Age: activate to sort column ascending" style="width: 26px;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($invoices as $item)
                                <tr role="row">
                                    <td class="text-capitalize">{{ number_format($item->vat,2) }}</td>
                                    <td>{{ number_format(intval($item->totalcost)) }}</td>
                                    <td class="text-capitalize">{{ $item->merchantName }}</td>
                                    <td>{{ $item->customerName }}</td>
                                   
                                    <td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y')}}</td>
                                  
                                    <td><a href="{{ route('invoices.details', ['id' => $item->id]) }}" class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>
                              
                                </tr> 
                                @endforeach
                          
                            
                            </tbody>
                           
                        </table>
                    <button type="button" class="btn btn-primary pl-3 pr-3">Export data <i class="fa fa-cloud-download" aria-hidden="true"></i> </button>
                    </div></div>
                
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

</div>


@endsection