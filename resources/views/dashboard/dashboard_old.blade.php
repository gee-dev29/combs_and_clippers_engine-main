@extends('layouts.app')
@section('content')
<style>
    .content {
        min-height: 120px
    }
</style>
<div class="main-content">
    <div class="breadcrumb">
        <h1>Dashboard</h1>
    </div>


    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <!-- ICON BG-->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Merchant']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Total Merchants</p>
                            <p class="text-primary text-24 line-height-1 mb-2"> {{ $totalMerchants }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Buyer']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Total Buyers</p>
                            <p class="text-primary text-24 line-height-1 mb-2"> {{ $totalBuyers }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Merchant']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Merchants last week</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $merchantLastWeek }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Merchant']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Merchants this week</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $merchantThisWeek }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Merchant']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Change in Merchants</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $percentChangeInMerchant }}%</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Buyer']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Buyers last week</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $buyerLastWeek }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Buyer']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Buyers this week</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $buyerThisWeek }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Buyer']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="i-Add-User"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Change in Buyers</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $percentChangeInBuyer }}%</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- ICON BG-->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Total orders</p>
                            <p class="text-primary text-24 line-height-1 mb-2"> {{ $totalorders }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-text-left"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content">
                            <p class="text-muted mt-2 mb-0">Last week's orders</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $lastWeekOrder }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">This week's orders</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $thisWeekOrders }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Change in orders</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $percentChangeOrder }}%</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- ICON BG-->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Total Revenue</p>
                            <p class="text-primary text-24 line-height-1 mb-2">
                                GBP{{0}}</p>
                            <p class="text-primary text-20 line-height-1 mb-2">shippingfee: GBP{{0}}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-text-left"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content">
                            <p class="text-muted mt-2 mb-0">Last week's Revenue</p>
                            <p class="text-primary text-24 line-height-1 mb-2">
                                GBP{{ 0 }}
                            </p>
                            <p class="text-primary text-20 line-height-1 mb-2">shippingfee:
                                GBP{{ 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">This week's Revenue</p>
                            <p class="text-primary text-24 line-height-1 mb-2">
                                GBP{{ 0 }}
                            </p>
                            <p class="text-primary text-20 line-height-1 mb-2">shippingfee:
                                GBP{{ 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('orders') }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"><i class="fa fa-bar-chart fa-3x text-primary"
                            aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Change in revenue</p>
                            <p class="text-primary text-24 line-height-1 mb-2">{{ $percentChangeInRevenue }}%</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- ICON BG-->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('customers', ['type' => 'Merchant']) }}">
                <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4">
                    <div class="card-body text-center"> <i class="i-Add-User  text-primary" aria-hidden="true"></i>
                        <div class="content text-left">
                            <p class="text-muted mt-2 mb-0">Vendors with Products</p>
                            <p class="text-primary text-24 line-height-1 mb-2"> {{ $vendorsWithProduct }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection