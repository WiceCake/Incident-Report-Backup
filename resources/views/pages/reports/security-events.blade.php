@extends('layout.app')

@section('title', 'MUTI Group | Security Events')

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

                        <!-- Invoice List Widget -->

                        {{-- <div class="card mb-6">
                            <div class="card-widget-separator-wrapper">
                                <div class="card-body card-widget-separator">
                                    <div class="row gy-4 gy-sm-1">
                                        <div class="col-sm-6 col-lg-3">
                                            <div
                                                class="d-flex justify-content-between align-items-center card-widget-1 border-end pb-4 pb-sm-0">
                                                <div>
                                                    <h4 class="mb-0">24</h4>
                                                    <p class="mb-0">Clients</p>
                                                </div>
                                                <div class="avatar me-sm-6">
                                                    <span class="avatar-initial rounded bg-label-secondary text-heading">
                                                        <i class="bx bx-user bx-26px"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <hr class="d-none d-sm-block d-lg-none me-6">
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <div
                                                class="d-flex justify-content-between align-items-center card-widget-2 border-end pb-4 pb-sm-0">
                                                <div>
                                                    <h4 class="mb-0">165</h4>
                                                    <p class="mb-0">Invoices</p>
                                                </div>
                                                <div class="avatar me-lg-6">
                                                    <span class="avatar-initial rounded bg-label-secondary text-heading">
                                                        <i class="bx bx-file bx-26px"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <hr class="d-none d-sm-block d-lg-none">
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <div
                                                class="d-flex justify-content-between align-items-center border-end pb-4 pb-sm-0 card-widget-3">
                                                <div>
                                                    <h4 class="mb-0">$2.46k</h4>
                                                    <p class="mb-0">Paid</p>
                                                </div>
                                                <div class="avatar me-sm-6">
                                                    <span class="avatar-initial rounded bg-label-secondary text-heading">
                                                        <i class="bx bx-check-double bx-26px"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-0">$876</h4>
                                                    <p class="mb-0">Unpaid</p>
                                                </div>
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded bg-label-secondary text-heading">
                                                        <i class="bx bx-error-circle bx-26px"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        @if (session('success'))
                            <div class="alert alert-success my-5 shadow-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Invoice List Table -->
                        <div class="card">

                            <h5 class="card-header pb-0 text-md-start text-center">Security Events</h5>
                            <div class="card-datatable table-responsive">
                                <table class="invoice-list-table table border-top">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Threat Level</th>
                                            <th>Events</th>
                                            <th>Ip Address</th>
                                            <th>Date Detected</th>
                                            <th>Action</th>
                                            {{-- <th class="text-truncate">Status</th> --}}
                                            {{-- <th>Invoice Status</th>
                                            <th class="cell-fit">Action</th> --}}
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
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

@endsection

@section('page_js')
    <script src="{{ asset('assets/js/security-events-list.js') }}"></script>
@endsection
