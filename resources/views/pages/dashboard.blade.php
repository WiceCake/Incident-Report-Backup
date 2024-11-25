@extends('layout.app')

@section('title', 'MUTI Group | Dashboard')

@section('content')

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
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
                        <div class="row">
                            <div class="col-lg-3 col-md-12 col-6 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="bx bx-error bx-26px"></i>
                                                </span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn p-0" type="button" id="cardOpt3"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                                    <a class="dropdown-item" href="http://localhost:5601/">View
                                                        More</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-1">Honeypot Interaction</p>
                                        <h4 class="card-title mb-3">{{ $logged_in['total'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="bx bx-exit bx-26px"></i>
                                                </span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn p-0" type="button" id="cardOpt6"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                                    <a class="dropdown-item" href="http://localhost:5601/">View
                                                        More</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-1">Attempted Logged In in Honeypot</p>
                                        <h4 class="card-title mb-3">{{ $logged_in_attempt['total'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="bx bx-error-circle bx-26px"></i>
                                                </span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn p-0" type="button" id="cardOpt3"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                                    <a class="dropdown-item" href="http://localhost:5601/">View
                                                        More</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-1">Honeypot User Events</p>
                                        <h4 class="card-title mb-3">{{ $events['total'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12 col-6 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class='bx bxs-component bx-26px'></i>
                                                </span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn p-0" type="button" id="cardOpt6"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
                                                    <a class="dropdown-item" href="http://localhost:5601/">View
                                                        More</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-1">Total Honeypot Threat Detected</p>
                                        <h4 class="card-title mb-3">
                                            {{ count($all_threats['data'] ?? []) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <!-- Total Revenue -->
                            {{-- <div class="col-12 col-xxl-12 order-2 order-md-3 order-xxl-2 mb-6">
                                <div class="card">
                                    <div class="row row-bordered g-0">
                                        <div class="col-lg-8">
                                            <div class="card-header d-flex align-items-center justify-content-between">
                                                <div class="card-title mb-0">
                                                    <h5 class="m-0 me-2">Total Revenue</h5>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn p-0" type="button" id="totalRevenue"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded bx-lg text-muted"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="totalRevenue">
                                                        <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                                                        <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                                        <a class="dropdown-item" href="javascript:void(0);">Share</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="totalRevenueChart" class="px-3"></div>
                                        </div>
                                        <div class="col-lg-4 d-flex align-items-center">
                                            <div class="card-body px-xl-9">
                                                <div class="text-center mb-6">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-outline-primary">
                                                            <script>
                                                                document.write(new Date().getFullYear() - 1);
                                                            </script>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item"
                                                                    href="javascript:void(0);">2021</a></li>
                                                            <li><a class="dropdown-item"
                                                                    href="javascript:void(0);">2020</a></li>
                                                            <li><a class="dropdown-item"
                                                                    href="javascript:void(0);">2019</a></li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div id="growthChart"></div>
                                                <div class="text-center fw-medium my-6">62% Company Growth</div>

                                                <div class="d-flex gap-3 justify-content-between">
                                                    <div class="d-flex">
                                                        <div class="avatar me-2">
                                                            <span class="avatar-initial rounded-2 bg-label-primary"><i
                                                                    class="bx bx-dollar bx-lg text-primary"></i></span>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <small>
                                                                <script>
                                                                    document.write(new Date().getFullYear() - 1);
                                                                </script>
                                                            </small>
                                                            <h6 class="mb-0">$32.5k</h6>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <div class="avatar me-2">
                                                            <span class="avatar-initial rounded-2 bg-label-info"><i
                                                                    class="bx bx-wallet bx-lg text-info"></i></span>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <small>
                                                                <script>
                                                                    document.write(new Date().getFullYear() - 2);
                                                                </script>
                                                            </small>
                                                            <h6 class="mb-0">$41.2k</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <!--/ Total Revenue -->
                            {{-- <div class="col-12 col-md-8 col-lg-12 col-xxl-12 order-3 order-md-2">
                                <div class="row">
                                    <div class="col-6 mb-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div
                                                    class="card-title d-flex align-items-start justify-content-between mb-4">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/paypal.png" alt="paypal"
                                                            class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt4"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end"
                                                            aria-labelledby="cardOpt4">
                                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                                More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="mb-1">Payments</p>
                                                <h4 class="card-title mb-3">$2,456</h4>
                                                <small class="text-danger fw-medium"><i class="bx bx-down-arrow-alt"></i>
                                                    -14.82%</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div
                                                    class="card-title d-flex align-items-start justify-content-between mb-4">
                                                    <div class="avatar flex-shrink-0">
                                                        <img src="../assets/img/icons/unicons/cc-primary.png"
                                                            alt="Credit Card" class="rounded" />
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn p-0" type="button" id="cardOpt1"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="cardOpt1">
                                                            <a class="dropdown-item" href="javascript:void(0);">View
                                                                More</a>
                                                            <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="mb-1">Transactions</p>
                                                <h4 class="card-title mb-3">$14,857</h4>
                                                <small class="text-success fw-medium"><i class="bx bx-up-arrow-alt"></i>
                                                    +28.14%</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div
                                                    class="d-flex justify-content-between align-items-center flex-sm-row flex-column gap-10">
                                                    <div
                                                        class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                                        <div class="card-title mb-6">
                                                            <h5 class="text-nowrap mb-1">Profile Report</h5>
                                                            <span class="badge bg-label-warning">YEAR 2022</span>
                                                        </div>
                                                        <div class="mt-sm-auto">
                                                            <span class="text-success text-nowrap fw-medium"><i
                                                                    class="bx bx-up-arrow-alt"></i> 68.2%</span>
                                                            <h4 class="mb-0">$84,686k</h4>
                                                        </div>
                                                    </div>
                                                    <div id="profileReportChart"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                        <div class="row">
                            <!-- Order Statistics -->
                            <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-1 me-2">Most Devices Interacted with Honeypot</h5>
                                        </div>
                                        {{-- <div class="dropdown">
                                            <button class="btn text-muted p-0" type="button" id="orederStatistics"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded bx-lg"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="orederStatistics">
                                                <a class="dropdown-item" href="javascript:void(0);">Select All</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Share</a>
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-center align-items-center mb-6">
                                            <div id="orderStatisticsChart"></div>
                                        </div>
                                        <ul class="p-0 m-0">
                                            @foreach ($devices as $key => $device)
                                                <li class="d-flex align-items-center mb-5">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <span class="avatar-initial rounded bg-label-primary"><i
                                                                class="bx
                                                                {{ $key == 'mobile' ? 'bx-mobile-alt' : 'bx-desktop' }}
                                                                "></i></span>
                                                    </div>
                                                    <div
                                                        class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                        <div class="me-2">
                                                            <h6 class="mb-0">{{ Str::ucfirst($key) ?? '' }}
                                                            </h6>
                                                            <small>Device</small>
                                                        </div>
                                                        <div class="user-progress">
                                                            <h6 class="mb-0">{{ $device->count() ?? '' }}</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!--/ Order Statistics -->

                            <!-- Expense Overview -->
                            <div class="col-md-6 col-lg-4 order-1 mb-6">
                                <div class="card h-100">
                                    <div class="card-header nav-align-top">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-1 me-2">Weekly Detection</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content p-0">
                                            <div class="tab-pane fade show active" id="navs-tabs-line-card-income"
                                                role="tabpanel">
                                                <div class="d-flex mb-6">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <span class="avatar-initial rounded bg-label-danger"><i
                                                                class="bx bx-chart bx-lg"></i></span>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Month of {{ \Carbon\Carbon::now()->format('F') }}
                                                        </p>
                                                        <div class="d-flex align-items-center">
                                                            <h6 class="mb-0 me-1">Total Threats Detected:</h6>
                                                            <small class="text-danger fw-medium">
                                                                {{ $weekly_threats }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="incomeChart"></div>
                                                <div class="text-center mt-10 gap-3">
                                                    <h6 class="mb-0">Week of the Month:
                                                        {{ \Carbon\Carbon::now()->weekOfMonth }}</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--/ Expense Overview -->

                            <!-- Transactions -->
                            <div class="col-md-6 col-lg-4 order-2 mb-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="card-title m-0 me-2">Detected Cookies</h5>
                                        {{-- <div class="dropdown">
                                            <button class="btn text-muted p-0" type="button" id="transactionID"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded bx-lg"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                                <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                                            </div>
                                        </div> --}}
                                    </div>
                                    <div class="card-body pt-4">
                                        <ul class="p-0 m-0">
                                            @if (count($cookies))
                                                @foreach ($cookies as $cookie)
                                                    <li class="d-flex align-items-center mb-6">
                                                        <div class="avatar flex-shrink-0 me-3">
                                                            <span class="avatar-initial rounded bg-label-primary"><i
                                                                    class="bx bx-user"></i></span>
                                                        </div>
                                                        <div
                                                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                                            <div class="me-2">
                                                                <h6 class="fw-normal mb-0">{{ $cookie->user_cookie }}</h6>
                                                                <div class="user-progress d-flex align-items-center gap-2">
                                                                    <small>Attempts: </small>
                                                                    <small>{{ count($cookie->content) }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <div class="mb-5 text-center">
                                                    No Cookie Found
                                                </div>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!--/ Transactions -->
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
    </div>
    <!-- / Layout wrapper -->

@endsection

@section('vendor_js')
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page_js')
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
@endsection
