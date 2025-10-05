@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>{{ $admin->get('name') }}</h3>
        <table class="table table-bordered">

            <tr>
                <td>Name</td>
                <td>{{ $admin->get('name') }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $admin->get('email') }}</td>
            </tr>
            <tr>
                <td>Accoun type</td>
                <td>{{ $admin->get('accounttype') }}</td>
            </tr>
            <tr>
                <td>Role</td>
                <td>{{ $admin->get('role') }}</td>
            </tr>

        </table>
        <a href="{{ route('admin.manage') }}" class="btn btn-primary px-5">Go back</a>
    </div>
@endsection
