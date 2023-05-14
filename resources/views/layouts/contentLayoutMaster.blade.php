
    <!DOCTYPE html>


<html lang="en">

<head>
    {{--   Required meta tags  --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="test" />
    {{--  <!--favicon-->  --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>test</title>
{{--    <link rel="shortcut icon" type="image/png" href="<?php echo asset(config('app.favicon')); ?>" />--}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

    {{-- Include core + vendor Styles --}}
    @include('panels/admin_style')

</head>
{{--  <!-- END: Head-->  --}}

<!-- BEGIN: Body-->

<body class="bg-theme bg-theme2">
<!--wrapper-->
<div class="wrapper">
    <!--sidebar wrapper -->
    <div class="sidebar-wrapper" data-simplebar="true">
        <div class="sidebar-header">
            <div>
                <img src="" class="logo-icon" alt="logo icon">
            </div>
            <div>
                <h4 class="logo-text">Movie Test</h4>
            </div>
            <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
            </div>
        </div>
        <!--navigation-->
        <ul class="metismenu" id="menu">
            @include('panels/sidebar')
        </ul>
        <!--end navigation-->
    </div>
    <!--end sidebar wrapper -->
    <!--start header -->
    <header>
        @include('panels/header')
    </header>
    <!--end header -->
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
        @include('panels/breadcrumb')
        <!--end breadcrumb-->
            @yield('content')
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i
            class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    {{--  <footer class="page-footer">
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>  --}}
</div>
<!--end wrapper-->
<!--start switcher-->
@include('panels/admin_switcher')
<!--end switcher-->
@include('panels/script_admin')

</body>
