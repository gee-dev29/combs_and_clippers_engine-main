<!DOCTYPE html>
<html lang="en" dir="">

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Pepperst Escrow | {{ $pageTitle or null }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
    <link href="{{ asset('backend/dist-assets/css/themes/lite-purple.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link href="{{ asset('backend/dist-assets/css/plugins/perfect-scrollbar.min.css') }}" rel="stylesheet" />
</head>

<body class="text-left">
    <div class="app-admin-wrap layout-sidebar-large">
        <div class="main-header">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="">
            </div>
            <div class="menu-toggle">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <!-- <div class="d-flex align-items-center">
                
                <div class="search-bar">
                    <input type="text" placeholder="Search">
                    <i class="search-icon text-muted i-Magnifi-Glass1"></i>
                </div>
            </div> -->
            <div style="margin: auto"></div>
            <div class="header-part-right">
                <!-- Full screen toggle -->
                <i class="i-Full-Screen header-icon d-none d-sm-inline-block" data-fullscreen></i>
               
                <!-- User avatar dropdown -->
                <div class="dropdown">
                    <div class="user col align-self-end">
                        <img src="{{ asset('backend/dist-assets/images/faces/find_user.png') }}" id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        @if(Auth::check())
                            <div class="dropdown-header">
                                <i class="i-Lock-User mr-1"></i> {{Auth::user()->name}}
                            </div>
                            <a class="dropdown-item" href="{{route('escrow.profile')}}">Account settings</a>
                           
                            <a class="dropdown-item" href="{{route('escrow.logout')}}">Sign out</a>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="side-content-wrap">
            <div class="sidebar-left open rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
                <ul class="navigation-left">
                    <li class="nav-item {{ $dashboardActive or null }}" ><a class="nav-item-hold" href="{{route('escrow.dashboard')}}"><i class="nav-icon i-Bar-Chart"></i><span class="nav-text">Dashboard</span></a>
                        <div class="triangle"></div>
                    </li>
                    {{--
                   
                    <li class="nav-item {{ $serviceActive or null }}" ><a class="nav-item-hold" href="{{route('escrow.services')}}"><i class="nav-icon i-Computer-Secure"></i><span class="nav-text">Services</span></a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item {{ $reportActive or null }}"><a class="nav-item-hold" href="{{route('escrow.reports')}}"><i class="i-Financial"></i><span class="nav-text">Reports</span></a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item {{ $profileActive or null }}"><a class="nav-item-hold" href="{{route('escrow.profile')}}"><i class="nav-icon i-Administrator"></i><span class="nav-text">User Profile</span></a>
                        <div class="triangle"></div>
                    </li>
                    --}}
                    
                    
                    @if(Auth::user()->account_type == 'super_admin')

                    <li class="nav-item {{ $usersActive or null }}"><a class="nav-item-hold" href="{{route('escrow.users')}}"><i class="nav-icon i-Add-User"></i><span class="nav-text">Users Mgt</span></a>
                        <div class="triangle"></div>
                    </li>
                    @endif
                    <li class="nav-item" ><a class="nav-item-hold" href="{{route('escrow.logout')}}"><i class="nav-icon i-Double-Tap"></i><span class="nav-text">Sign Out</span></a>
                        <div class="triangle"></div>
                    </li>
                    
                </ul>
            </div>
            <div class="sidebar-left-secondary rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
                <!-- Submenu Dashboards-->
               
                <!-- chartjs-->
                <ul class="childNav" data-parent="charts">
                    <li class="nav-item"><a href="charts.echarts.html"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">echarts</span></a></li>
                    <li class="nav-item"><a href="charts.chartsjs.html"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">ChartJs</span></a></li>
                    <li class="nav-item dropdown-sidemenu"><a href="#"><i class="nav-icon i-File-Clipboard-Text--Image"></i><span class="item-name">Apex Charts</span><i class="dd-arrow i-Arrow-Down"></i></a>
                        <ul class="submenu">
                            <li><a href="charts.apexAreaCharts.html">Area Charts</a></li>
                            <li><a href="charts.apexBarCharts.html">Bar Charts</a></li>
                            <li><a href="charts.apexBubbleCharts.html">Bubble Charts</a></li>
                            <li><a href="charts.apexColumnCharts.html">Column Charts</a></li>
                            <li><a href="charts.apexCandleStickCharts.html">CandleStick Charts</a></li>
                            <li><a href="charts.apexLineCharts.html">Line Charts</a></li>
                            <li><a href="charts.apexMixCharts.html">Mix Charts</a></li>
                            <li><a href="charts.apexPieDonutCharts.html">PieDonut Charts</a></li>
                            <li><a href="charts.apexRadarCharts.html">Radar Charts</a></li>
                            <li><a href="charts.apexRadialBarCharts.html">RadialBar Charts</a></li>
                            <li><a href="charts.apexScatterCharts.html">Scatter Charts</a></li>
                            <li><a href="charts.apexSparklineCharts.html">Sparkline Charts</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="childNav" data-parent="extrakits">
                    <li class="nav-item"><a href="image.cropper.html"><i class="nav-icon i-Crop-2"></i><span class="item-name">Image Cropper</span></a></li>
                    <li class="nav-item"><a href="loaders.html"><i class="nav-icon i-Loading-3"></i><span class="item-name">Loaders</span></a></li>
                    <li class="nav-item"><a href="ladda.button.html"><i class="nav-icon i-Loading-2"></i><span class="item-name">Ladda Buttons</span></a></li>
                    <li class="nav-item"><a href="toastr.html"><i class="nav-icon i-Bell"></i><span class="item-name">Toastr</span></a></li>
                    <li class="nav-item"><a href="sweet.alerts.html"><i class="nav-icon i-Approved-Window"></i><span class="item-name">Sweet Alerts</span></a></li>
                    <li class="nav-item"><a href="tour.html"><i class="nav-icon i-Plane"></i><span class="item-name">User Tour</span></a></li>
                    <li class="nav-item"><a href="upload.html"><i class="nav-icon i-Data-Upload"></i><span class="item-name">Upload</span></a></li>
                </ul>
                <ul class="childNav" data-parent="uikits">
                    <li class="nav-item"><a href="alerts.html"><i class="nav-icon i-Bell1"></i><span class="item-name">Alerts</span></a></li>
                    <li class="nav-item"><a href="accordion.html"><i class="nav-icon i-Split-Horizontal-2-Window"></i><span class="item-name">Accordion</span></a></li>
                    <li class="nav-item"><a href="badges.html"><i class="nav-icon i-Medal-2"></i><span class="item-name">Badges</span></a></li>
                    <li class="nav-item"><a href="buttons.html"><i class="nav-icon i-Cursor-Click"></i><span class="item-name">Buttons</span></a></li>
                    <li class="nav-item"><a href="cards.html"><i class="nav-icon i-Line-Chart-2"></i><span class="item-name">Cards</span></a></li>
                    <li class="nav-item"><a href="card.metrics.html"><i class="nav-icon i-ID-Card"></i><span class="item-name">Card Metrics</span></a></li>
                    <li class="nav-item"><a href="carousel.html"><i class="nav-icon i-Video-Photographer"></i><span class="item-name">Carousels</span></a></li>
                    <li class="nav-item"><a href="lists.html"><i class="nav-icon i-Belt-3"></i><span class="item-name">Lists</span></a></li>
                    <li class="nav-item"><a href="pagination.html"><i class="nav-icon i-Arrow-Next"></i><span class="item-name">Paginations</span></a></li>
                    <li class="nav-item"><a href="popover.html"><i class="nav-icon i-Speach-Bubble-2"></i><span class="item-name">Popover</span></a></li>
                    <li class="nav-item"><a href="progressbar.html"><i class="nav-icon i-Loading"></i><span class="item-name">Progressbar</span></a></li>
                    <li class="nav-item"><a href="tables.html"><i class="nav-icon i-File-Horizontal-Text"></i><span class="item-name">Tables</span></a></li>
                    <li class="nav-item"><a href="tabs.html"><i class="nav-icon i-New-Tab"></i><span class="item-name">Tabs</span></a></li>
                    <li class="nav-item"><a href="tooltip.html"><i class="nav-icon i-Speach-Bubble-8"></i><span class="item-name">Tooltip</span></a></li>
                    <li class="nav-item"><a href="modals.html"><i class="nav-icon i-Duplicate-Window"></i><span class="item-name">Modals</span></a></li>
                    <li class="nav-item"><a href="nouislider.html"><i class="nav-icon i-Width-Window"></i><span class="item-name">Sliders</span></a></li>
                </ul>
                <ul class="childNav" data-parent="sessions">
                    <li class="nav-item"><a href="http://demos.ui-lib.com/gull/html/sessions/signin.html"><i class="nav-icon i-Checked-User"></i><span class="item-name">Sign in</span></a></li>
                    <li class="nav-item"><a href="http://demos.ui-lib.com/gull/html/sessions/signup.html"><i class="nav-icon i-Add-User"></i><span class="item-name">Sign up</span></a></li>
                    <li class="nav-item"><a href="http://demos.ui-lib.com/gull/html/sessions/forgot.html"><i class="nav-icon i-Find-User"></i><span class="item-name">Forgot</span></a></li>
                </ul>
                <ul class="childNav" data-parent="others">
                    <li class="nav-item"><a href="http://demos.ui-lib.com/gull/html/sessions/not-found.html"><i class="nav-icon i-Error-404-Window"></i><span class="item-name">Not Found</span></a></li>
                    <li class="nav-item"><a href="user.profile.html"><i class="nav-icon i-Male"></i><span class="item-name">User Profile</span></a></li>
                    <li class="nav-item"><a class="open" href="blank.html"><i class="nav-icon i-File-Horizontal"></i><span class="item-name">Blank Page</span></a></li>
                </ul>
            </div>
            <div class="sidebar-overlay"></div>
        </div>
        <!-- =============== Left side End ================-->
        

        @yield('content')



    </div>

    <!-- ============ Search UI Start ============= -->
    <div class="search-ui">
        <div class="search-header">
            <img src="{{ asset('backend/dist-assets/images/logo.png') }}" alt="" class="logo">
            <button class="search-close btn btn-icon bg-transparent float-right mt-2">
                <i class="i-Close-Window text-22 text-muted"></i>
            </button>
        </div>
        <input type="text" placeholder="Type here" class="search-input" autofocus>
        <div class="search-title">
            <span class="text-muted">Search results</span>
        </div>
        <div class="search-results list-horizontal">
            <div class="list-item col-md-12 p-0">
                <div class="card o-hidden flex-row mb-4 d-flex">
                    <div class="list-thumb d-flex">
                        <!-- TUMBNAIL -->
                        <img src="../../dist-assets/images/products/headphone-1.jpg" alt="">
                    </div>
                    <div class="flex-grow-1 pl-2 d-flex">
                        <div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row">
                            <!-- OTHER DATA -->
                            <a href="#" class="w-40 w-sm-100">
                                <div class="item-title">Headphone 1</div>
                            </a>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">$300
                                <del class="text-secondary">$400</del>
                            </p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges">
                                <span class="badge badge-danger">Sale</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-item col-md-12 p-0">
                <div class="card o-hidden flex-row mb-4 d-flex">
                    <div class="list-thumb d-flex">
                        <!-- TUMBNAIL -->
                        <img src="../../dist-assets/images/products/headphone-2.jpg" alt="">
                    </div>
                    <div class="flex-grow-1 pl-2 d-flex">
                        <div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row">
                            <!-- OTHER DATA -->
                            <a href="#" class="w-40 w-sm-100">
                                <div class="item-title">Headphone 1</div>
                            </a>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">$300
                                <del class="text-secondary">$400</del>
                            </p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges">
                                <span class="badge badge-primary">New</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-item col-md-12 p-0">
                <div class="card o-hidden flex-row mb-4 d-flex">
                    <div class="list-thumb d-flex">
                        <!-- TUMBNAIL -->
                        <img src="../../dist-assets/images/products/headphone-3.jpg" alt="">
                    </div>
                    <div class="flex-grow-1 pl-2 d-flex">
                        <div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row">
                            <!-- OTHER DATA -->
                            <a href="#" class="w-40 w-sm-100">
                                <div class="item-title">Headphone 1</div>
                            </a>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">$300
                                <del class="text-secondary">$400</del>
                            </p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges">
                                <span class="badge badge-primary">New</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-item col-md-12 p-0">
                <div class="card o-hidden flex-row mb-4 d-flex">
                    <div class="list-thumb d-flex">
                        <!-- TUMBNAIL -->
                        <img src="../../dist-assets/images/products/headphone-4.jpg" alt="">
                    </div>
                    <div class="flex-grow-1 pl-2 d-flex">
                        <div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center flex-lg-row">
                            <!-- OTHER DATA -->
                            <a href="#" class="w-40 w-sm-100">
                                <div class="item-title">Headphone 1</div>
                            </a>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">Gadget</p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100">$300
                                <del class="text-secondary">$400</del>
                            </p>
                            <p class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges">
                                <span class="badge badge-primary">New</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- PAGINATION CONTROL -->
        <div class="col-md-12 mt-5 text-center">
            <nav aria-label="Page navigation example">
                <ul class="pagination d-inline-flex">
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <!-- ============ Search UI End ============= -->

     @yield('modals')

     @yield('scripts')

    <script src="{{ asset('backend/dist-assets/js/plugins/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/plugins/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/scripts/script.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/scripts/sidebar.large.script.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/plugins/apexcharts.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/scripts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/plugins/echarts.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/scripts/echart.options.min.js') }}"></script>

    
    <script src="{{ asset('backend/dist-assets/js/scripts/dashboard.v1.script.min.js') }}"></script>

    <script src="{{ asset('backend/assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/bootstrap-datepicker.js') }}"></script>
    
    @yield('extrascripts')
    <!-- <script src="{{ asset('backend/dist-assets/js/scripts/card.metrics.script.min.js') }}"></script> -->
   
    
</body>


<!-- Mirrored from demos.ui-lib.com/gull/html/layout1/dashboard1.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 27 Sep 2021 20:55:41 GMT -->
</html>