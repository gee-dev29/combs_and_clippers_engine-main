@extends('backend.layouts.master')

@section('content')

<div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <h1>User Profile</h1>
                    <ul>
                        <!-- <li><a href="#">Pages</a></li> -->
                        <!-- <li>User Profile</li> -->
                    </ul>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <div class="card user-profile o-hidden mb-4">
                    <div class="header-cover" style="background-image: url('backend/dist-assets/images/photo-wide-4.jpg')"></div>
                    <div class="user-info"><img class="profile-picture avatar-lg mb-2" src="{{ asset('backend/dist-assets/images/faces/find_user.png') }}" alt="" />
                        <p class="m-0 text-24">{{$user->firstName .' '. $user->lastName}}</p>
                        <p class="text-muted m-0">{{$user->job_title}}</p>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs profile-nav mb-4" id="profileTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">About</a></li>

                            <!-- <li class="nav-item"><a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline" role="tab" aria-controls="timeline" aria-selected="false">Timeline</a></li> -->
                            
                            <li class="nav-item"><a class="nav-link" id="friends-tab" data-toggle="tab" href="#friends" role="tab" aria-controls="friends" aria-selected="false">Accounts</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">Photos</a></li> -->
                        </ul>
                        <div class="tab-content" id="profileTabContent">
                            <div class="tab-pane fade active show" id="about" role="tabpanel" aria-labelledby="about-tab">
                                <h4>Personal Information</h4>
                                <p>
                                    @if(!is_null($user->myOrganization))
                                    
                                      {{$user->myOrganization->description}}
                                    @endif
                                    
                                </p>
                                <hr />
                                <div class="row">
                                    <div class="col-md-4 col-6">
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Calendar text-16 mr-1"></i> FirstName</p><span>{{$user->firstName}}</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Edit-Map text-16 mr-1"></i>LastName</p><span>{{$user->lastName}}</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Edit-Map text-16 mr-1"></i>Email</p><span>{{$user->email}}</span>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Globe text-16 mr-1"></i> Phone No</p><span>{{$user->phone_no}}</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-MaleFemale text-16 mr-1"></i>Is Super Admin</p><span>{{$user->super_admin}}</span>
                                        </div>
                                        @if(!is_null($user->myOrganization))
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Cloud-Weather text-16 mr-1"></i> Org. Sub accounts</p><span>{{$user->myOrganization->sub_user_count}}</span>
                                        </div>
                                        @endif
                                    </div>
                                    @if(!is_null($user->myOrganization))
                                    <div class="col-md-4 col-6">
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Globe text-16 mr-1"></i> Organization</p><span>{{$user->myOrganization->clientName}}</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-MaleFemale text-16 mr-1"></i>Org. Email</p><span>{{$user->myOrganization->business_email}}</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Cloud-Weather text-16 mr-1"></i> Address</p><span>{{$user->myOrganization->address}}</span>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- <div class="col-md-4 col-6">
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Face-Style-4 text-16 mr-1"></i> Profession</p><span>Digital Marketer</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Professor text-16 mr-1"></i> Experience</p><span>8 Years</span>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-primary mb-1"><i class="i-Home1 text-16 mr-1"></i> School</p><span>School of Digital Marketing</span>
                                        </div>
                                    </div> -->
                                </div>
                                <hr />
                                <h4>Other Info</h4>
                                <p class="mb-4">Here are the services your company offers</p>
                                <div class="row">
                                  @if(!is_null($user->myServices))
                                    @foreach($user->myServices as $service)
                                    <div class="col-md-2 col-sm-4 col-6 text-center">
                                        <p class="text-16 mt-1">{{$service->name}}</p>
                                    </div>
                                    @endforeach
                                  @endif
                                    <!-- <div class="col-md-2 col-sm-4 col-6 text-center"><i class="i-Camera text-32 text-primary"></i>
                                        <p class="text-16 mt-1">Photography</p>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 text-center"><i class="i-Car-3 text-32 text-primary"></i>
                                        <p class="text-16 mt-1">Driving</p>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 text-center"><i class="i-Gamepad-2 text-32 text-primary"></i>
                                        <p class="text-16 mt-1">Gaming</p>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 text-center"><i class="i-Music-Note-2 text-32 text-primary"></i>
                                        <p class="text-16 mt-1">Music</p>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 text-center"><i class="i-Shopping-Bag text-32 text-primary"></i>
                                        <p class="text-16 mt-1">Shopping</p>
                                    </div> -->
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="friends" role="tabpanel" aria-labelledby="friends-tab">
                                <div class="row">
                                  @if(!is_null($user->orgAccounts))
                                    @foreach($user->orgAccounts as $acct)
                                    <div class="col-md-3">
                                        <div class="card card-profile-1 mb-4">
                                            <div class="card-body text-center">
                                                <div class="avatar box-shadow-2 mb-3"><img src="../../dist-assets/images/faces/16.jpg" alt="" /></div>
                                                <h5 class="m-0">{{$acct->firstName .' '. $acct->lastName}}</h5>
                                                <p class="mt-0">{{$acct->job_title}}</p>
                                                <p>{{$acct->phone_no}}</p>
                                                <a href="mailto: {{$acct->email}}" class="btn btn-primary btn-rounded">Contact {{$acct->firstName}}</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                  @endif
                                   
                                </div>
                            </div>
                            <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/headphone-1.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/headphone-2.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/headphone-3.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/iphone-1.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/iphone-2.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/watch-1.jpg" alt="" />
                                            <div class="card-img-overlay">
                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i> 12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end of main-content -->

    @endsection('content')