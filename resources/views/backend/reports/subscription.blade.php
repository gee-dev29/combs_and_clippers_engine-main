@extends('backend.layouts.master')

@section('content')


<div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <h1>Reports</h1>
                    
                    <!-- <form  method="post" action="{{ route('report.fetch')}}">
                    {{ csrf_field() }}
                    <div class="col-md-2">
                        <select  name="reportType"  class="form-control" required="" id="">
                              <option value="">Select Report Type</option>
                              <option value="subscription">Subscription</option>
                              <option value="unsubscription">Unsubscription</option>
                        </select>
                    </div>
                    
                    
                    <div class="col-md-2 mt-3 mt-md-0">
                        <select  name="partner"  class="form-control" required="" id="">
                              <option value="">Select Partner</option>
                              <option value="">Manotel</option>
                              
                        </select>
                    </div>
                    <div class="col-md-2 mt-3 mt-md-0">
                        <select  name="format"  class="form-control" required="" id="">
                              <option value="xlsx">Excel</option>
                              <option value="pdf">PDF</option>
                        </select>
                    </div>

                    <div class="col-md-2 mt-3 mt-md-0">
                         <div class='input-group date' id='datetimepicker' >
                             <input type='text' placeholder="From Date" name="from_date" class="form-control"   required="" readonly="" />
                             <span class="input-group-addon">
                             <span class="glyphicon glyphicon-calendar"></span>
                             </span>
                          </div>
                    </div>
                    <div class="col-md-2 mt-3 mt-md-0">
                         <div class='input-group date' id='datetimepicker1' >
                             <input type='text' placeholder="To Date" name="from_date" class="form-control"   required="" readonly="" />
                             <span class="input-group-addon">
                             <span class="glyphicon glyphicon-calendar"></span>
                             </span>
                          </div>
                    </div>
                    <div class="col-md-1 mt-3 mt-md-0">
                        <button class="btn btn-primary btn-block">Filter</button>
                    </div>
                  
                    </form>   -->
                    <a type="button" class="btn btn-danger btn-xs pull-right" href="{{ route('report.download', ['type' => 'subscription']) }}" style="margin-right: 10px;">Download Report</a>
                    <a type="button" class="btn btn-primary btn-xs pull-right" href="{{ route('reports')}}" style="margin-right: 10px;">Reports</a>
                </div>

                  <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="well">
                           <!--    <h4>Normal Well</h4> -->
                                        <table class="table">
                                          <thead class="thead-dark">
                                            <tr>
                                              
                                              <th scope="col">#</th>
                                              <th scope="col">MSISDN</th>
                                              <th scope="col">Network</th>
                                              <th scope="col">Service ID</th>
                                              <th scope="col">Product ID</th>
                                              <th scope="col">Amount</th>
                                              <th scope="col">Start Date</th>
                                              <th scope="col">End Date</th>
                                              <th scope="col">Date</th>
                                             
                                            </tr>
                                          </thead>
                                          <tbody>
       
                                          @if(count($reports) > 0)
                                            @foreach($reports as $sub)
                                            <tr>
                                              <th scope="row">{{ $loop->iteration }}</th>
                                              <td>{{ $sub->msisdn }}</td>
                                              <td>{{ $sub->network_provider }}</td>
                                              <td>{{ $sub->service_id }}</td>
                                              <td>{{ $sub->product_id }}</td>
                                              <td>{{ $sub->amount }}</td>
                                              <td>{{ $sub->start_date }}</td>
                                              <td>{{ $sub->end_date }}</td>
                                              <td>{{ $sub->created_at }}</td>
                                              
                                            </tr>
                                            @endforeach()
                                          @else
                                            No record found.
                                          @endif()
                                          </tbody>
                                        </table>

                                        
                                        <!-- <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#editModal">primary</a>
                                        <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">danger</a> -->
                                        <!-- <a href="#" class="btn btn-success">success</a> -->
                                    </div>
                                </div>
                                
                            </div>


                <!-- end of main-content -->
            </div>


        </div>

   @endsection('content')

  

 @section('extrascripts')
    
   <script>
      $(function () {
        $('#datetimepicker').datepicker({
        format: 'yyyy/mm/dd'
       });
        $('#datetimepicker1').datepicker({
          format: 'yyyy/mm/dd'
         });
     });
    </script>
 
 @endsection('extrascripts')