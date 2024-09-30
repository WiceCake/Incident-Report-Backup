@extends('layout.app')

@section('title', 'MUTI Group | Honeypot Logs')

@section('content')

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">

            <!-- Menu -->
            @include('layout.menu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('layout.header')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Ajax Sourced Server-side -->
                        <div class="card">
                            <h5 class="card-header pb-0 text-md-start text-center">Honeypot Logs</h5>
                            <div class="card-datatable table-responsive text-nowrap">
                            <table class="invoice-list-table table border-top">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th class="text-truncate">Events</th>
                                            <th>Date Detected</th>
                                            {{-- <th class="text-truncate">Status</th> --}}
                                            {{-- <th>Invoice Status</th>
                                            <th class="cell-fit">Action</th> --}}
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <!--/ Ajax Sourced Server-side -->

                        <hr class="my-12">

                    </div>
                    <!-- / Content -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>


        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>

    </div>
    <!-- / Layout wrapper -->

@endsection

@section('vendor_js')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_js')
    <script src="{{ asset('assets/js/honeypot-tables.js') }}"></script>
@endsection
