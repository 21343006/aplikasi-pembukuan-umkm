<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>{{ $title ?? 'Pembukuan UMKM' }}</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="/assets/img/favicon.png" rel="icon">
    <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin - v2.4.1
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="/dashboard" class="logo d-flex align-items-center">
                <img src="/assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">Pembukuan UMKM</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div><!-- End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="/assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
                        <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
                    </a><!-- End Profile Image Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>{{ Auth::user()->name }}</h6>
                            <span>Web Designer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a wire:navigate class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}"
                    href="{{ route('dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li><!-- End Dashboard Nav -->

            <li class="nav-heading">Menu Utama</li>

            <li class="nav-item">
                <a wire:navigate class="nav-link {{ request()->routeIs('incomes') ? '' : 'collapsed' }}"
                    href="{{ route('incomes') }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Pemasukan</span>
                </a>
            </li>

            <li class="nav-item">
                <a wire:navigate class="nav-link {{ request()->routeIs('expenditures') ? '' : 'collapsed' }}"
                    href="{{ route('expenditures') }}">
                    <i class="bi bi-cart-dash"></i>
                    <span>Pengeluaran</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('modal.page', 'modal.awal', 'fixed.cost') ? '' : 'collapsed' }}"
                    data-bs-target="#modal-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-wallet2"></i><span>Modal</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="modal-nav"
                    class="nav-content collapse {{ request()->routeIs('modal.page', 'modal.awal', 'fixed.cost') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a wire:navigate href="{{ route('modal.page') }}"
                            class="{{ request()->routeIs('modal.page') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Data Modal</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('modal.awal') }}"
                            class="{{ request()->routeIs('modal.awal') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Modal Awal</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('fixed.cost') }}"
                            class="{{ request()->routeIs('fixed.cost') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Biaya Tetap</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Modal Nav -->

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('laporan.harian', 'laporan.bulanan', 'laporan.tahunan', 'profit.loss') ? '' : 'collapsed' }}"
                    data-bs-target="#laporan-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-journal-text"></i><span>Laporan</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="laporan-nav"
                    class="nav-content collapse {{ request()->routeIs('laporan.harian', 'laporan.bulanan', 'laporan.tahunan', 'profit.loss') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a wire:navigate href="{{ route('laporan.harian') }}"
                            class="{{ request()->routeIs('laporan.harian') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Laporan Harian</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('laporan.bulanan') }}"
                            class="{{ request()->routeIs('laporan.bulanan') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Laporan Bulanan</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('laporan.tahunan') }}"
                            class="{{ request()->routeIs('laporan.tahunan') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Laporan Tahunan</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('profit.loss') }}"
                            class="{{ request()->routeIs('profit.loss') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Laba Rugi</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Laporan Nav -->

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bep.form', 'irr.analysis') ? '' : 'collapsed' }}"
                    data-bs-target="#analisis-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-graph-up"></i><span>Analisis</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="analisis-nav"
                    class="nav-content collapse {{ request()->routeIs('bep.form', 'irr.analysis') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a wire:navigate href="{{ route('bep.form') }}"
                            class="{{ request()->routeIs('bep.form') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Break Even Point</span>
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ route('irr.analysis') }}"
                            class="{{ request()->routeIs('irr.analysis') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>IRR</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Analisis Nav -->

        </ul>

    </aside><!-- End Sidebar -->

    {{ $slot }}
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Pembukuan UMKM</span></strong>. All Rights Reserved
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/vendor/chart.js/chart.min.js"></script>
    <script src="/assets/vendor/echarts/echarts.min.js"></script>
    <script src="/assets/vendor/quill/quill.min.js"></script>
    <script src="/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="/assets/js/main.js"></script>

</body>

</html>