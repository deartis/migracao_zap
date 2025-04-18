@php
    use Carbon\Carbon;
    Carbon::setLocale('pt_BR');
@endphp

    <!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">--}}
    <title></title>
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #25D366;
            --sidebar-bg: #f8f9fa;
            --hover-bg: #e9ecef;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            transition: all 0.3s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .menu-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            color: #212529;
            text-decoration: none;
            transition: all 0.2s;
        }

        .menu-item:hover {
            background-color: var(--hover-bg);
        }

        .menu-item.active {
            background-color: #e9ecef;
            border-left: 4px solid var(--primary-color);
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .dropdown-toggle::after {
            margin-left: auto;
        }

        .stats-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            height: 100%;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stats-card p {
            font-size: 1.2rem;
            margin-bottom: 0;
        }

        .card-success {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .card-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .card-primary {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
        }

        .menu-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background-color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: none;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .whatsapp-icon {
            color: #25D366;
        }

        .profile-section {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-clean {
            background-color: #ffc107;
            border-color: #ffc107;
            transition: all 0.3s;
        }

        .btn-clean:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }

            .menu-toggle {
                display: flex;
            }
        }

        .section-title {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #495057;
        }
    </style>
</head>
<body>
@include('parts.sidebar')

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
</body>
</html>
