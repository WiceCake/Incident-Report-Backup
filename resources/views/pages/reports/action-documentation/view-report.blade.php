@extends('layout.app')

@section('title', 'MUTI Group | ' . $draft_report->report_id)

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

                        @error('threat_type')
                            <div class="alert alert-danger my-5 shadow-sm">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="row invoice-preview">

                            <div class="col-12 mb-6" id="incidentReportsData">

                                <!-- Securirity Events -->
                                @if (session('success'))
                                    <div class="alert alert-success my-5 shadow-sm">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <div class="card invoice-preview-card p-sm-12 p-6">
                                    <div class="card-body invoice-preview-header rounded">
                                        <div>
                                            <div class="mb-4 text-heading">
                                                <h5 class="fw-medium mb-2">Report ID: {{ $draft_report->report_id }}
                                                </h5>
                                                <input type="hidden" id="reportID" value="{{ $draft_report->report_id }}">
                                                <div>
                                                    <span class="text-heading demo fw-bold fs-4 mb-3 d-block">Incident Title:</span>
                                                    <span
                                                        class="text-heading demo fw-bold fs-5 lh-1 me-5">{{ $draft_report->incident_title }}</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div>
                                                <div class="mb-1 text-heading">
                                                    <span>Date Created:</span>
                                                    <span class="fw-medium">{{ $draft_report->timestamp }}</span>
                                                </div>
                                                <div class="mb-1 text-heading">
                                                    <span>Created By:</span>
                                                    <span class="fw-medium">{{ $draft_report->admin_name }}</span>
                                                </div>
                                                <div class="mb-1 text-heading">
                                                    <span>Status:</span>
                                                    <span class="fw-medium">{{ $draft_report->status }}</span>
                                                </div>
                                                {{-- @if (count((array) $report_data->draft_data))
                                                    <div class="mb-1 text-heading">
                                                        <span>Date Drafted:</span>
                                                        <span
                                                            class="fw-medium">{{ $report_data->draft_data->timestamp }}</span>
                                                    </div>
                                                @endif --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0">

                                    </div>
                                    {{-- <hr class="mt-0 mb-6"> --}}

                                    {{-- <div class="card-body p-0">
                                        <div class="row">
                                            <div class="col-12">
                                                <span class="fw-medium text-heading">Attachments:</span>
                                                @if (!count($report_data->attachment_path))
                                                    <p class="my-5">No attachments available</p>
                                                @endif
                                                @foreach ($report_data->attachment_path as $file)
                                                    @php
                                                        $fileParts = explode('/', $file);
                                                        $lastPart = end($fileParts);
                                                    @endphp
                                                    <a class="d-block link-underline link-underline-opacity-0 my-2"
                                                        href="{{ asset($file) }}" target="_blank">{{ $lastPart }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div>
                                        <span class="fw-medium fs-large text-heading">Summary about the Incident:</span>
                                        <p class="my-5">{{ $draft_report->summary_info }}</p>
                                    </div>
                                    <hr class="mt-6 mb-6">
                                    <div class="col-12">
                                        <span class="fw-medium fs-large text-heading">Plans about the Incident:</span>
                                        <p class="my-5">{{ $draft_report->plan_info }}</p>
                                    </div>
                                    <hr class="mt-6 mb-6">
                                    @if($draft_report->status != 'Mitigated')
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-medium fs-large text-heading mb-6">Progress Logs:</span>
                                        <button class="btn btn-primary d-grid w-25 mb-4" data-bs-toggle="modal"
                                            data-bs-target="#basicModal">
                                            <span class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                    class="bx bx-check bx-sm me-2"></i>Log Progress</span>
                                        </button>
                                    </div>
                                    @else
                                    <span class="fw-medium fs-large text-heading mb-6">Progress Logs:</span>
                                    @endif
                                    <div class="card-datatable table-responsive">
                                        <table class="table m-0 invoice-list-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th class="text-truncate">Description</th>
                                                    <th class="text-truncate">Method Used</th>
                                                    <th class="text-truncate">Performed By</th>
                                                    <th class="text-truncate">Time Issued</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            <!-- /Security Events -->

                            <!-- Security Events Actions -->
                            <div class="col-12 invoice-actions">
                                <div class="card">
                                    <div class="card-body">
                                        @if ($draft_report->status == 'In Progress')
                                            <button class="btn btn-primary d-grid w-100 mb-4" data-bs-toggle="modal"
                                                data-bs-target="#basicModal1">
                                                <span
                                                    class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                        class="bx bx-check bx-sm me-2"></i>Mitigate Attack</span>
                                            </button>
                                        @else
                                            <button class="btn btn-primary d-grid w-100 mb-4" data-bs-toggle="modal"
                                                data-bs-target="#basicModal2">
                                                <span
                                                    class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                        class="bx bx-check bx-sm me-2"></i>Create Post Assessment Report</span>
                                            </button>
                                        @endif
                                        <button class="btn btn-label-success d-grid w-100"
                                            onclick="printDiv(incidentReportsData, '{{ $draft_report->report_id }}')">
                                            Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /Security Events Actions -->
                        </div>

                        <!-- Modal Create Draft -->
                        <div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('report.action_documentation.logging') }}"
                                        enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel1">Create Progress Log</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div>
                                                    @csrf
                                                    <input type="hidden" value="{{ $draft_report->report_id }}"
                                                        name="draft_id">
                                                    <input type="hidden" class="form-control" id="adminName"
                                                        name="admin_name" value="{{ auth()->user()->name }}" />
                                                    <div class="mb-6">
                                                        <label for="invoice-to" class="form-label">Description about the
                                                            action taken (Progress about action)*</label>
                                                        <textarea class="form-control" placeholder="Include any information here about the incident..." rows="2"
                                                            style="height: 157px;" name="log_description" required></textarea>
                                                    </div>
                                                    <div class="mb-6">
                                                        <label for="invoice-from" class="form-label">Performed By*</label>
                                                        <input type="text" class="form-control" id="adminName"
                                                            name="user_performed" value="" required />
                                                    </div>
                                                    <div class="mb-6">
                                                        <label for="invoice-to" class="form-label">Method Used (Indicate
                                                            Everything Used)*</label>
                                                        <textarea class="form-control" placeholder="Include any information method used to mitigate the attack..."
                                                            rows="2" style="height: 157px;" name="method_used" required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn btn-primary">Create Progress Log</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Create Draft -->
                        <div class="modal fade" id="basicModal1" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('report.action_documentation.mitigate') }}"
                                        enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel1">Message</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div>
                                                    @csrf
                                                    <span>Are you sure you want to finish this incident report and note as
                                                        mitigated?</span>
                                                    <input type="hidden" value="{{ $draft_report->report_id }}"
                                                        name="report_id">
                                                    <input type="hidden" name="admin_name"
                                                        value="{{ auth()->user()->name }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn btn-danger">Confirm</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Create Draft -->
                        <div class="modal fade" id="basicModal2" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('report.action_documentation.post_assessment') }}"
                                        enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel1">Message</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div>
                                                    @csrf
                                                    <span>Are you sure you want to finish this incident and create post assessment report?</span>
                                                    <input type="hidden" value="{{ $draft_report->report_id }}"
                                                        name="report_id">
                                                    <input type="hidden" name="admin_name"
                                                        value="{{ auth()->user()->name }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn btn-danger">Confirm</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Offcanvas -->

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
        <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/moment/moment-timezone-with-data-10-year-range.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    @endsection

    {{-- Page JS --}}
    @section('page_js')
        <script src="{{ asset('assets/js/action-documentation-view.js') }} "></script>
        {{-- <script src="{{ asset('assets/js/ui-modals.js') }}"></script> --}}
    @endsection
