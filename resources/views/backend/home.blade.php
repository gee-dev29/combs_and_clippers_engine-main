@extends('backend.layouts.master')

@section('content')

     <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span>HOME PAGE
                            <!-- <button type="button" class="btn btn-info btn-xs pull-right" data-toggle="modal" data-target="#newModal">Add AboutUs</button> -->
                            <!-- <button type="button" class="btn btn-info btn-xs pull-right" data-toggle="modal" data-target="">Add AboutUs</button> -->
                            </span>
                        </div>

                        <div class="panel-body">
                          <div class="card text-center">
                            
                            <div class="card-body">
                              <h5 class="card-title">MANOTEL REPORTING SYSTEM</h5>
                              <p class="card-text">Client reporting system </p>

                              
                            </div>
                            
                          </div>  
                                
                              <div class="row">
                              {{--
                                @foreach($abouts as $about)
                                <div class="col-md-8 col-sm-8">
                                    <div class="well">
                                      
                                        <h4>{{ $about->title}}</h4>
                                        {!! $about->body !!}
                                        
                                       
                                        <a href="#" class="btn btn-primary editBlog" title="Edit" aboutID="{{$about->id}}" aboutTitle="{{$about->title}}" aboutBody="{!! $about->body !!}"  data-toggle="modal" data-target="#editModal">Edit</a>

                                        <a href="#" class="btn btn-danger deleteBlog" title="Delete" aboutId="{{$about->id}}" data-toggle="modal" data-target="#deleteModal">Delete</a>
                                      
                                        <!-- <a href="#" class="btn btn-success">success</a> -->
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="well well-lg">
                                        <!-- <h4>Large Well</h4>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tincidunt est vitae ultrices accumsan. Aliquam ornare lacus adipiscing, posuere lectus et, fringilla augue.</p> --> 
                                      @if(!is_null($about->image_link))
                                        <img src="{{ asset('/storage'.$about->image_link)}}" alt=""  height="200" width="250">
                                      @endif()
                                    </div>
                                </div>
                               <!--  <div class="col-md-4 col-sm-4">
                                    <div class="well well-sm">
                                        <h4>Small Well</h4>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tincidunt est vitae ultrices accumsan. Aliquam ornare lacus adipiscing, posuere lectus et, fringilla augue.</p>
                                    </div>
                                </div> -->
                              @endforeach()
                              --}}
                           </div>

                        </div>
                        
                    </div>
                     <!-- End Form Elements -->

@endsection('content')


@section('modals')

   <!-- Trigger the modal with a button -->
                <!--  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Open Modal</button> -->
                
                <!-- new article Modal -->
                <div id="newModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="color:#d66887;">Add AboutUs</h4>
                      </div>
                      <div class="modal-body">
                        <!-- <p>Some text in the modal.</p> -->
                        <form method="post" action="" enctype="multipart/form-data">
                          {{ csrf_field() }}
                          <input type="text" name="title" class="form-control" placeholder="Title" required="">

                          <br>
                          <textarea class="form-control" name="about_body" cols="4" rows="5" required=""></textarea>
            
                          {{-- <div class="picture" style="">
                            <img src="{{ asset('backend/assets/img/find_user.png') }}"
                            class="picture_src" id="picture_preview" alt="" width="100px" height="100px" >
                          <input class="img_container" type="file" id="upload" name="image">
                          </div> --}}
                          <br>
                          
                          <input type="submit" name="" value="Submit" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancle</button>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                      </div>
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

                <!-- Edit Modal -->
                <div id="editModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="color:#7095d1;">Edit Article</h4>
                      </div>
                      <div class="modal-body">
                        <!-- <p>Some text in the modal.</p> -->
                        <form method="post" action="" enctype="multipart/form-data">
                          {{ csrf_field() }}
                          <input type="hidden" name="aboutID" id="modal_about_id">
                          <input type="text" name="about_title" class="form-control" id="modal_about_title" placeholder="Title" required="">
                          <br>
                          <textarea class="form-control" name="body" id="modal_about_body" cols="4" rows="5" required=""></textarea>
                          <br>
                          {{-- <input type="file" name="image" class="btn btn-default">
                          <br> --}}
                          <input type="submit" name="" value="Submit" class="btn btn-primary">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancle</button>
                          
                        </form>
                      </div>
                      
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

                <!-- Delete Modal -->
                <div id="deleteModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" style="color:#d66887;">Delete Article</h4>
                      </div>
                      <div class="modal-body">
                        <h2 style="color:#7095d1;"><b>Are you sure you want delete this article?</b></h2>
                        <form method="post" action="">
                          <input type="hidden" name="aboutId" id="del_about_id">
                             {{ csrf_field() }}

                           <div class="modal-footer">
                              <button type="submit" class="btn btn-danger">DELETE</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
                              
                          </div>
                        </form>
                      </div>
                      <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div> -->
                    </div>

                  </div>
                </div>
                <!-- end Modal -->

@endsection('modals')


@section('scripts')

<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>

<!-- Using ckeditor -->
    <script>
      //CKEDITOR.config.extraPlugins = "base64image";
      //CKEDITOR.replace( 'about_body' );
    </script>

    <!-- putting parameters in  edit modal -->
    <script>

      $('body').on('click', '.editBlog', function(){
         
        var about_id    =  $(this).attr('aboutID');
        var about_title = $(this).attr('aboutTitle');
        var about_body = $(this).attr('aboutBody');
       

        //var selected_salesman  =   $("#" + salesmen_id).find(':selected').attr('value');
        
        $('#modal_about_id').val(about_id);
        $('#modal_about_title').val(about_title);
        $('#modal_about_body').val(about_body).val();
        
        //CKEDITOR.replace( 'body' );
      });
    </script>

    <!-- putting parameter in delete modal -->
    <script>

      $('body').on('click', '.deleteBlog', function(){
         
        var del_about_id    =  $(this).attr('aboutId');
       
        $('#del_about_id').val(del_about_id);
        
      });
    </script>

    <!-- Image changing script -->
    <script >
      
        $("#upload").on('change', function() {
            //alert("alert");
            readUrl();
        });
        function readUrl (){
            var file = $("#upload")[0].files[0];
            //console.log(file);
            //reader interprets the file selected
            var reader = new FileReader();
                //console.log(reader.result);
            reader.onloadend = function (){
                //console.log(reader.result);
                $("#picture_preview").attr("src", reader.result);
                //$("#picture_preview")[0].src = "" + reader.results;
                //console.log($("#picture_preview"));
                //console.log(reader.result);
            }
            if(file){
                reader.readAsDataURL(file);
            }
        }
  
    </script>

@endsection('scripts')

