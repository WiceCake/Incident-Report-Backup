@extends('layout.app')

@section('title', 'MUTI Group | Post Incident Reports')

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

                        @if (session('success'))
                            <div class="alert alert-success my-5 shadow-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Invoice List Table -->
                        <div class="card">
                            <h5 class="card-header pb-0 text-md-start text-center">Post Incident Reports</h5>
                            <div class="card-datatable table-responsive">
                                <table class="invoice-list-table table border-top">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Incident Title</th>
                                            <th>Time Created</th>
                                            <th>Submitted By</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layout.footer')
                    <!-- / Footer -->

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
    {{-- <script src="{{ asset('assets/vendor/libs/moment/moment-timezone.js') }}"></script> --}}
    <script src="{{ asset('assets/vendor/libs/moment/moment-timezone-with-data-10-year-range.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page_js')
    <script src="{{ asset('assets/js/post-incident-list.js') }}"></script>
@endsection
