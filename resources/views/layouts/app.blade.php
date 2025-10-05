<!DOCTYPE html>
<html lang="en" dir="">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Admin console | {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/themes/lite-purple.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/plugins/perfect-scrollbar.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/jquery-3.3.1.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
        integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.debug.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.23/dist/jspdf.plugin.autotable.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('slimselect/slimselect.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('slimselect/slimselect.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="{{ asset('src/output.css') }}">
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
</head>
<style>
</style>

<body class="text-left">
    <div class="app-admin-wrap layout-sidebar-large">
        <div class="main-header text-white font-weight-bold" style="background: #10182a">

            <div class="logor">
                <img class="img-fluid ml-3" src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}">
            </div>
            <div class="menu-toggle text-white" style="color: #fff">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="d-flex align-items-center">


            </div>
            <div style="margin: auto"></div>
            <div class="header-part-right">


                <div class="dropdown">
                    <div class="user col align-self-end">

                        <span id="userDropdown" alt="" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"> <i class="i-Lock-User mr-1 text-white"></i>{{ Auth::user()->name
                            }}</span>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">

                            <a class="dropdown-item" href="{{ route('password') }}">Change password</a>
                            <a class="dropdown-item">
                                <form id="lgf" method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <label id="lg"> Sign out</label>
                                </form>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="side-content-wrap">
            <div class="sidebar-left open rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
                <ul class="navigation-left">
                    <li class="nav-item">
                        <a class="nav-item-hold" href="{{ route('dashboard') }}">
                            <i class="nav-icon i-Bar-Chart"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item has-submenu" data-item="appointments">
                        <a class="nav-item-hold" href="#"><i class="nav-icon fa fa-calendar"></i>
                            <span class="nav-text">Appointments</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item has-submenu" data-item="accounts">
                        <a class="nav-item-hold" href="#"><i class="nav-icon fa fa-user"></i>
                            <span class="nav-text">Accounts</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item has-submenu" data-item="payments">
                        <a class="nav-item-hold" href="#"><i class="nav-icon fa fa-money"></i>
                            <span class="nav-text">Payments</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item has-submenu" data-item="stores">
                        <a class="nav-item-hold" href="#"><i class="nav-icon fa fa-shopping-bag"></i>
                            <span class="nav-text">Stores</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-item-hold" href="{{ route('admin.reports.choose') }}"><i
                                class="nav-icon fa fa-bar-chart"></i>
                            <span class="nav-text">Reports</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item has-submenu" data-item="blog">
                        <a class="nav-item-hold" href="#"><i
                                class="nav-icon i-File-Horizontal-Text"></i>
                            <span class="nav-text">Blog</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-item-hold" href="{{ route('admin.management') }}"><i
                                class="nav-icon i-Administrator"></i>
                            <span class="nav-text">Admins</span>
                        </a>
                        <div class="triangle"></div>
                    </li>

                </ul>
            </div>

            <div class="sidebar-left-secondary rtl-ps-none" data-perfect-scrollbar="" data-suppress-scroll-x="true">
                <!-- Appointments submenu -->
                <ul class="childNav" data-parent="appointments">
                    <li class="nav-item">
                        <a href="{{ route('appointments') }}">
                            <i class="nav-icon fa fa-calendar-check-o"></i>
                            <span class="item-name">Manage Appointments</span>
                        </a>
                    </li>
                </ul>

                <!-- Accounts submenu -->
                <ul class="childNav" data-parent="accounts">
                    <li class="nav-item">
                        <a href="{{ route('accounts') }}">
                            <i class="nav-icon fa fa-users"></i>
                            <span class="item-name">Manage Accounts</span>
                        </a>
                    </li>
                </ul>

                <!-- Payments submenu -->
                <ul class="childNav" data-parent="payments">
                    <li class="nav-item">
                        <a href="{{ route('appointment.payments') }}">
                            <i class="nav-icon fa fa-credit-card"></i>
                            <span class="item-name">Appointment Payments</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('boothrent.payments') }}">
                            <i class="nav-icon fa fa-building"></i>
                            <span class="item-name">Booth Rent Payments</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('withdrawal.payments') }}">
                            <i class="nav-icon fa fa-exchange"></i>
                            <span class="item-name">Withdrawal Payments</span>
                        </a>
                    </li>
                </ul>

                <!-- Stores submenu -->
                <ul class="childNav" data-parent="stores">
                    <li class="nav-item">
                        <a href="{{ route('stores') }}">
                            <i class="nav-icon fa fa-store"></i>
                            <span class="item-name">Stores</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="{{ route('service.types') }}">
                            <i class="nav-icon fa fa-tools"></i>
                            <span class="item-name">Service Types</span>
                        </a>
                    </li>
                </ul>

                <!-- Blog submenu -->
                <ul class="childNav" data-parent="blog">
                    <li class="nav-item">
                        <a href="{{ route('blog.category.all') }}">
                            <i class="nav-icon i-Error-404-Window"></i>
                            <span class="item-name">Category Setup</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('blog.all') }}">
                            <i class="nav-icon i-File-Horizontal"></i>
                            <span class="item-name">Blog Setup</span>
                        </a>
                    </li>
                </ul>

            </div>

            <div class="sidebar-overlay"></div>
        </div>
        <!-- =============== Left side End ================-->
        <div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                @yield('content')
            </div><!-- Footer Start -->

        </div>
    </div><!-- ============ Search UI Start ============= -->


    <script src="{{ asset('js/plugins/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('js/scripts/script.min.js') }}"></script>
    <script src="{{ asset('js/scripts/sidebar.large.script.min.js') }}"></script>
    <script src="{{ asset('js/plugins/datatables.min.js') }}"></script>
    <script src="{{ asset('js/scripts/datatables.script.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $("#daterange").flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
            });
    </script>
    <script>
        // $(document).ready(function() {
            //     $('#zero_configuration_table').DataTable({
            //         "order": []
            //     });
            // });

            $("#lg").click(function() {
                $("#lgf").submit();
            })
    </script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 1500
            });
        @endif
    </script>

    @yield('additional_script')
</body>

</html>