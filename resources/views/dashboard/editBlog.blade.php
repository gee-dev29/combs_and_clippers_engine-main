@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h3>Blog </h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('blog.edit', ['id' => $id]) }}">
                            @include('dashboard.flash')
                            @csrf

                            <div class="form-group mb-4">
                                <label class="mb-0">Title</label>
                                <input name="title" class="form-control" id="title" type="text"
                                    placeholder="title" required 
                                    value="{{ $blog ? $blog->title : '' }}"
                                />
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Excerpt</label>
                                <textarea class="form-control" id="excerpt" rows="10" name="excerpt"
                                    placeholder="Enter blog excerpt" required>
                                    {{ $blog ? $blog->excerpt : '' }}
                                </textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Full Description</label>
                                <textarea class="form-control" id="description" rows="10" name="full_description"
                                    placeholder="Enter Blog description" required>
                                    {{ $blog ? $blog->full_description : '' }}
                                </textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Product Category</label>
                                <select name="blog_category_id" class="custom-select" required>
                                    @foreach (\App\Models\BlogCategory::all() as $category)
                                        <option value="{{ $category->id }}"
                                            {{ !is_null($blog->blog_category_id) && $blog->blog_category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Status</label>
                                <select name="status" class="custom-select" required>
                                    <option value="published" {{$blog->status == "published" ? 'selected' : '' }}>Publish</option>
                                    <option value="draft" {{$blog->status == "draft" ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <img src="{{ $blog->cover_image }}" alt="Image" class="img-fluid">
                                </div>

       
                                @foreach ($blog->images as $key => $image)
                                    <div class="col-md-4 mb-4">
                                        <img src="{{ $image }}" alt="Image" class="img-fluid">
                                    </div>
                                @endforeach
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Cover Image</label>
                                <input type="file" class="form-control-file" id="cover_image" name="cover_image">
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-0">Images</label>
                                <input type="file" class="form-control-file" id="images" name="images[]"
                                    multiple>
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
    <script>
        document.addEventListener("DOMContentLoaded", function(event){    
            tinymce.init({
            selector: '#description',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
            });
    
    
        })


        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
        tinymce.triggerSave(); // Update textarea before form submission
        var content = document.getElementById('description').value;
        if (!content || content.trim() === '') {
            e.preventDefault();
            alert('Please enter a blog description');
        }
    });
    </script>
    @endsection
