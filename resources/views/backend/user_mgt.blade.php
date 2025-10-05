@extends('backend.layouts.master')

@section('content')

<div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <h1>Users</h1>
                    <ul>
                        <li><a href="#">Users Mgt</a></li>
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
                                <h3 class="w-50 float-left card-title m-0">User Accounts</h3>
                                <div class="dropdown dropleft text-right w-50 float-right">
                                    <button class="btn bg-gray-100" id="dropdownMenuButton1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="nav-icon i-Gear-2"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton1"><a class="dropdown-item" href="#" data-toggle="modal" data-target="#newModal">Add new user</a>
                                        <!-- <a class="dropdown-item" href="#">View All users</a><a class="dropdown-item" href="#">Something else here</a> -->
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="table-responsive">
                                    <table class="table text-center" id="user_table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Phone No</th>
                                                
                                                <th scope="col">Job Title</th>
                                                
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(count($users) > 0)
                                                @foreach($users as $user)
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->phone }}</td>
                                                    
                                                    <td>{{ $user->job_title }}</td>

                                                    <td><a class="text-success mr-2 editUser" href="#" data-toggle="modal" data-target="#editModal" title="Edit" userID="{{ $user->id }}" sAdmin="{{ $user->account_type }}" fName="{{ $user->firstName }}" lName="{{ $user->lastName }}" userEmail="{{ $user->email }}" jobTitle="{{ $user->job_title }}" orgID="{{$user->app_id}}" userPhone="{{ $user->phone_no }}"><i class="nav-icon i-Pen-2 font-weight-bold"></i></a><a class="text-danger mr-2 deleteUser" href="#" data-toggle="modal" data-target="#deleteModal" userId="{{ $user->id }}" title="Delete"><i class="nav-icon i-Close-Window font-weight-bold"></i></a></td>
                                                    
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
                                {{$users->links()}}
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
                        <h4 class="modal-title">Create New Account</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    
                      </div>
                      <div class="modal-body">
                        <p>Add a new User</p>
                        <form method="post" action="{{ route('escrow.user.save')}}" enctype="multipart/form-data">
                          {{ csrf_field() }}

                          <div class="container">
                              <label class="">First Name</label>
                              <input type="text" name="fname" class="form-control labeler" placeholder="First Name" required="">
                              
                              @if ($errors->has('fname'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('fname') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Last Name</label>
                              <input type="text" name="lname" class="form-control labeler" placeholder="Last Name" required="">
                              @if ($errors->has('lname'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('lname') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Email Addr</label>
                              <input type="email" name="email" class="form-control labeler" placeholder="e.g name@gmail.com" required="">
                              
                              @if ($errors->has('email'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('email') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Password</label>
                              <input type="password" name="password" class="form-control labeler" placeholder="password" required="">
                              @if ($errors->has('password'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('password') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Phone No.</label>
                              <input type='number' name="phone_no" class="form-control labeler"   placeholder="e.g 08030028000" />
                              
                              @if ($errors->has('phone_no'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('phone_no') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Job Title</label>
                              <input type="text" name="job_title" class="form-control labeler" placeholder="password" required="">
                              @if ($errors->has('job_title'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('job_title') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Super Admin</label>
                              <select  name="is_super_admin" class="form-control labeler" required="">
                                  <option value="admin">No</option>
                                  <option value="super_admin">Yes</option>
                              </select>
                              
                              @if ($errors->has('is_super_admin'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('is_super_admin') }}</strong>
                                  </span>
                              @endif
                            
                            
                            <span class="spaner">
                              <label class="">Client App</label>
                              <select  name="app_id"  class="labeler form-control" required="">
                                  <option value="">Select Client App</option>
                                @if(!is_null($client_apps))
                                  @foreach($client_apps as $client_app)
                                  <option value="{{$client_app->app_id}}">{{$client_app->app_name}}</option>
                                  @endforeach
                                @endif
                                
                              </select>
                            </span>
                            {{----}}
                            
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
                        <h4 class="modal-title">Edit User</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        
                      </div>
                      <div class="modal-body">
                        <p>Modify User Info.</p>
                        <form method="post" action="{{ route('escrow.user.edit')}}" enctype="multipart/form-data">
                          {{ csrf_field() }}
                          <input type="number" hidden="" name="userID" id="modal_user_id">
                          <div class="container">
                              <label class="">First Name</label>
                              <input type="text" name="fname" class="form-control labeler" placeholder="First Name" id="modal_user_fname" required="">
                              
                              @if ($errors->has('fname'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('fname') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Last Name</label>
                              <input type="text" name="lname" class="form-control labeler" placeholder="Last Name" id="modal_user_lname" required="">
                              @if ($errors->has('lname'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('lname') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Email Addr</label>
                              <input type="email" name="email" class="form-control labeler" placeholder="e.g name@gmail.com" id="modal_user_email" required="">
                              
                              @if ($errors->has('email'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('email') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Password</label>
                              <input type="password" name="password" class="form-control labeler" placeholder="password" >
                              @if ($errors->has('password'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('password') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Phone No.</label>
                              <input type='number' name="phone_no" class="form-control labeler" id="modal_user_phone"   placeholder="e.g 08030028000" />
                              
                              @if ($errors->has('phone_no'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('phone_no') }}</strong>
                                  </span>
                              @endif
                            

                            <span class="spaner">
                              <label class="">Job Title</label>
                              <input type="text" name="job_title" class="form-control labeler" placeholder="password" id="modal_user_jtitle" required="">
                              @if ($errors->has('job_title'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('job_title') }}</strong>
                                  </span>
                              @endif
                            </span>
                            
                          </div>

                          <div class="container">
                              <label class="">Super Admin</label>
                              
                              <select  name="is_super_admin" class="form-control selector" id="modal_user_sAdmin" required="">
                                   <option value="admin">No</option>
                                  <option value="super_admin">Yes</option>
                              </select>
                              
                              @if ($errors->has('is_super_admin'))
                                  <span class="text-danger">
                                      <strong>{{ $errors->first('is_super_admin') }}</strong>
                                  </span>
                              @endif
                            
                           
                            <span class="spaner">
                              <label class="">Client App</label>
                              <select  name="app_id"  class="labeler form-control" required="" id="modal_user_orgID">
                                  <option value="">Select Client App</option>
                                @if(!is_null($client_apps))
                                  @foreach($client_apps as $client_app)
                                  <option value="{{$client_app->app_id}}">{{$client_app->app_name}}</option>
                                  @endforeach
                                @endif
                                
                              </select>
                            </span>
                             {{----}}
                            
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
                        <h4 class="modal-title">Delete User</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        
                      </div>
                      <div class="modal-body">
                        <h2 style="color:#7095d1;"><b>Are you sure you want delete this User?</b></h2>
                        <form method="post" action="{{ route('escrow.user.delete')}}">
                          {{ csrf_field() }}
                          <input type="hidden" name="user_id" id="del_user_id">
                          <br>
                          
                          <input type="submit" name="" value="Delete" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </form>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

                <!-- Block Modal -->
                <div id="blockModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Block User</h4>
                      </div>
                      <div class="modal-body">
                        <h2 style="color:#7095d1;"><b>Are you sure you want block/unblock this User?</b></h2>
                        <form method="post" action="{{ route('escrow.user.block')}}">
                          {{ csrf_field() }}
                          <input type="hidden" name="userID" id="block_user_id">
                          <br>
                          
                          <input type="submit" name="" value="Block/Unblock" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </form>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- end Modal -->


@endsection('modals')

@section('extrascripts')



    <!-- putting parameters in  edit modal -->
    <script>

      $('body').on('click', '.editUser', function(){
         
        var userID    =  $(this).attr('userID');
        var fName = $(this).attr('fName');
        var lName = $(this).attr('lName');
        var userEmail = $(this).attr('userEmail');
        var jobTitle = $(this).attr('jobTitle');
        var orgID = $(this).attr('orgID');
        var userPhone = $(this).attr('userPhone');
        var super_admin = $(this).attr('sAdmin');

        

        $('#modal_user_id').val(userID);
        $('#modal_user_fname').val(fName);
        $('#modal_user_lname').val(lName);
        $('#modal_user_email').val(userEmail);
        $('#modal_user_jtitle').val(jobTitle);
        $('#modal_user_phone').val(userPhone);
        $('#modal_user_orgID').val(orgID);
        $('#modal_user_sAdmin').val(super_admin);
        
      });
    </script>

    <!-- putting parameter in delete modal -->
    <script>

      $('body').on('click', '.deleteUser', function(){
         
        var del_user_id    =  $(this).attr('userId');
        //alert(del_user_id);
        $('#del_user_id').val(del_user_id);
        
      });
    </script>

    <!-- putting parameter in block modal -->
    <script>

      $('body').on('click', '.blockUser', function(){
         
        var del_user_id    =  $(this).attr('userid');
       
        $('#block_user_id').val(del_user_id);
        
      });
    </script>

     <script >
      
        $("#upload1").on('change', function() {
            readUrl1();
        });
        function readUrl1 (){
            var file1 = $("#upload1")[0].files[0];
            var reader = new FileReader();
            reader.onloadend = function (){
                $("#picture_preview1").attr("src", reader.result);
            }
            if(file1){
                reader.readAsDataURL(file1);
            }
        }
  
    </script> 

@endsection('extrascripts')