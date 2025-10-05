@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible bg-success text-white border-0 fade show" role="alert">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    {{ $message }}
</div>
@endif
  
@if ($message = Session::get('error'))
<div class="alert alert-success alert-dismissible bg-danger text-white border-0 fade show" role="alert">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    {{ $message }}
</div>
@endif
   
@if ($message = Session::get('warning'))
<div class="alert alert-success alert-dismissible bg-warning text-white border-0 fade show" role="alert">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    {{ $message }}
</div>
@endif
   
@if ($message = Session::get('info'))
<div class="alert alert-success alert-dismissible bg-info text-white border-0 fade show" role="alert">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    {{ $message }}
</div>
@endif
  
@if ($errors->any())
<div class="alert alert-success alert-dismissible bg-danger text-white border-0 fade show" role="alert">
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif