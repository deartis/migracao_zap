@php
    use Carbon\Carbon;
    Carbon::setLocale('pt_BR');
    $user = auth()->user();
    $token = $user->id;
@endphp
    <!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title></title>
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --mobile-breakpoint: 992px;
        }
        body {
            min-height: 100vh;
            overflow-x: hidden;
           /* font-family: "Trebuchet MS", sans-serif;*/
        }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            margin-right: 15px;
        }
        .nav-item .nav-link {
            color: #333;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            /* Remova white-space: nowrap; daqui */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .nav-item .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-heading {
            font-weight: bold;
            color: #6c757d;
            padding: 10px 15px;
            margin-top: 10px;
            border-bottom: 1px solid #dee2e6;
            /* Remova white-space: nowrap; daqui */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .nav-link i {
            flex-shrink: 0;
            min-width: 1.25em;
            text-align: center;
        }

        .nav-link span {
            line-height: 1.2;
            padding-left: 5px;
        }
        .chart-container {
            position: relative;
            height: 200px;
            width: 200px;
            margin: 0 auto;
            max-width: 100%;
        }
        .chart-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #20c997;
            display: inline-block;
            margin-right: 5px;
        }
        .table-actions {
            max-height: 300px;
            overflow-y: auto;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #198754;
            display: flex;
            align-items: center;
            padding: 15px;
        }
        .logo img {
            width: 30px;
            margin-right: 10px;
        }
        .menu-toggle {
            cursor: pointer;
        }
        .mobile-menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: #495057;
        }
        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-heading,
        .sidebar.collapsed .logo span {
            display: none;
        }
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Responsividade */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }
            .sidebar.mobile-active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-toggle {
                display: block;
            }
            .menu-toggle {
                display: none;
            }
            .sidebar-overlay.active {
                display: block;
            }
            .chart-container {
                height: 150px;
                width: 150px;
            }
        }

        @media (max-width: 767.98px) {
            .chart-container {
                height: 120px;
                width: 120px;
            }
            .chart-label {
                font-size: 18px;
            }
            .status-container {
                margin-top: 1rem;
                justify-content: flex-start !important;
            }
            .table-responsive {
                width: 100%;
                overflow-x: auto;
            }
        }

        @media (max-width: 575.98px) {
            .card {
                margin-bottom: 1rem;
            }
        }

        /* Tooltips para o menu colapsado */
        .sidebar.collapsed .nav-item {
            position: relative;
        }
        .sidebar.collapsed .nav-item:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            white-space: nowrap;
            z-index: 1001;
        }

    </style>
</head>
<body>
@include('parts.sidebar')
<!-- Main Content -->
<div class="main-content" id="mainContent">
    @include('parts.header')
    <div class="container-fluid">
        <div>
            @yield('content')
        </div>
    </div>
</div>
@stack('scriptvar')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.whatsappApiToken = "{{ $token }}";
    window.whatsappApiUrl = "{{ $whatsappApiUrl }}";
    console.log(window.usoPacote);

    document.addEventListener('DOMContentLoaded', function () {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const isClickInsideMenu = sidebar.contains(event.target) || menuToggle.contains(event.target);

            if (!isClickInsideMenu && window.innerWidth < 992 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Adjust on window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('active');
            }
        });
    });
</script>

@vite('resources/js/chart-sidebar.js')
</body>
</html>
