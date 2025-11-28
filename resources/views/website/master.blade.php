<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="enable" data-theme="default" data-theme-colors="default" data-bs-theme="{{$darkMode==1?'dark':'light'}}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WMS Developed by Multibarnd Infotech">
    <meta name="keywords" content="mit, wms">
    <meta name="author" content="MIT">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="{{ url('app_assets') }}/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('app_assets') }}/images/ico/favicon.ico">
    @include('website.includes.style')
</head>
<body class="fullscreen-enable">
<!-- Begin page -->
<div id="layout-wrapper">
    <!-- Header Start -->
    @include('website.includes.header')
    <!-- Header End -->
    <!-- ========== App Menu ========== -->
    @include('website.includes.sidebar')
    <!-- Left Sidebar End -->
    <!-- Vertical Overlay-->
    <div class="vertical-overlay"></div>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        @yield('content')
        <!-- End Page-content -->
        @include('website.includes.footer')
    </div>
    <!-- end main content-->
</div>
<!-- END layout-wrapper -->
<!--start back-to-top-->
<button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="ri-arrow-up-line"></i>
</button>
<!--end back-to-top-->
<!--preloader-->

<div id="preloader">
    <div id="status">
        <div class="spinner-border text-primary avatar-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Floating Chatbot Button -->
<div id="chatbot-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <button id="chatbot-toggle" class="btn btn-success rounded-circle" 
            style="width:60px; height:60px; font-size:24px;">
        💬
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="card shadow-sm" 
         style="display:none; width: 300px; height:400px; margin-bottom:10px; flex-direction:column;">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span>Waste Bot</span>
            <button id="chatbot-close" class="btn btn-sm btn-light">&times;</button>
        </div>
        <div id="chatbot-messages" class="card-body overflow-auto" style="flex:1; padding:10px;">
            <div class="text-muted small">Hi! How can I help you?</div>
        </div>
        <div class="card-footer p-2">
            <form id="chatbot-form" class="d-flex">
                <input type="text" id="chatbot-input" class="form-control form-control-sm me-1" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-success btn-sm">Send</button>
            </form>
        </div>
    </div>
</div>

{{--<div class="customizer-setting d-none d-md-block">--}}
{{--    <div class="btn-info rounded-pill shadow-lg btn btn-icon btn-lg p-2" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">--}}
{{--        <i class='mdi mdi-spin mdi-cog-outline fs-22'></i>--}}
{{--    </div>--}}
{{--</div>--}}


<!-- Theme Settings -->
{{-- @include('website.includes.offcanvas')--}}
<!-- JAVASCRIPT -->

{!! Toastr::message() !!}


@include('website.includes.script')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatWindow = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const chatForm = document.getElementById('chatbot-form');
    const chatInput = document.getElementById('chatbot-input');
    const chatMessages = document.getElementById('chatbot-messages');

    toggleBtn.addEventListener('click', () => {
        chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
        chatWindow.style.flexDirection = 'column';
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if(!msg) return;

        chatMessages.innerHTML += `<div><strong>You:</strong> ${msg}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;
        chatInput.value = '';

        fetch("{{ url('/chatbot/message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: msg })
        })
        .then(res => res.json())
        .then(data => {
            chatMessages.innerHTML += `<div><strong>Bot:</strong> ${data.reply}</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    });
});
</script>



</body>
</html>
