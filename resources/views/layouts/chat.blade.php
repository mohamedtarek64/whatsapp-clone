<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title id="page-title">{{ config('app.name', 'WhatsApp') }}</title>
        <link rel="icon" href="https://static.whatsapp.net/rsrc.php/v3/yz/r/ujTY9i_Jhs1.png" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CDN Font Awesome -->
        <script src="https://kit.fontawesome.com/a43498efe3.js" crossorigin="anonymous"></script>

        @livewireStyles
        <style>
            body { 
                font-family: 'Outfit', sans-serif;
                background-color: #d1d7db;
                overflow: hidden;
            }
            .whatsapp-green-strip {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 127px;
                background-color: #00a884;
                z-index: -1;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="whatsapp-green-strip"></div>

        <div class="h-screen w-screen flex items-center justify-center p-0 md:p-4 lg:p-6">
            <div class="w-full h-full max-w-[1700px] bg-white shadow-2xl flex overflow-hidden rounded-none md:rounded-sm">
                {{ $slot }}
            </div>
        </div>

        @stack('modals')
        @livewireScripts
        @stack('js')
    </body>
</html>
