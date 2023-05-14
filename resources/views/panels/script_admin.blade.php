<!-- Bootstrap JS -->
<script src="{{ asset(mix('/vendors/admin/js/bootstrap.bundle.min.js')) }}"></script>
<!--plugins-->
<script src="{{ asset(mix('/vendors/admin/js/jquery.min.js')) }}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/simplebar/js/simplebar.min.js')) }}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/metismenu/js/metisMenu.min.js')) }}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/perfect-scrollbar/js/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/select2/js/select2.min.js'))}}"></script>
<!--notification js -->
<script src="{{ asset(mix('/vendors/admin/plugins/notifications/js/lobibox.min.js'))}}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/notifications/js/notifications.min.js'))}}"></script>
<script src="{{ asset(mix('/vendors/admin/plugins/notifications/js/notification-custom-script.js'))}}"></script>
<script src="{{ asset(mix('/js/extensions/sweetalert2.all.min.js'))}}"></script>

@yield('add_plugins')
<script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    {{--(function() {--}}
    {{--    'use strict'--}}

    {{--    // Fetch all the forms we want to apply custom Bootstrap validation styles to--}}
    {{--    var forms = document.querySelectorAll('.needs-validation')--}}

    {{--    // Loop over them and prevent submission--}}
    {{--    Array.prototype.slice.call(forms)--}}
    {{--        .forEach(function(form) {--}}
    {{--            @if ($errors->any())--}}
    {{--                form.classList.add('was-validated')--}}
    {{--            @endif--}}

    {{--        })--}}
    {{--})()--}}
</script>
@yield('add_page_script')
<!--app JS-->
@if(request()->url() != url('/login') && request()->url() != url("/register"))
    <script>
        $(function(){
            new PerfectScrollbar(".header-message-list")
            new PerfectScrollbar(".header-notifications-list")
        });
    </script>
@endif
<script src="{{ asset(mix('/vendors/admin/js/app.js')) }}"></script>

<script>

</script>
@if(Session::has('message'))

    <script>
        let type = "{{Session::get('status',"success")}}"
        switch (type) {
            case 'info':

                Lobibox.notify('info', {
                    pauseDelayOnHover: true,
                    size: 'mini',
                    rounded: true,
                    icon: 'bx bx-info-circle',
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: "{!! Session::get('message') !!}"
                });
                break;

            case 'warning':
                Lobibox.notify('warning', {
                    pauseDelayOnHover: true,
                    size: 'mini',
                    rounded: true,
                    icon: 'bx bx-error',
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: "{!! Session::get('message') !!}"
                });
                break;

            case 'success':
                Lobibox.notify('success', {
                    pauseDelayOnHover: true,
                    size: 'mini',
                    rounded: true,
                    icon: 'bx bx-error',
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: "{!! Session::get('message') !!}"
                });
                break;

            case 'error':
                Lobibox.notify('error', {
                    pauseDelayOnHover: true,
                    size: 'mini',
                    rounded: true,
                    icon: 'bx bx-error',
                    continueDelayOnInactiveTab: false,
                    position: 'top right',
                    msg: "{!! Session::get('message') !!}"
                });
                break;

        }
    </script>
@endif
@yield('add_custom_script')
