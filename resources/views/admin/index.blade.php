@extends('layouts.app')
@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Admin Users</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('admin.post') }}">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session()->has('success'))
                                <div class="alert alert-success text-center">
                                    {{ session()->get('success') }}
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="alert alert-danger text-center">
                                    {{ session()->get('error') }}
                                </div>
                            @endif

                            @csrf
                            <div class="form-group mb-4">
                                <label class="mb-0">Name</label>
                                <input name="name" value="{{ old('name') }}" class="form-control" id="name"
                                    type="text" placeholder="Arya Stark" />


                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Account type</label>
                                <select required name="accountType" class="custom-select">
                                    <option value="" selected>--</option>
                                    <option value="Admin">Admin</option>
                                    {{-- <option value="User">User</option> --}}
                                </select>
                            </div>
                            <label class="font-weight-bold mb-0">Access Control</label>
                            <div class="list-group-item rounded mb-4">
                                <div class="form-row">
                                    @foreach ($access as $item)
                                        <div class="col-md-4">
                                            <div class="form-group form-check">
                                                <input name="access[{{ $item->name }}]" type="checkbox"
                                                    class="form-check-input" id="{{ $item->name }}Check">
                                                <label class="form-check-label text-capitalize"
                                                    for="{{ $item->name }}Check">{{ $item->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Email address</label>
                                <input name="email" value="{{ old('email') }}" class="form-control" id="email"
                                    type="text" placeholder="aryastark@pepperest.com" />

                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Create Account</button>
                            </div>

                            <a class="mt-5" href="{{ route('admin.manage') }}">Manage admins</a>

                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div><!-- Footer Start -->


    </div>
@endsection
