@php
    use Carbon\Carbon;
    app()->setLocale('pt_BR');
    $user = auth()->user();
    $token = $user->id;
@endphp
@php

@endphp
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title></title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @include('parts.sidebar')
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        @include('parts.header')
        <div class="container-fluid">
            <div>
                @if (session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif

                @if (session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <x-whatsapp-button phone="5522998243838" label="Suporte" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        window.whatsappApiToken = "{{ $token }}";
        window.whatsappApiUrl = "{{ $whatsappApiUrl }}";

        //console.log(window.whatsappApiUrl );

        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideMenu = sidebar.contains(event.target) || menuToggle.contains(event
                    .target);

                if (!isClickInsideMenu && window.innerWidth < 992 && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            });

            // Adjust on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>
    @stack('scriptvar')
    @vite('resources/js/chart-sidebar.js')
    @vite('resources/js/fix-placeholders.js')
    {{--@vite('resources/js/msgmassacontatos.js')--}}
    @stack('scripts')
</body>

</html>
