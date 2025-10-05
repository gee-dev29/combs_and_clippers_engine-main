@extends('backend.layouts.master')

@section('content')



<div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <!-- <h1 class="mr-2">Analytics</h1> -->
                    <ul>
                        <li>Analytics</li>
                       <!--  <li>Version 3</li> -->
                    </ul>
                <form method="post" action="{{ route('report.fetch')}}">
                                          {{ csrf_field() }}
                    <div class="col-md-2">
                        <select  name="report_type"  class="form-control" required="" id="">
                              <option value="">Report Type</option>
                              <option value="subscription">Subscription</option>
                              <option value="unsubscription">Unsubscription</option>
                        </select>
                    </div>
                    
                    
                    <div class="col-md-2 mt-3 mt-md-0">
                        <select  name="client"  class="form-control" required="" id="">
                              <option value="">Select Partner</option>
                              @if(!is_null($orgs))
                                  @foreach($orgs as $org)
                                  <option value="{{$org->id}}">{{$org->clientName}}</option>
                                  @endforeach
                              @endif
                              
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
                             <input type='text' placeholder="To Date" name="to_date" class="form-control"   required="" readonly="" />
                             <span class="input-group-addon">
                             <span class="glyphicon glyphicon-calendar"></span>
                             </span>
                          </div>
                    </div>
                    <div class="col-md-2 mt-3 mt-md-0">
                        <button class="btn btn-primary btn-block">Filter</button>
                    </div>
                </form>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <div class="row">
                    <!-- no 13 chart-->
                    <div class="col-md-3 col-lg-3">
                        <div class="card mb-4 o-hidden">
                            <div class="card-body">
                                <div class="ul-widget__row-v2">
                                    <div id="chart13"></div>
                                    <div class="ul-widget__content-v2">
                                        <h4 class="heading mt-3">{{number_format($sub_count)}}</h4><small class="text-muted m-0">{{number_format($year_long_sub->avg('count'),2)}} Avg Sub this Year</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- no 14 chart-->
                    <div class="col-md-3 col-lg-3">
                        <div class="card mb-4 o-hidden">
                            <div class="card-body">
                                <div class="ul-widget__row-v2">
                                    <div id="chart14"></div>
                                    <div class="ul-widget__content-v2">
                                        <h4 class="heading mt-3">{{number_format($unsub_count)}}</h4><small class="text-muted m-0">{{number_format($year_long_unsub->avg('count'), 2)}} Avg Unsub this Year</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- no 15 chart-->
                    <div class="col-md-3 col-lg-3">
                        <div class="card mb-4 o-hidden">
                            <div class="card-body">
                                <div class="ul-widget__row-v2">
                                    <div id="chart15"></div>
                                    <div class="ul-widget__content-v2">
                                        <h4 class="heading mt-3">₦{{number_format($year_revenue)}}</h4><small class="text-muted m-0">₦{{number_format($year_long_revenue->avg('revenue'),2)}} Avg Rev. this Year</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- no 16 chart-->
                    <div class="col-md-3 col-lg-3">
                        <div class="card mb-4 o-hidden">
                            <div class="card-body">
                                <div class="ul-widget__row-v2">
                                    <div id="chart16"></div>
                                    <div class="ul-widget__content-v2">
                                        <h4 class="heading mt-3">₦{{number_format($week_long_rev->sum('revenue'))}}</h4><small class="text-muted m-0">₦{{number_format($week_long_rev->avg('revenue'))}} Avg Rev this Week</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- left-side-->

                    <div class="col-lg-6 col-md-12">
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">Last Month Revenue</div>
                                    <p class="mb-4 text-primary text-24">₦{{number_format($last_month_revenue)}}</p>
                                </div>
                               <!--  <div id="echart1" style="height: 260px;"></div> -->
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">Last Week Revenue</div>
                                    <p class="mb-4 text-warning text-24">₦{{number_format($last_week_revenue)}}</p>
                                </div>
                               <!--  <div id="echart2" style="height: 260px;"></div> -->
                            </div>
                        </div>
                       
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">This Month Revenue</div>
                                    <p class="mb-4 text-primary text-24">₦{{number_format($month_revenue)}}</p>
                                </div>
                               <!--  <div id="echart1" style="height: 260px;"></div> -->
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">This Week Revenue</div>
                                    <p class="mb-4 text-warning text-24">₦{{number_format($week_long_rev->sum('revenue'))}}</p>
                                </div>
                               <!--  <div id="echart2" style="height: 260px;"></div> -->
                            </div>
                        </div>
                       
                    </div>
                </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="card-title">Current Year's Revenue Report</div>
                                <div id="columnDataLabel"></div>
                            </div>
                        </div>
                    </div>
                    <!-- right-side-->
                    <div class="col-lg-6 col-md-12">
                        <div class="row">
                            <!-- <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="card-title"> Last & Current Year Subscription</div>
                                        <div id="basicColumn-chart"></div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="card-title">Last Week Sub/Revenue</div>
                                        <div id="multiLine" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                           
                            <!-- <div class="col-md-12 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-2 text-muted">Project Completion Rate</h6>
                                        <p class="text-22 font-weight-light mb-1"><i class="i-Up text-success"></i> 15%</p>
                                        <div id="echart9" style="height: 60px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="mb-2 text-muted">Project Completion Rate</h6>
                                        <p class="text-22 font-weight-light mb-1"><i class="i-Down text-danger"></i> 15%</p>
                                        <div id="echart10" style="height: 60px;"></div>
                                    </div>
                                </div>
                            </div> -->
                           
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                </div>
                

                <!-- end of main-content -->
            </div>

            <div class="flex-grow-1"></div>

            <div class="row">


                <!-- <div class="col-lg-6 col-md-12">
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">Last Month Sales</div>
                                    <p class="mb-4 text-primary text-24">$40250</p>
                                </div>
                                <div id="echart1" style="height: 260px;"></div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="card card-chart-bottom o-hidden mb-4">
                                <div class="card-body">
                                    <div class="text-muted">Last Week Sales</div>
                                    <p class="mb-4 text-warning text-24">$10250</p>
                                </div>
                                <div id="echart2" style="height: 260px;"></div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <h5 class="card-title m-0 p-3">Last 20 Day Subscription Distribution</h5>
                            <div id="echart3" style="height: 360px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

 @endsection('content')
 @section('scripts')
    <input type="text" id="subscription" hidden="" value="{{json_encode($year_long_sub->pluck('count')->toArray())}}" name="">

    <input type="text" id="last_year_sub" hidden="" value="{{json_encode($last_year_long_sub->pluck('count')->toArray())}}" name="">
    
    <input type="text" id="unsubscription" hidden="" value="{{json_encode($year_long_unsub->pluck('count')->toArray())}}" name="">
    <input type="text" id="monthname" hidden="" value="{{json_encode($year_long_sub->pluck('monthname')->toArray())}}" name="">
    <input type="text" id="monthnum" hidden="" value="{{json_encode($year_long_sub->pluck('month')->toArray())}}" name="">

    <input type="text" id="rev_monthnum" hidden="" value="{{json_encode($year_long_revenue->pluck('month')->toArray())}}" name="">
    <input type="text" id="revenue" hidden="" value="{{json_encode($year_long_revenue->pluck('revenue')->toArray())}}" name="">
    <input type="text" id="revenue_sum" hidden="" value="{{$year_long_revenue->sum('revenue')}}" name="">
    
    <input type="text" id="week_revenue" hidden="" value="{{json_encode($week_long_rev->pluck('revenue')->toArray())}}" name="">
    <input type="text" id="week_sub" hidden="" value="{{json_encode($week_long_rev->pluck('count')->toArray())}}" name="">
    <input type="text" id="week_day" hidden="" value="{{json_encode($week_long_rev->pluck('day')->toArray())}}" name="">

    <input type="text" id="last_week_revenue" hidden="" value="{{json_encode($last_week_long_rev->pluck('revenue')->toArray())}}" name="">
    <input type="text" id="last_week_sub" hidden="" value="{{json_encode($last_week_long_rev->pluck('count')->toArray())}}" name="">
    <input type="text" id="last_week_dayname" hidden="" value="{{json_encode($last_week_long_rev->pluck('dayname')->toArray())}}" name="">
    
    <input type="text" id="last_twenty_days" hidden="" value="{{json_encode($last_twenty_days->pluck('count')->toArray())}}" name="">
    
 @endsection('scripts')

 @section('extrascripts')
    <script src="{{ asset('backend/dist-assets/js/scripts/echarts.script.min.js') }}"></script>

    <script src="{{ asset('backend/dist-assets/js/scripts/widgets-statistics.min.js') }}"></script>
    <script src="{{ asset('backend/dist-assets/js/scripts/apexColumnChart.script.min.js') }}"></script>

    <script src="{{ asset('backend/dist-assets/js/scripts/echarts.script.min.js') }}"></script>
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