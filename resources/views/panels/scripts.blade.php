<!-- BEGIN: Vendor JS-->
<script src="{{ asset(mix('vendors/js/vendors.min.js')) }}"></script>
<!-- BEGIN Vendor JS-->
<!-- BEGIN: Page Vendor JS-->
<script src="{{asset(mix('vendors/js/ui/jquery.sticky.js'))}}"></script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- END: Theme JS-->
<script src="{{ asset(mix('vendors/js/extensions/toastr.min.js')) }}"></script>

@if(Auth::check() && Auth::user() && !Auth::user()->is_admin)
    @if(Auth::user()->activeSubscription()==null)
        <script>
            toastr['warning']("No Activite Subscription", 'Warning!', {
                closeButton: true,
                positionClass: 'toast-bottom-right',
                containerId: 'toast-bottom-right',
                timeout: 0,
            });
        </script>
    @endif
@endif


{{-- page script --}}
@yield('page-script')

@if(Session::has('message'))
    <script>
        let type = "{{ Session::get('status', 'success') }}";
        switch (type) {
            case 'info':
                toastr['info']("{!! Session::get('message') !!}", 'Alarm!', {
                    closeButton: true,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                    newestOnTop: true,
                });

                break;

            case 'warning':
                toastr['warning']("{!! Session::get('message') !!}", 'Warn!', {
                    closeButton: true,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                    newestOnTop: true,

                });
                break;

            case 'success':
                toastr['success']("{!! Session::get('message') !!}", 'Success!!', {
                    closeButton: true,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                    newestOnTop: true,

                });
                break;

            case 'error':
                toastr['error']("{!! Session::get('message') !!}", 'Error..!!', {
                    closeButton: true,
                    positionClass: 'toast-top-right',
                    progressBar: true,
                    newestOnTop: true,

                });
                break;
        }
    </script>
@endif
