@extends('layout.app')

@section('title', 'MUTI Group | ' . $draft_id)

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
                            <!-- Securirity Events -->
                            <div class="col-12 mb-6" id="incidentReportsData">
                                <div class="card invoice-preview-card p-sm-12 p-6">
                                    <div class="card-body invoice-preview-header rounded">
                                        <div>
                                            <div class="mb-4 text-heading">
                                                <h5 class="fw-medium mb-2">Incident Report ID: {{ $report_data->report_id }}
                                                </h5>
                                                <div>
                                                    <span class="text-heading demo fw-bold fs-4 mb-3 d-block">Security Event
                                                        Name:</span>
                                                    <span
                                                        class="text-heading demo fw-bold fs-5 lh-1 me-5">{{ $report_data->threat_name }}</span>
                                                </div>
                                            </div>
                                            <hr>
                                            <div>
                                                <div class="mb-1 text-heading">
                                                    <span>Date Detected:</span>
                                                    <span
                                                        class="fw-medium">{{ $report_data->threat_data->timestamp }}</span>
                                                </div>
                                                <div class="mb-1 text-heading">
                                                    <span>Status:</span>
                                                    <span class="fw-medium">{{ $report_data->status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body px-0">

                                    </div>
                                    {{-- <div class="table-responsive border border-bottom-0 border-top-0 rounded mb-4">
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
                                                    <td class="text-wrap text-break">{{ $report_data->threat_data->threat }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Threat Level</td>
                                                    <td class="text-nowrap">{{ $report_data->threat_data->threat_level }}
                                                    </td>
                                                </tr>
                                                @if ($report_data->threat_data->others->btn_name)
                                                    <tr>
                                                        <td class="text-nowrap text-heading">Button Name</td>
                                                        <td class="text-nowrap">
                                                            {{ $report_data->threat_data->others->btn_name }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-nowrap text-heading">IP Address</td>
                                                    <td class="text-nowrap">{{ $report_data->threat_data->ip_address }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Cookies</td>
                                                    <td class="text-wrap text-break">
                                                        {{ $report_data->threat_data->others->cookies }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">Device</td>
                                                    <td class="text-nowrap">{{ $report_data->threat_data->others->device }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap text-heading">User Agent</td>
                                                    <td class="text-wrap text-break">
                                                        {{ $report_data->threat_data->others->user_agent }}</td>
                                                </tr>
                                                @if ($report_data->threat_data->others->url)
                                                    <tr>
                                                        <td class="text-nowrap text-heading">Url</td>
                                                        <td class="text-wrap text-break">
                                                            {{ $report_data->threat_data->others->url }}</td>
                                                    </tr>
                                                @endif
                                                @if ($report_data->threat_data->others->referrer_url)
                                                    <tr>
                                                        <td class="text-nowrap text-heading">Url Referrer</td>
                                                        <td class="text-wrap text-break">
                                                            {{ $report_data->threat_data->others->referrer_url }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div> --}}
                                    <hr class="mt-0 mb-6">
                                    <span class="fw-medium text-heading mb-4">Reports:</span>
                                    <div class="dataTables_scroll">
                                        <div class="table-responsive border border-bottom-0 border-top-0 rounded mb-4"
                                            style="position: relative; overflow: auto; width: 100%;">
                                            <table class="table no-footer" id="DataTables_Table_0"
                                                aria-describedby="DataTables_Table_0_info" style="width: 1645px;">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th class="text-truncate">Incident Name</th>
                                                        <th class="text-truncate">Threat Level</th>
                                                        <th class="text-truncate">IP Address</th>
                                                        <th class="text-truncate">Action Taken</th>
                                                        <th class="text-truncate">Created By</th>
                                                        <th class="text-truncate">Date Issued</th>
                                                        <th class="text-truncate">Others</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $threat_name = $report_actions->first()->threat;
                                                        $threat_level = $report_actions->first()->threat_level;
                                                        $ip_address = $report_actions->first()->ip_address;
                                                    @endphp
                                                    @foreach ($report_actions as $report)
                                                        @php
                                                            $checkReport = $report->date_completed ?? null;
                                                            $checkStatus = $report->status ?? null;
                                                            if ($checkReport || $checkStatus == 'Not Completed') {
                                                                continue;
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                {{ $report->threat_id ?? ($report->report_id ?? $report->id) }}
                                                            </td>
                                                            <td>
                                                                {{ $threat_name }}
                                                            </td>
                                                            <td>
                                                                {{ $threat_level }}
                                                            </td>
                                                            <td>
                                                                {{ $ip_address }}
                                                            </td>
                                                            <td>
                                                                {{ $report->action_type ?? var_dump($report) }}
                                                            </td>
                                                            <td>
                                                                {{ $report->admin_name ?? $report->admin_name }}
                                                            </td>
                                                            <td>
                                                                {{ $report->timestamp ?? $report->time_completed }}
                                                            </td>
                                                            <td class="text-wrap text-break">
                                                                <button data-bs-toggle="tooltip" class="btn btn-icon"
                                                                    data-bs-placement="top"
                                                                    title="Preview Additional Information"><i
                                                                        class="bx bx-show bx-md" data-bs-toggle="modal"
                                                                        data-bs-target="#basicModal2"></i></button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <hr class="mb-6">
                                        @if (count((array) $report_actions->get('draft_data')) ?? null)
                                            <span class="fw-medium text-heading"> Complete Actions: </span>
                                            <form class="mt-4"
                                                action="{{ route('report.action_documentation.complete_action') }}"
                                                method="POST">
                                                @csrf
                                                @foreach ($report_actions->get('draft_data')->actions as $action)
                                                    <label class="form-check mb-2">
                                                        <input type="hidden" class="form-check-input" name="draft_id"
                                                            value='{{ $draft_id }}'>
                                                        {{-- {{dd($action)}} --}}
                                                        @if ($action->status != 'Completed')
                                                            <input type="checkbox" class="form-check-input"
                                                                name="checkedValue[]" value='{{ $action->id }}'>
                                                        @endif
                                                        <span class="form-check-label">{{ $action->action_title }}
                                                            {{ $action->status != 'Completed' ? '' : '(Completed)' }}
                                                        </span>
                                                    </label>
                                                    {{-- <span class="d-block mb-3">{{ $loop->index + 1 }}.
                                                        {{ $action['action_title'] }}</span> --}}
                                                @endforeach
                                                @php
                                                    $countActions1 = count($actions);
                                                    $countUncompleted1 = count(
                                                        array_filter((array) $actions->all(), function ($data) {
                                                            return $data->status === 'Not Completed';
                                                        }),
                                                    );

                                                @endphp
                                                @if($countUncompleted1 != 0)
                                                <button type="submit" class="btn btn-primary mt-3">
                                                    Complete Task
                                                </button>
                                                @endif
                                            </form>
                                        @endif
                                    </div>

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

                                    {{-- @if (count((array) $report_data->draft_data))
                                        <div class="card-body px-0">
                                            <div class="row">
                                                <hr class="mt-0 mb-6">
                                                <div class="col-12">
                                                    <span class="fw-medium text-heading">Summary about the Incident:</span>
                                                    <p class="my-5">{{ $report_data->draft_data->summary_info }}</p>
                                                </div>
                                                <hr class="mt-6 mb-6">
                                                <div class="col-12">
                                                    <span class="fw-medium text-heading">Plans about the Incident:</span>
                                                    <p class="my-5">{{ $report_data->draft_data->plan_info }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif --}}
                                </div>
                            </div>
                            <!-- /Security Events -->

                            <!-- Security Events Actions -->
                            <div class="col-12 invoice-actions">
                                <div class="card">
                                    <div class="card-body">
                                        @php
                                            $countActions = count($actions);
                                            $countUncompleted = count(
                                                array_filter((array) $actions->all(), function ($data) {
                                                    return $data->status === 'Not Completed';
                                                }),
                                            );

                                        @endphp
                                        @if ($countUncompleted == 0)
                                            <button class="btn btn-primary d-grid w-100 mb-4" data-bs-toggle="modal"
                                                data-bs-target="#basicModal1">
                                                <span
                                                    class="d-flex align-items-center justify-content-center text-nowrap"><i
                                                        class="bx bx-check bx-sm me-2"></i>Complete</span>
                                            </button>
                                        @endif
                                        <button class="btn btn-label-success d-grid w-100"
                                            onclick="printDiv(incidentReportsData, '{{ $report_data->report_id }}')">
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
                                    <form method="POST" action="{{ route('report.incident.draft') }}"
                                        enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel1">Create Draft Report</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div>
                                                    {{-- <i class='bx bx-question-mark' style="font-size: 10rem"></i> --}}
                                                    @csrf
                                                    {{-- <input type="hidden" value="{{ $data->timestamp }}" id="threatTime"> --}}
                                                    {{-- <input type="hidden" value="{{ $data->threat }}" id="threatName"> --}}
                                                    {{-- <input type="hidden" value="{{ auth()->user()->username }}" name="username"> --}}
                                                    <input type="hidden" value="{{ $report_data->report_id }}"
                                                        name="incident_id">
                                                    <input type="hidden"
                                                        value="{{ $report_data->threat_data->threat_id }}"
                                                        name="security_event_id">
                                                    <div class="mb-6">
                                                        <label for="invoice-to" class="form-label">Incident Title*</label>
                                                        <input type="text" class="form-control" id="timestampDetected"
                                                            name="incident_title" value="" required />
                                                    </div>
                                                    <div class="mb-6">
                                                        <label for="invoice-from" class="form-label">From</label>
                                                        <input type="text" class="form-control" id="adminName"
                                                            name="admin_name" value="{{ auth()->user()->name }}"
                                                            readonly />
                                                    </div>
                                                    <div class="mb-6">
                                                        <label for="invoice-to" class="form-label">Summary of the
                                                            Incident*</label>
                                                        <textarea class="form-control" placeholder="Include any information here about the incident..." rows="2"
                                                            style="height: 157px;" name="summary_info" required></textarea>
                                                    </div>
                                                    <div class="mb-6">
                                                        <label for="invoice-to" class="form-label">Actions to be taken
                                                            about
                                                            the Incident*</label>
                                                        <div class="inputGroup mb-3" id="actionsContainer">
                                                            <input type="text" class="form-control w-100 mb-3"
                                                                name="actions[]" placeholder="Input action here..."
                                                                required />
                                                        </div>
                                                        <button type="button" class="btn btn-primary"
                                                            onclick="addInput(actionsContainer)">
                                                            Add More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn btn-primary">Create Report</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Create Draft -->
                        <div class="modal fade" id="basicModal1" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('report.action_documentation.completed') }}"
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
                                                    <span>Are you sure you want to take complete this report?</span>
                                                    <input type="hidden"
                                                        value="{{ $report_actions->get('old_data')->threat_id }}"
                                                        name="event_id">
                                                    <input type="hidden"
                                                        value="{{ $draft_id }}"
                                                        name="draft_id">
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
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel1">Additional Data</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if ($report_actions->get('old_data')->others ?? null)
                                            <span class="d-block mb-3"> Cookies:
                                                {{ $report_actions->get('old_data')->others->cookies }}
                                            </span>
                                        @endif
                                        @if ($report_actions->get('old_data')->others ?? null)
                                            <span class="d-block mb-3"> Device:
                                                {{ $report_actions->get('old_data')->others->device }} </span>
                                        @endif
                                        @if ($report_actions->get('report_data')->attachment_path ?? null)
                                            <div class="mb-3">
                                                <span class="d-block"> Attachments: </span>
                                                @if (count($report_actions->get('report_data')->attachment_path))
                                                    @foreach ($report_actions->get('report_data')->attachment_path as $file)
                                                        @php
                                                            $fileParts = explode('/', $file);
                                                            $lastPart = end($fileParts);
                                                        @endphp
                                                        <a class="d-block link-underline link-underline-opacity-0"
                                                            href="{{ asset($file) }}"
                                                            target="_blank">{{ $lastPart }}</a>
                                                    @endforeach
                                                @else
                                                    No attachment available
                                                @endif
                                            </div>
                                        @endif
                                        {{-- {{dd()}} --}}
                                        @if (count((array) $report_actions->get('draft_data')) ?? null)
                                            <span class="d-block mb-2"> Summary of the Incident: </span>
                                            <p class="">{{ $report_actions->get('draft_data')->summary_info }}</p>
                                        @endif
                                        @if (count((array) $report_actions->get('draft_data')) ?? null)
                                            <span class="d-block mb-2"> Actions: </span>
                                            @foreach ($report_actions->get('draft_data')->actions as $action)
                                                <span class="d-block mb-3">{{ $loop->index + 1 }}.
                                                    {{ $action->action_title }}</span>
                                            @endforeach
                                        @endif
                                    </div>
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
