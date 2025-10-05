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

@if (session()->has('message'))
    <div class="text-center alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
