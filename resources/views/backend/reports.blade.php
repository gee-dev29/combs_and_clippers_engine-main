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
                </div>

                <div class="row">
                    @if(Session::has('message'))
                         <i style="color: red;" class=""> {{ Session::get('message') }} </i>
                      @endif()
                     @if(count($errors) > 0)
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                     @endif
                                <div class="col-md-12 col-sm-12">
                                    <div class="well">
                           <!--    <h4>Normal Well</h4> -->
                                      <div class="col-md-8 col-sm-12"> 
                                        <form  method="post" action="{{ route('report.fetch')}}">
                                          {{ csrf_field() }}
                                          <div class="container">
                                              <label class="">From Date</label>
                                              <div class='input-group date' id='datetimepicker' >
                                                 <input type='text' name="from_date" class="form-control"   required="" readonly="" />
                                                 <span class="input-group-addon">
                                                 <span class="glyphicon glyphicon-calendar"></span>
                                                 </span>
                                              </div>

                                            <span class="spaner">
                                              <label class="">To Date</label>
                                              <div class='input-group date' id='datetimepicker1'>
                                                 <input type='text' name="to_date" class="form-control" required="" readonly="" />
                                                 <span class="input-group-addon">
                                                 <span class="glyphicon glyphicon-calendar"></span>
                                                 </span>
                                              </div>
                                            </span>
                                            
                                          </div>
                                          <br>
                                          
                                        <div class="container">
                                          <label class="">Client's Report</label>
                                          <select  name="client"  class="labeler form-control" required="" id="modal_">
                                              <option value="">Select Client</option>
                                            @if(!is_null($orgs))
                                              @foreach($orgs as $org)
                                              <option value="{{$org->id}}">{{$org->clientName}}</option>
                                              @endforeach
                                            @endif
                                              
                                             
                                          </select>

                                          <span class="spaner">
                                            <label class="">Report Format</label>
                                            <select  name="format"  class="labeler form-control" required="" id="modal_">
                                              <option value="xlsx">Excel</option>
                                              <option value="pdf">PDF</option>
                                              
                                            </select>
                                          </span>
                                        </div>

                                        <div class="container">
                                          <label class="">Report Type</label>
                                          <select  name="report_type"  class="labeler form-control" required="" id="modal_">
                                              
                                              <option value="subscription">Subscription</option>
                                              <option value="unsubscription">Unsubscription</option>
                                              
                                             
                                          </select>

                                          <span class="spaner">
                                            <label class="">Services</label>
                                            <select  name="services[]"  class="labeler form-control" required="" multiple="" id="modal_">
                                              <option value="">All</option>
                                            @if(!is_null($services))
                                              @foreach($services as $service)
                                              <option value="{{$service->product_id}}">{{$service->name}}</option>
                                              @endforeach
                                            @endif
                                              
                                            </select>
                                          </span>

                                          
                                        </div>
                                         
                                          <br>
                                          <input type="submit" name="" value="Submit" class="btn btn-primary">
                                        </form>
                                      </div>
                                        
                                        
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