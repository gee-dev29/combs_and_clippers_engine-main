@extends('layouts.app')
@section('content')
    <style>
        .tagify--outside {
            border: 0;
            width: 100%;
        }

        .tagify--outside .tagify__input {
            order: -1;
            flex: 100%;
            border: 1px solid var(--tags-border-color);
            margin-bottom: 1em;
            transition: .1s;
        }

        .tagify--outside .tagify__input:hover {
            border-color: var(--tags-hover-border-color);
        }

        .tagify--outside.tagify--focus .tagify__input {
            transition: 0s;
            border-color: var(--tags-focus-border-color);
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h3>Create Newsletter</h3>
                <div class="card mb-5">
                    <div class="card-body">
                        <form method="post" class="p-3" action="{{ route('newsletter.send') }}">
                            @include('dashboard.flash')
                            @csrf
                            <div class="form-group mb-4">
                                <label class="mb-0">User Group (Recipient)</label>
                                <select id="user_group" name="user_group" class="custom-select" required
                                    onchange="showUsers()">
                                    <option value="custom">Custom</option>
                                    <option value="all_vendor">Vendors - All</option>
                                    <option value="no_product">Vendors - No Product</option>
                                    <option value="no_sub">Vendors - Expired Subscription</option>
                                    <option value="due_3">Vendors - Subscription Due in 3 days</option>
                                    <option value="due_2">Vendors - Subscription Due in 2 days</option>
                                    <option value="due_1">Vendors - Subscription Due in 1 day</option>
                                    <option value="new_today">Vendors - New(today)</option>
                                </select>
                            </div>

                            <table class="display table table-striped table-bordered dataTable" id="user_group_table"
                                style="width: 100%;" role="grid" aria-describedby="user_group_table_info">
                                <thead>
                                    <tr role="row">
                                        <th class="sorting_asc" tabindex="0" aria-controls="user_group_table"
                                            rowspan="1" colspan="1" style="width: 46px;">ID</th>
                                        <th class="sorting_asc" tabindex="0" aria-controls="user_group_table"
                                            rowspan="1" colspan="1" style="width: 46px;">Name</th>
                                        <th class="sorting_asc" tabindex="0" aria-controls="user_group_table"
                                            rowspan="1" colspan="1" style="width: 46px;">Phone</th>
                                    </tr>
                                </thead>
                            </table>

                            <div class="form-group mb-4" id="phones-div">
                                <label for="message">Recipients Phone numbers</label>
                                <input id="phones" name='phones' class='tagify--outside'
                                    placeholder='Enter recipients phone numbers'>
                            </div>

                            <div class="form-group mb-4">
                                <label for="message">Message</label>
                                <textarea class="form-control" id="message" rows="10" name="message" required></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary pl-3 pr-3">Send Newsletter</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- Footer Start -->
    <script>
        var input = document.getElementById('phones');

        function showUsers() {
            var selectedUserGroup = $('#user_group').val();
            if (selectedUserGroup == 'custom') {
                $('#phones-div').show();
                destroyDataTable('#user_group_table');
                $('#user_group_table').hide();
                initializeTags(input);
            } else {
                $('#phones-div').hide();
                $('#user_group_table').show();
                initializeDataTable(selectedUserGroup);
            }
        }

        $(document).ready(function() {
            showUsers();
        });

        function initializeTags(input) {
            new Tagify(input, {
                originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(','),
                editTags: 1,
                transformTag: transformTag,
                texts: {
                    duplicate: "Duplicates are not allowed"
                },
                delimiters: ",| ", // add new tags when a comma or a space character is entered
                trim: false, // if "delimiters" setting is using space as a delimeter, then "trim" should be set to "false"
            });
        }

        function initializeDataTable(selectedUserGroup) {
            $('#user_group_table').DataTable({
                order: [],
                stateSave: true,
                processing: true,
                destroy: true,
                //serverSide: true,
                ajax: {
                    url: "{{ route('newsletter.getUserGroup') }}",
                    method: "POST",
                    //dataSrc: ""
                    //dataType: "json",
                    //type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        selectedUserGroup: selectedUserGroup
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'phone'
                    }
                ]
            });
        }

        function destroyDataTable(id) {
            var table = $(id).DataTable();
            table.destroy();
        }
        // generate a random color (in HSL format, which I like to use)
        function getRandomColor() {
            function rand(min, max) {
                return min + Math.random() * (max - min);
            }

            var h = rand(1, 360) | 0,
                s = rand(40, 70) | 0,
                l = rand(65, 72) | 0;

            return 'hsl(' + h + ',' + s + '%,' + l + '%)';
        }

        function transformTag(tagData) {
            tagData.color = getRandomColor();
            tagData.style = "--tag-bg:" + tagData.color;

            if (tagData.value.toLowerCase() == 'shit')
                tagData.value = 's✲✲t'
        }
    </script>
@endsection
