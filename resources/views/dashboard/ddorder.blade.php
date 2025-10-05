@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card text-left">
                    <div class="card-body">
                        <h4 class="card-title mb-5 font-weight-bold">Orders

                            @if (app('request')->input('from') !== null)
                                <small>Filter: From {{ app('request')->input('from') }} <b>To:</b>
                                    {{ app('request')->input('to') }}</small>
                            @endif
                        </h4>



                        <div class="container text-right">
                            @if (app('request')->input('from') !== null)
                                <!-- Example split danger button -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-dark">Download <i class="fa fa-download"
                                            aria-hidden="true"></i> </button>
                                    <button type="button" class="btn btn-dark dropdown-toggle dropdown-toggle-split"
                                        data-toggle="dropdown" aria-expanded="false">
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" id="export_button" href="#">Excel <i
                                                class="fa fa-file-excel-o" aria-hidden="true"></i></a>
                                        <div class="dropdown-divider"></div>
                                        {{-- <a class="dropdown-item" onclick="exportPDF('fk')" href="#">PDF <i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
      --}}

                                    </div>
                                </div>
                            @endif
                            <button data-toggle="modal" data-target="#exampleModal" class="btn ml-3 pl-4 pr-4 btn-dark"> <i
                                    class="fa fa-search" aria-hidden="true"></i> Filter by date</button>
                        </div>




                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Filter by date</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('filter') }}" method="get">
                                            @csrf
                                            <input type="hidden" name="table" value="orders">
                                            <input type="hidden" name="page" value="orders">
                                            <div class="form-row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="mb-0">From[yyyy-mm-dd]</label>
                                                        <input type="text" minlength="10" maxlength="10"
                                                            class="form-control" name="from" required
                                                            placeholder="yyyy-mm-dd">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="mb-0">To[yyyy-mm-dd]</label>
                                                    <input minlength="10" maxlength="10" type="text" class="form-control"
                                                        name="to" required placeholder="yyyy-mm-dd">
                                                </div>
                                            </div>

                                            <div class="form-group text-center">
                                                <button type="submit" class="btn btn-primary pr-3 pl-4">Filter</button>
                                            </div>

                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" id="fk">
                            <div class=" container-fluid dt-bootstrap4">

                                <div class="row">
                                    <div class="col-sm-12">

                                        <?php
                                        
                                        $vr = json_decode($collection, true);
                                        $th = array_keys($vr[0]);
                                        //print_r($th);
                                        
                                        $tb = array_values($vr);
                                        //  print_r($tb);
                                        
                                        // die;
                                        ?>

                                        <table class="display table table-striped table-bordered" id="order"
                                            style="width: 100%;" role="grid"
                                            aria-describedby="zero_configuration_table_info">
                                            <thead>
                                                <tr role="row">

                                                    <?php
                                                    foreach ($th as $key => $value) {
                                                        echo '<th class="text-capitalize">' . $value . '</th>';
                                                    }
                                                    ?>

                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                foreach ($tb as $key => $value) {
                                                    echo '<tr>';
                                                    foreach ($value as $k => $v) {
                                                        echo '<td>' . $value[$k] . '</td>';
                                                    }
                                                
                                                    echo '</tr>';
                                                }
                                                
                                                ?>


                                            </tbody>

                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>



    <script>
        function html_table_to_excel(type) {
            var data = document.getElementById('order');

            var file = XLSX.utils.table_to_book(data, {
                sheet: "sheet1"
            });

            XLSX.write(file, {
                bookType: type,
                bookSST: true,
                type: 'base64'
            });

            XLSX.writeFile(file, 'file.' + type);
        }

        const export_button = document.getElementById('export_button');

        export_button.addEventListener('click', () => {
            html_table_to_excel('xlsx');
        });




        var specialElementHandlers = {
            // element with id of "bypass" - jQuery style selector
            '.no-export': function(element, renderer) {
                // true = "handled elsewhere, bypass text extraction"
                return true;
            }
        };

        function exportPDF(id) {
            var doc = new jsPDF('p', 'pt', 'a4');
            //A4 - 595x842 pts
            //https://www.gnu.org/software/gv/manual/html_node/Paper-Keywords-and-paper-size-in-points.html


            //Html source 
            var source = document.getElementById(id);
            console.log(source);
            var margins = {
                top: 10,
                bottom: 10,
                left: 10,
                width: 595
            };

            doc.fromHTML(
                source, // HTML string or DOM elem ref.
                margins.left,
                margins.top, {
                    'width': margins.width,
                    'elementHandlers': specialElementHandlers
                },

                function(dispose) {
                    // dispose: object with X, Y of the last line add to the PDF 
                    //          this allow the insertion of new lines after html
                    doc.save('Order.pdf');
                }, margins);
        }
    </script>
@endsection
