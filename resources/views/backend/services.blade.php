@extends('backend.layouts.master')

@section('content')

<div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <h1>Services</h1>
                    <ul>
                        <li><a href="#">Services</a></li>
                        <!-- <li>Version 1</li> -->
                    </ul>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                
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
                    <div class="col-md-12">
                        <div class="card o-hidden mb-4">
                            <div class="card-header d-flex align-items-center border-0">
                                <h3 class="w-50 float-left card-title m-0">My Services</h3>
                                <div class="dropdown dropleft text-right w-50 float-right">
                                @if(Auth::user()->super_admin == 1)
                                    <button class="btn bg-gray-100" id="dropdownMenuButton1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="nav-icon i-Gear-2"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton1"><a class="dropdown-item" data-toggle="modal" data-target="#newModal">Add new service</a>
                                        <!-- <a class="dropdown-item" href="#">View All users</a><a class="dropdown-item" href="#">Something else here</a> -->
                                    </div>
                                @endif
                                </div>
                            </div>
                            <div>
                                <div class="table-responsive">
                                    <table class="table text-center" id="user_table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Service ID</th>
                                                <th scope="col">Product ID</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Duration</th>
                                                <th scope="col">Sub Count</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($services) > 0)
                                                @foreach($services as $service)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>{{ $service->name }}</td>
                                                    <td>{{ $service->service_id }}</td>
                                                    <td>{{ $service->product_id }}</td>
                                                    <td>₦{{ $service->amount }}</td>
                                                    <td>{{ $service->duration }}</td>
                                                    
                                                    <td>{{ $service->subscriptions->count() }}</td>
                                                    
                                                    <td>
                                                    @if(Auth::user()->super_admin == 1)
                                                        <a class="text-success mr-2 editCat"  service_ID="{{ $service->id }}" serviceName="{{ $service->name }}"
                                              serviceAmount="{{ $service->amount }}" productID="{{ $service->product_id }}" serviceID="{{ $service->service_id }}" orgID="{{$service->organization_id}}" network="{{ $service->network_provider }}" duration="{{ $service->duration }}" data-toggle="modal" data-target="#editModal" title="Edit"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2 deleteCat" data-toggle="modal" data-target="#deleteModal" serviceId="{{ $service->id }}" title="Delete"><i class="nav-icon i-Close-Window font-weight-bold"></i></a>
                                                    @endif
                                                    </td>
                                                    
                                                </tr>
                                                @endforeach
                                            @endif
                                            
                                            <!-- <tr>
                                                <th scope="row">4</th>
                                                <td>Mathew Doe</td>
                                                <td><img class="rounded-circle m-0 avatar-sm-table" src="../../dist-assets/images/faces/1.jpg" alt="" /></td>
                                                <td>Mathew@gmail.com</td>
                                                <td><span class="badge badge-success">Active</span></td>
                                                <td>21 days</td>
                                                <td><a class="text-success mr-2" href="#"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2" href="#"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td>
                                            </tr> -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    

   @endsection('content')


   @section('modals')

   <!-- Trigger the modal with a button -->
                <!--  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Open Modal</button> -->
                
                <!-- new event Modal -->
                <div id="newModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">New Exemption</h4>
                      </div>
                      <div class="modal-body">
                        <p>Add Service</p>
                        <form method="post" action="{{ route('service.save')}}" enctype="multipart/form-data">
                          {{ csrf_field() }}
                        
                          
                          <div class="container">
                              <label class="">Service Name</label>
                              <div class='input-group date' id='name' >
                                 <input type='text' name="name" class="form-control"    value="{{ old('name') }}" />
                                 <span class="input-group-addon">
                                <!--  <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('name'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Amount</label>
                              <div class='input-group date' id='amount'>
                                 <input type='text' name="amount" class="form-control" value="{{ old('amount') }}" >
                                 <span class="input-group-addon">
                                <!--  <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('amount'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>
                          <!-- ['name','service_id', 'product_id', 'network_provider', 'amount', 'duration', 'duration_type', 'organization_id', 'service_type'] -->
                          <br>
                          <div class="container">
                              <label class="">Product ID</label>
                              <div class='input-group date' id='product_id' >
                                 <input type='text' name="product_id" class="form-control"    value="{{ old('product_id') }}" />
                                 <span class="input-group-addon">
                                 <!-- <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('product_id'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('product_id') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Service ID</label>
                              <div class='input-group date' id='service_id'>
                                 <input type='text' name="service_id" class="form-control" value="{{ old('service_id') }}" />
                                 <span class="input-group-addon">
                                 <!-- <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('service_id'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('service_id') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>

                           <br>
                          <div class="container">
                              <label class="">Network</label>
                              <div class='input-group date' id='network_provider' >
                                 <input type='text' name="network_provider" class="form-control"    value="{{ old('network_provider') }}" />
                                 <span class="input-group-addon">
                                 <!-- <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('network_provider'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('network_provider') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Duration</label>
                              <div class='input-group date' id='duration'>
                                 <input type='text' name="duration" class="form-control" value="{{ old('duration') }}" />
                                 <span class="input-group-addon">
                                 <!-- <span class="glyphicon glyphicon-calendar"></span> -->
                                 </span>
                                 @if ($errors->has('duration'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('duration') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>
                          
                          <div class="container">
                            <div class='input-group'  >
                              <span class="spaner">
                                <label class="">Organisation</label> 
                                <select  name="org_id" class="labeler form-control" required="" value="{{ old('org_id') }}" id="modal_exempt_type">
                                  <option value="">Select Organisation</option>
                                  @foreach($orgs as $org)
                                    <option value="{{$org->id}}">{{$org->clientName}}</option>
                                  @endforeach
                                      
                                  </select>
                                  @if ($errors->has('org_id'))
                                      <span class="text-danger">
                                          <strong>{{ $errors->first('org_id') }}</strong>
                                      </span>
                                  @endif
                              </span>
                            </div>
                          </div>
                          
                          <br>
                          <input type="submit" name="" value="Submit" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </form>
                      </div>
                      <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div> -->
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

                <!-- Edit Modal -->
                <div id="editModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Service</h4>
                      </div>
                      <div class="modal-body">
                        <p>Modify This Service.</p>
                        <form method="post" action="{{ route('service.edit')}}" enctype="multipart/form-data">
                          {{ csrf_field() }}
                          <input type="hidden" name="service_ID" id="modal_service_ID">

                          <div class="container">
                              <label class="">Service Name</label>
                              <div class='input-group'  >
                                 <input type='text' name="name" class="form-control"  id='modal_serviceName'  value="{{ old('name') }}" />
                                 <span class="input-group-addon">
                                
                                 </span>
                                 @if ($errors->has('name'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Amount</label>
                              <div class='input-group' >
                                 <input type='text' name="amount" class="form-control" id='modal_serviceAmount' value="{{ old('amount') }}" />
                                 <span class="input-group-addon">
                                 
                                 </span>
                                 @if ($errors->has('amount'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>
                          <!-- ['name','service_id', 'product_id', 'network_provider', 'amount', 'duration', 'duration_type', 'organization_id', 'service_type'] -->
                          <br>
                          <div class="container">
                              <label class="">Product ID</label>
                              <div class='input-group'  >
                                 <input type='text' name="product_id" class="form-control"  id='modal_productID'  value="{{ old('product_id') }}" />
                                 <span class="input-group-addon">
                                
                                 </span>
                                 @if ($errors->has('product_id'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('product_id') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Service ID</label>
                              <div class='input-group' >
                                 <input type='text' name="service_id" class="form-control" id='modal_serviceID' value="{{ old('service_id') }}" />
                                 <span class="input-group-addon">
                                 
                                 </span>
                                 @if ($errors->has('service_id'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('service_id') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>

                           <br>
                          <div class="container">
                              <label class="">Network</label>
                              <div class='input-group'  >
                                 <input type='text' name="network_provider" class="form-control"  id='modal_network' value="{{ old('network_provider') }}" />
                                 <span class="input-group-addon">
                                 
                                 </span>
                                 @if ($errors->has('network_provider'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('network_provider') }}</strong>
                                    </span>
                                @endif
                              </div>

                            <span class="spaner">
                              <label class="">Duration</label>
                              <div class='input-group' >
                                 <input type='text' name="duration" class="form-control" id='modal_Duration' value="{{ old('duration') }}" />
                                 <span class="input-group-addon">
                                 
                                 </span>
                                 @if ($errors->has('duration'))
                                    <span class="text-danger">
                                        <strong>{{ $errors->first('duration') }}</strong>
                                    </span>
                                @endif
                              </div>
                            </span>
                            
                          </div>

                          <div class="container">
                            <div class='input-group'  >
                              <span class="spaner">
                                <label class="">Organisation</label> 
                                <select  name="org_id" class="labeler form-control" required="" value="{{ old('org_id') }}" id="modal_orgID">
                                  <option value="">Select Organisation</option>
                                  @foreach($orgs as $org)
                                    <option value="{{$org->id}}">{{$org->clientName}}</option>
                                  @endforeach
                                      
                                  </select>
                                  @if ($errors->has('org_id'))
                                      <span class="text-danger">
                                          <strong>{{ $errors->first('org_id') }}</strong>
                                      </span>
                                  @endif
                              </span>
                            </div>
                          </div>
                          <br>
                          <input type="submit" name="" value="Submit" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </form>
                      </div>
                      <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div> -->
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

                <!-- Delete Modal -->
                <div id="deleteModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Delete Service</h4>
                      </div>
                      <div class="modal-body">
                        <h2 style="color:#7095d1;"><b>Are you sure you want to delete this Service?</b></h2>
                        <form method="post" action="{{ route('service.delete')}}">
                          {{ csrf_field() }}
                          <input type="hidden" name="serviceId" id="del_service_id">
                          <br>
                          
                          <input type="submit" name="" value="Deactivate" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </form>
                      </div>
                      <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div> -->
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

@endsection('modals')


@section('extrascripts')

    

    <!-- putting parameters in  edit modal -->
    <script>

      $('body').on('click', '.editCat', function(){
         
        var service_ID    =  $(this).attr('service_ID');
        var serviceName = $(this).attr('serviceName');
        var serviceAmount = $(this).attr('serviceAmount');
        var productID = $(this).attr('productID');
        var serviceID = $(this).attr('serviceID');
        var network = $(this).attr('network');
        var duration = $(this).attr('duration');
        var orgID = $(this).attr('orgID');

         //alert(service_ID);

        $('#modal_service_ID').val(service_ID);
        $('#modal_serviceName').val(serviceName);
        $('#modal_serviceAmount').val(serviceAmount);
        $('#modal_productID').val(productID);
        $('#modal_serviceID').val(serviceID);
        $('#modal_network').val(network);
        $('#modal_Duration').val(duration);
        $('#modal_orgID').val(orgID);
        
      });
    </script>

    <!-- putting parameter in delete modal -->
    <script>

      $('body').on('click', '.deleteCat', function(){
         
        var del_service_id    =  $(this).attr('serviceId');
       
        $('#del_service_id').val(del_service_id);
        
      });
    </script>

   

@endsection('extrascripts')