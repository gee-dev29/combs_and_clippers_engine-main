@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-4 mx-auto">

            <div class="main-content">
                <div class="breadcrumb">
                    <h1>Reset my password</h1>

                </div>
                <div class="separator-breadcrumb border-top"></div>

                <form action="{{ route('admin.password.update') }}" method="post">
                    @include('flash')
                    @csrf
                    <div class="form-group">
                        <label class="mb-0">Current password</label>
                        <input type="password" name="current_password" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="mb-0">New password</label>
                        <input type="password" name="password" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="mb-0">Confirm New password</label>
                        <input type="password" name="password_confirmation" required class="form-control">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary pl-3 pr-3 btn-block">Save new password</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- ============ Search UI Start ============= -->


    </div>
    </div>
@endsection
