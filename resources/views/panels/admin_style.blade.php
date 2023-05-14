@yield('vendor-style')

<!--plugins-->
<link rel="stylesheet" href="{{ asset(mix('/vendors/admin/plugins/notifications/css/lobibox.min.css'))}}" />
<link href="{{ asset(mix('/vendors/admin/plugins/simplebar/css/simplebar.css')) }}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/select2/css/select2.min.css'))}}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/select2/css/select2-bootstrap4.css'))}}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css')) }}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/metismenu/css/metisMenu.min.css')) }}" rel="stylesheet" />

<!-- loader-->
<link href="{{ asset(mix('/vendors/admin/css/pace.min.css')) }}" rel="stylesheet" />
<script src="{{ asset(mix('/vendors/admin/js/pace.min.js')) }}"></script>
<!-- Bootstrap CSS -->
<link href="{{ asset(mix('/vendors/admin/plugins/datetimepicker/css/classic.css'))}}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/datetimepicker/css/classic.time.css'))}}" rel="stylesheet" />
<link href="{{ asset(mix('/vendors/admin/plugins/datetimepicker/css/classic.date.css'))}}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset(mix('/vendors/admin/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css'))}}">
<link href="{{ asset(mix('/vendors/admin/css/bootstrap.min.css')) }}" rel="stylesheet">
<link href="{{ asset(mix('/vendors/admin/css/bootstrap-extended.css')) }}" rel="stylesheet">
<link href="{{ asset(mix('/css/extensions/sweetalert2.min.css')) }}" rel="stylesheet">

<link href="{{ asset(mix('/vendors/admin/css/app.css')) }}" rel="stylesheet">
<link href="{{ asset(mix('/vendors/admin/css/icons.css')) }}" rel="stylesheet">

{{-- Page Styles --}}
@yield('page-style')

<!-- BEGIN: Custom CSS-->
