@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Blog Category </h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('blog.category.edit', ['id' => $id]) }}">
                            @include('dashboard.flash')
                            @csrf
                            <div class="form-group mb-4">
                                <label class="mb-0">Name</label>
                                <input name="name" class="form-control" id="name" type="text"
                                    placeholder="Trending" required 
                                    value="{{ $category ? $category->name : '' }}"
                                />
                            </div>
                            <br>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3"> Update</button>
                            </div>
                            <a class="btn btn-primary pl-4 pr-4 mb-3"
                                href="{{ url()->previous() }}"><i class="fa fa-long-arrow-left"
                                    aria-hidden="true"></i> Go back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Footer Start -->
    </div>
@endsection
