@extends('layouts/contentLayoutMaster')

@section('content')
    <div clas="row w-full mb-5">
        <a href="{{route('admin.movies.create')}}" class="btn btn-info float-right">Add Movie</a>
    </div>
    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>ID</th>
                        <th>Plan</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Cast</th>
                        <th>Price</th>
                        <th>Rent Start</th>
                        <th>Rent End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>


                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>ID</th>
                        <th>Plan</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Cast</th>
                        <th>Price</th>
                        <th>Rent Start</th>
                        <th>Rent End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('add_plugins')

    <script src="{{ asset(mix('vendors/admin/plugins/datatable/js/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/admin/plugins/datatable/js/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        $(document).ready(function() {
            // init table dom

            //show response message
            function showResponseMessage(data) {

                if (data.status === 'success') {
                    Lobibox.notify('info', {
                        pauseDelayOnHover: true,
                        size: 'mini',
                        rounded: true,
                        icon: 'bx bx-info-circle',
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: data.message
                    });

                    dataListView.draw();
                } else {
                    Lobibox.notify('warning', {
                        pauseDelayOnHover: true,
                        size: 'mini',
                        rounded: true,
                        icon: 'bx bx-error',
                        continueDelayOnInactiveTab: false,
                        position: 'top right',
                        msg: data.message
                    });
                }
            }
            let Table = $("table");

            let dataListView=$('#example').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('admin.movies.search') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {_token: "{{csrf_token()}}"}
                },
                columns: [
                    { data: 'responsive_id', orderable:false,searchable:false },
                    { data: 'uid' },
                    { data: 'id' },
                    { data: 'type'},
                    { data: 'poster' },
                    { data: 'title' },
                    { data: 'cast' },
                    { data: 'price' },
                    { data: 'rent_start' },
                    { data: 'rent_end' },
                    { data: 'status' },
                    { data: 'action' , orderable:false,searchable:false },
                ],
                columnDefs:[
                    {
                        // For Responsive
                        className: 'control',
                        orderable: false,
                        responsivePriority: 2,
                        targets: 0
                    },
                    {
                        // For Checkboxes
                        targets: 1,
                        orderable: false,
                        responsivePriority: 3,
                        render: function (data) {
                            return (
                                '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="" id="' +
                                data +
                                '" /><label class="form-check-label" for="' +
                                data +
                                '"></label></div>'
                            );
                        },
                        checkboxes: {
                            selectAllRender:
                                '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>',
                            selectRow: true
                        }
                    },
                    {
                        targets: 2,
                        visible: false
                    },
                    {
                        // Actions
                        targets: -1,
                        title: 'Actions',
                        orderable: false,
                        render: function (data, type, full) {
                            let returnUrl =
                                '<span class="action-delete text-danger cursor-pointer me-1" data-id=' + full['id'] + '>' +
                                feather.icons['trash'].toSvg({class: 'font-medium-4'}) +
                                '</span>' +

                                '<a href="' + full['edit'] + '" class="text-primary me-1">' +
                                feather.icons['edit'].toSvg({class: 'font-medium-4'}) +
                                '</a>';

                            return (
                                returnUrl
                            );
                        }
                    }

                ],
                dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                language: {
                    sLengthMenu: "_MENU_",
                    sZeroRecords: "{{ __('locale.datatables.no_results') }}",

                },
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function (row) {
                                let data = row.data();
                                return 'Details of ' + data['name'];
                            }
                        }),
                        type: 'column',
                        renderer: function (api, rowIdx, columns) {
                            let data = $.map(columns, function (col) {
                                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                                    ? '<tr data-dt-row="' +
                                    col.rowIdx +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    '<td>' +
                                    col.title +
                                    ':' +
                                    '</td> ' +
                                    '<td>' +
                                    col.data +
                                    '</td>' +
                                    '</tr>'
                                    : '';
                            }).join('');

                            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
                        }
                    }
                },
            })

            Table.delegate(".get_status", "click", function () {
                let movie_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/movies/')}}/"+movie_id+"/active",
                    type: "POST",
                    data: {
                        _token: "{{csrf_token()}}"
                    },
                    success: function (data) {
                        showResponseMessage(data);
                    }
                });
            });
            Table.delegate(".action-delete", "click", function (e) {
                e.stopPropagation();
                let id = $(this).data('id');
                console.log('id',id);
                Swal.fire({
                    title: "Are you sure?",
                    text: "Are you going to delete movie?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{url('admin/movies/')}}/"+id,
                            type: "DELETE",
                            data: {
                                _method: 'DELETE',
                                _token: "{{csrf_token()}}"
                            },
                            success: function (data) {
                                showResponseMessage(data);
                            },
                            error: function (reject) {
                                if (reject.status === 422) {
                                    let errors = reject.responseJSON.errors;
                                    $.each(errors, function (key, value) {
                                        Lobibox.notify('warning', {
                                            pauseDelayOnHover: true,
                                            size: 'mini',
                                            rounded: true,
                                            icon: 'bx bx-error',
                                            continueDelayOnInactiveTab: false,
                                            position: 'top right',
                                            msg: value[0]
                                        });
                                    });
                                } else {

                                    Lobibox.notify('warning', {
                                        pauseDelayOnHover: true,
                                        size: 'mini',
                                        rounded: true,
                                        icon: 'bx bx-error',
                                        continueDelayOnInactiveTab: false,
                                        position: 'top right',
                                        msg: reject.responseJSON.message
                                    });

                                }
                            }
                        })
                    }
                })
            })
        });



    </script>
@endsection

@section('add_page_script')
    <script src="{{ asset(mix('vendors/admin/js/index.js')) }}"></script>
@endsection
