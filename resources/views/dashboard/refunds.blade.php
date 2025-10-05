@extends('layouts.app')
@section('content')
    
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 mb-4">
            <div class="card text-left">
                <div class="card-body">
                    <h4 class="card-title mb-5 font-weight-bold">Refunds

          @if (app('request')->input('from') !== null)
            <small>Filter: From  {{app('request')->input('from')}} <b>To:</b>   {{app('request')->input('to')}}</small>
          @endif
                </h4>


 {{--
<div class="container text-right">
    @if ( app('request')->input('from') !== null)
<button  class="btn btn-primary pl-4 pl-4"> 
  
<div class="dropdown">
    <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-download" aria-hidden="true"></i> Download
    </a>
  
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
      <a class="dropdown-item" href="#" id="export_button">Excel <i class="fa fa-file-excel-o" aria-hidden="true"></i> </a>
      <a class="dropdown-item" href="#">PDF <i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
    </div>
  </div> 
@endif
    <button data-toggle="modal" data-target="#exampleModal" class="btn ml-3 pl-4 pr-4 btn-dark"> <i class="fa fa-search" aria-hidden="true"></i> Filter by date</button>
</div>




 
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Filter by date</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{route('filter')}}" method="get">
              @csrf
              <input type="hidden" name="table" value="orders">
              <input type="hidden" name="page" value="orders">
<div class="form-row">
    <div class="col-md-6">
 <div class="form-group">
     <label class="mb-0">From[yyyy-mm-dd]</label>
     <input type="text" minlength="10" maxlength="10" class="form-control" name="from" required placeholder="yyyy-mm-dd">
 </div>
    </div>
    <div class="col-md-6">
        <label class="mb-0">To[yyyy-mm-dd]</label>
        <input minlength="10" maxlength="10" type="text" class="form-control" name="to" required placeholder="yyyy-mm-dd">
    </div>
</div>

<div class="form-group text-center">
    <button type="submit" class="btn btn-primary pr-3 pl-4">Filter</button>
</div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div> 
  --}}

                   <div class="table-responsive">
                        <div id="zero_configuration_table_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                          
                                            <div class="row"><div class="col-sm-12">
                                                
                                                
                                                <table class="display table table-striped table-bordered dataTable " id="zero_configuration_table" style="width: 100%;" role="grid" aria-describedby="zero_configuration_table_info">
                            <thead>
                             
                                <tr role="row">
                                    <th class="sorting_asc" rowspan="1" colspan="1" style="width: 46px;">Buyer</th>
                                    <th class="sorting_asc" rowspan="1" colspan="1" style="width: 46px;">Merchant</th>
                                    <th class="sorting_asc" rowspan="1" colspan="1" style="width: 46px;">Issue</th>
                                    

                                    <th class="sorting_asc" rowspan="1" colspan="1" style="width: 46px;">Amount(GBP)</th>
                                     
                                    <th class="sorting_asc" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" style="width: 46px;">Tranx Ref</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Merchant Email</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 39px;">Request Date</th>
                                    <th class="sorting" tabindex="0" aria-controls="zero_configuration_table" rowspan="1" colspan="1" style="width: 20px;"></th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($collection as $item)
                                <tr role="row">
                                    <td>{{ $item->cust_name }}</td>
                                    <td>{{ $item->merchant }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td class="font-weight-bold">{{ number_format($item->amount,2) }}</td>
                                   
                                    <td>{{ $item->trans_ref }}</td>
                                    <td class="text-capitalize">{{ $item->merchant_email }}</td>
                                    <td class="text-capitalize">{{ $item->date_request }}</td>
                                    {{--<td><a href="{{ route('order.details', ['id' => $item->id]) }}" class="btn btn-primary btn-sm pl-3 pr-3">Details</a></td>--}}
                              
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

@if (app('request')->input('from') !== null)
<script> 
$('.table').removeClass("dataTable");
</script>
    
@endif


<script>
   

    function html_table_to_excel(type)
    {
        var data = document.getElementById('zero_configuration_table');

        var file = XLSX.utils.table_to_book(data, {sheet: "sheet1"});

        XLSX.write(file, { bookType: type, bookSST: true, type: 'base64' });

        XLSX.writeFile(file, 'file.' + type);
    }

    const export_button = document.getElementById('export_button');

    export_button.addEventListener('click', () =>  {
        html_table_to_excel('xlsx');
    });
 </script>


@endsection