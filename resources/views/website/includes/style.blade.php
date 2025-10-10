<!-- jsvectormap css -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/jsvectormap/css/jsvectormap.min.css" >
<!--Swiper slider css-->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/swiper/swiper-bundle.min.css" >
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/sweetalert2/sweetalert2.min.css" >
<link rel="stylesheet" type="text/css" href="{{url('jQueryUI')}}/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="{{url('jQueryDateTimePicker')}}/css/jquery-ui-timepicker-addon.min.css">
<!-- Layout config Js -->
<script src="{{url('website')}}/assets/js/layout.js"></script>
<!-- Bootstrap Css -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/css/bootstrap.min.css" >
<!-- Icons Css -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/css/icons.min.css" >
<!-- App Css-->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/css/app.min.css" >
<!-- custom Css-->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/css/custom.min.css" >
<!-- Select2 CSS -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/select2/select2.min.css"/>
<!--datatable css-->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/datatable/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/datatable/responsive.bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/datatable/buttons.dataTables.min.css"/>
<!--toaster-->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/toastr/toastr.min.css"/>
<!-- Flatpickr CSS -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/flatpickr/flatpickr.min.css"/>
<!-- font-awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- richtext CSS -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/richtext/richtext.min.css"/>
<!-- MonthPicker CSS -->
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/MonthPicker/MonthPicker.min.css"/>
<link rel="stylesheet" type="text/css" href="{{url('website')}}/assets/libs/YearPicker/yearpicker.css"/>

<style>
    span.logo-lg {
        line-height: 90px;
    }
    tfoot {
        display: table-header-group !important;
    }
    .toolbar {
        float: right;
        margin-left: 10px;
    }
    .btnAdd{
        padding: 2px 6px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
        padding: 0px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border: 1px solid #ffffff !important;
        background-color: #ffffff !important;
    }



    /*select2 custom style*/
    .single-search-input{
        border: 1px solid #ddd  !important;
    }

    /* Change background color and text color of selected choices */
    .select2-selection__choice {
        background-color: #3498db !important; /* Set background color */
        color: #fff !important;               /* Set text color */
        border: 1px solid #2980b9 !important; /* Set border color */
        border-radius: 4px !important;        /* Make it rounded */
        padding: 3px 10px !important;         /* Adjust padding */
        margin: 2px 5px !important;           /* Adjust spacing between choices */
    }

    /* Change the color and size of the remove icon (x) */
    .select2-selection__choice__remove {
        color: #ffffff !important;            /* Change remove icon color */
        font-weight: bold !important;         /* Make the icon bold */
        margin-right: 5px !important;         /* Add spacing around the icon */
    }

    /* Change hover effect on the remove icon */
    .select2-selection__choice__remove:hover {
        color: #e74c3c !important;            /* Change remove icon color on hover */
        background-color: #3498db !important;
    }
    .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
    }
    /*select2 custom style*/

    /*employee dropdown list style start*/
    .searchEmployee{
        background:#ffffff;
        color:#2d3436;
        cursor:pointer;
        list-style:none;
        padding: 10px
    }
    .searchEmployee:hover{
        background:#2385BA;
        color: white;
    }
    .searchEmployeeSection{
        position: relative;
    }
    ul.custom-list {
        height: 200px; /* Fixed height */
        overflow-y: auto; /* Add vertical scrolling */
    }
    /*employee dropdown list style end*/

    /*select 2 style change start*/
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #444;
        line-height: 34px !important;
    }
    .select2-container--default .select2-selection--single {

        border: 1px solid #ced4da !important;
        height: 38px !important;

    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
    }
    /*select 2 style change end*/

    /* Add to grid table Normal CSS start*/

    table.added_table tr th {
        background: #E1E5EE !important;
        font-weight: 600;
        font-size: 13px;
        padding-left: 5px;
        border: 1px solid gray
    }

    table.added_table tr td {
        font-weight: 500;
        font-size: 13px;
        padding-left: 5px;
        border: 1px solid gray
    }
    /* Add to grid table Normal CSS end*/

    .form-control:disabled, .form-control[readonly] {
        background-color: #ECEFF1 !important;
    }

    /*flatpicker custom style*/
    .flatpickr-months {
        background-color: #2385ba !important;
        padding-bottom: 8px !important;
    }

    ul.custom-list{
        top: 65px !important;
        left: -20px !important;
        width: 100%;
    }

    span.red {
        color: red;
    }

    /*data table button start*/
    .table {
        table-layout: fixed; /* Enforces fixed column widths */
        width: 100%; /* Ensures the table stretches to the container's width */
    }
    .single-search-input{
        width: 100% !important;
    }
    /*.data-table td {*/
    /*    white-space: nowrap; !* Prevent text from wrapping, you can adjust this as needed *!*/
    /*    overflow: hidden; !* Hide overflow content *!*/
    /*    text-overflow: ellipsis; !* Show ellipsis for overflowing text *!*/
    /*}*/
    .assignData i,.editData i,.deleteData i,.approvedData i,.rejectData i,.PViewData i,.denyData i,.statusData i{
        font-size: 12px;
    }
    .approvedData,.editData,.denyData,.statusData{
        margin-right: 3px !important;
    }
    /*data table button end*/
    .error {
        color: red; /* Set the text color to red */
    }
    .topip, .bottomip{
        display: flex;
        justify-content: space-between;
        width: 100%;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        border: none !important;
    }

    /*employee add section card header*/
    .card-header{
        padding: 5px 16px !important;
    }

    /*add to grid button*/
    .triangle-button {
        width: 0;
        height: 0;
        border-left: 25px solid transparent;
        border-right: 25px solid transparent;
        border-top: 35px solid #317FB4;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .triangle-button:hover {
        transform: scale(1.1); /* Slightly increase size on hover */
    }

    /*invalid feedback style*/
    .invalid-feedback{
        color: red !important;
        font-size: 14px !important;
    }
    select.is-invalid ~ span .select2-selection {
        border: 1px solid red !important;
        /*border-color: var(--vz-form-invalid-border-color);*/
        padding-right: calc(1.5em + 1rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23f06548'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23f06548' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(1em + .25rem) center;
        background-size: calc(.75em + .5rem) calc(.75em + .5rem)
    }

    img {
        user-select: none; /* Prevent selection */
        pointer-events: none; /* Disable interaction (optional) */
    }
</style>
@yield('header_css')
