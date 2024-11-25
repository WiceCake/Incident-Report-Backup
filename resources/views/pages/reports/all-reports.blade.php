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
                            <h5 class="card-header pb-0 text-md-start text-center">Reports</h5>
                            <div class="card-datatable table-responsive">
                                <div class="ms-6 mt-5">

                                    <div class="mb-5 d-flex align-items-center gap-3">
                                        <label for="startDate">Start Date:</label>
                                        <input class="me-3 form-control w-25" type="date" id="startDate" placeholder="Start Date">

                                        <label for="endDate">End Date:</label>
                                        <input class="me-3 form-control w-25" type="date" id="endDate" placeholder="End Date">
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <label for="reportType">Report Type:</label>
                                        <select id="reportType" class="form-select w-25">
                                            <option value="">All</option>
                                            <option value="Incident Report">Incident Report</option>
                                            <option value="Completed Reports">Completed Reports</option>
                                            <!-- Add other report types as needed -->
                                        </select>
                                        <label for="status">Status:</label>
                                        <select id="status" class="form-select w-25">
                                            <option value="">All</option>
                                            <option value="Under Review">Under Review</option>
                                            <option value="Pending Approval">Pending Approval</option>
                                            <option value="Approved">Approved</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Completed">Completed</option>
                                            <!-- Add other report types as needed -->
                                        </select>
                                    </div>
                                </div>
                                <table class="invoice-list-table table border-top">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Report Type</th>
                                            <th>Incident Title</th>
                                            <th>Time Created</th>
                                            <th>Submitted By</th>
                                            <th>Status</th>
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
    {{-- <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/searchpanes/2.3.3/js/dataTables.searchPanes.js"></script>
    <script src="https://cdn.datatables.net/searchpanes/2.3.3/js/searchPanes.dataTables.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.1.0/js/select.dataTables.js"></script> --}}
@endsection

@section('page_js')
    <script src="{{ asset('assets/js/all-reports-list.js') }}"></script>
@endsection
