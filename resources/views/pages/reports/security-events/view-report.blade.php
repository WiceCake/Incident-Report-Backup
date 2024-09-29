@extends('layout.app')

@section('title', 'MUTI Group | ' . $data->threat_id)

@section('page-css')
    <link rel="stylesheet" href="../assets/vendor/css/pages/app-invoice.css" />

@endsection

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




                        <div class="row invoice-preview">
                            <!-- Securirity Events -->
                            <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6" id="securityEventsData">
                                <div class="card invoice-preview-card p-sm-12 p-6">
                                    <div class="card-body invoice-preview-header rounded">
                                        <div>
                                            @if ($errors->any())
                                                <div class="alert alert-danger text-dark px-5 py-3">
                                                    <ul class="m-0">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <div class="mb-4 text-heading">
                                                <h5 class="fw-medium mb-2">ID: {{ $data->threat_id }}</h5>
                                                <div>
                                                    <span class="text-heading demo fw-bold fs-4 mb-3 d-block">Security Event
                                                        Name:</span>
                                                    <span
                                                        class="text-heading demo fw-bold fs-5 lh-1 me-5">{{ $data->threat }}</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div>
                                                <div class="mb-1 text-heading">
                                                    <span>Day Issues:</span>
                                                    <span class="fw-medium" id="dateDataDay"></span>
                                                </div>
                                                <div class="text-heading">
                                                    <span>Time Issue:</span>
                                                    <span class="fw-medium" id="dateDataTime"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0">

                                    </div>
                                    <div class="table-responsive border border-bottom-0 border-top-0 rounded mb-4">
                                        <table class="table m-0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th class="text-truncate">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Security Event Name</td>
                                                    <td class="text-wrap text-break">{{ $data->threat }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Threat Level</td>
                                                    <td class="text-nowrap">{{ $data->threat_level }}</td>
                                                </tr>
                                                @if ($data->others->btn_name)
                                                    <tr>
                                                        <td class="text-nowrap text-heading">Button Name</td>
                                                        <td class="text-nowrap">{{ $data->others->btn_name }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-nowrap text-heading">IP Address</td>
                                                    <td class="text-nowrap">{{ $data->ip_address }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Cookies</td>
                                                    <td class="text-wrap text-break">{{ $data->others->cookies }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Device</td>
                                                    <td class="text-nowrap">{{ $data->others->device }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">User Agent</td>
                                                    <td class="text-wrap text-break">{{ $data->others->user_agent }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Url</td>
                                                    <td class="text-wrap text-break">{{ $data->others->url }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Url Referrer</td>
                                                    <td class="text-wrap text-break">{{ $data->others->referrer_url }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr class="mt-0 mb-6">
                                    <div class="card-body p-0">
                                        <div class="row">
                                            <div class="col-12">
                                                <span class="fw-medium text-heading">Note:</span>
                                                <span>The presented data here can be printed through the download button in
                                                    the sidebar!</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Security Events -->

                            <!-- Security Events Actions -->
                            <div class="col-xl-3 col-md-4 col-12 invoice-actions">
                                <div class="card">
                                    <div class="card-body">
                                        <button class="btn btn-primary d-grid w-100 mb-4" data-bs-toggle="offcanvas"
                                            data-bs-target="#sendInvoiceOffcanvas" onclick="changeTime()">
                                            <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                    class="bx bx-paper-plane bx-sm me-2"></i>Create Report</span>
                                        </button>
                                        <button class="btn btn-label-success d-grid w-100"
                                            onclick="printDiv(securityEventsData, '{{ $data->threat_id }}')">
                                            Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /Security Events Actions -->
                        </div>

                        <!-- Offcanvas -->
                        <!-- Send Incident Report Sidebar -->
                        <div class="offcanvas offcanvas-end" id="sendInvoiceOffcanvas" aria-hidden="true">
                            <div class="hidden-data d-none">
                            </div>
                            <div class="offcanvas-header mb-6 border-bottom">
                                <h5 class="offcanvas-title">Create Incident Report</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body pt-0 flex-grow-1">
                                <form method="POST" action="{{ route('report.security.create') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" value="{{ $data->timestamp }}" id="threatTime">
                                    <input type="hidden" value="{{ $data->threat }}" id="threatName">
                                    <input type="hidden" value="{{ auth()->user()->username }}" name="username">
                                    <input type="hidden" value="{{ $data->threat_id }}" name="threat_id">
                                    <div class="mb-6">
                                        <label for="invoice-from" class="form-label">From</label>
                                        <input type="text" class="form-control" id="adminName" name="admin_name"
                                            value="{{ auth()->user()->name }}" readonly />
                                    </div>
                                    <div class="mb-6">
                                        <label for="invoice-to" class="form-label">Time Detected</label>
                                        <input type="text" class="form-control" id="timestampDetected"
                                            name="time_detected" readonly />
                                    </div>
                                    <div class="mb-6">
                                        <label for="invoice-to" class="form-label">Time Issued</label>
                                        <input type="text" class="form-control" id="timestampIssue"
                                            name="time_issued" readonly />
                                    </div>
                                    <div class="mb-6">
                                        <label for="invoice-subject" class="form-label">Select Type of Security
                                            Events</label>
                                        <select class="form-select" aria-label="Default select example"
                                            name="threat_type">
                                            <option value="Honeypot Interaction">Honeypot Interaction</option>
                                            <option value="Web Threat Attack">Web Threat Attack</option>
                                        </select>
                                    </div>
                                    <div class="mb-6">
                                        <label for="invoice-message" class="form-label">Security Events Name</label>
                                        <input class="form-control" type="text" name="threat_name"
                                            value="{{ $data->threat }}" readonly>
                                    </div>
                                    <div class="mb-6">
                                        <label for="invoice-message" class="form-label">Any Other Related
                                            Information</label>
                                        <input class="form-control" type="file" name="incident_attachments[]"
                                            id="formFileMultiple" multiple>
                                    </div>
                                    <div class="mb-6 d-flex flex-wrap">
                                        <button type="submit" class="btn btn-primary me-4"
                                            data-bs-dismiss="offcanvas">Create Report</button>
                                        <button type="button" class="btn btn-label-secondary"
                                            data-bs-dismiss="offcanvas">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- /Send Incident Report Sidebar -->

                        <!-- /Offcanvas -->

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

    {{-- Vendors --}}
    @section('vendor_js')

        <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }} "></script>
        <script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }} "></script>
        <script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }} "></script>
        <script src="{{ asset('assets/vendor/libs/print-js/html2canvas.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/print-js/jspdf.umd.min.js') }}"></script>

    @endsection

    {{-- Page JS --}}
    @section('page_js')
        <script src="{{ asset('assets/js/security-events-view.js') }} "></script>
    @endsection
