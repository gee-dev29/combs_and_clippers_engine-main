@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 mb-4">
            <div class="card text-left">
                <div class="card-body">
                    <h4 class="card-title mb-5 font-weight-bold">Payments</h4>
                   <div class="table-responsive">
                        <div id="zero_configuration_table_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                          
                                            <div class="row"><div class="col-sm-12">
                                                
                                                
                                                <table class="display table table-striped table-bordered dataTable" id="zero_configuration_table" style="width: 100%;" role="grid" aria-describedby="zero_configuration_table_info">
                            <thead>
                                <tr role="row">
                                   
                                    <th class="sorting_asc" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" style="width: 46px;">Customer</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Position: activate to sort column ascending" style="width: 53px;">Channel</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Status</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Date</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Age: activate to sort column ascending" style="width: 26px;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($payments as $item)
                                <tr role="row">
                                    <td class="text-capitalise">{{ $item->customer }}</td>
                                    <td>{{ $item->channel }}</td>
                                    <td>{{ $item->trans_status }}</td>
                                    <td>{{ $item->created_at }}</td>
                                  
                                    <td><a href="{{ route('payment.details', ['id' => $item->id]) }}" class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>
                              
                                </tr> 
                                @endforeach
                          
                            
                            </tbody>
                           
                        </table></div></div>
                
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

</div>


@endsection