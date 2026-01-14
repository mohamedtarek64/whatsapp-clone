<!DOCTYPE html>

{{-- La plantilla utiliza la función "str_replace" de PHP para establecer el atributo lang del elemento html según el idioma establecido en la aplicación. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- La plantilla incluye una metaetiqueta csrf-token con el valor del token CSRF (Cross-Site Request Forgery) actual de la aplicación, que se utiliza para proteger contra ataques de falsificación de solicitudes entre sitios. --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- La plantilla establece el título de la página con la función config de Laravel, que permite acceder a la configuración de la aplicación. Si no se especifica un nombre para la aplicación, se usa el valor predeterminado "Laravel". --}}
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        {{-- La plantilla incluye enlaces a dos archivos CSS y JS externos con la función @vite de Laravel. Esta función es una forma de incluir archivos de recursos de manera más eficiente en una aplicación Laravel. --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- CDN Font Awesome -->
        <script src="https://kit.fontawesome.com/a43498efe3.js" crossorigin="anonymous"></script>

        <!-- Styles -->
        {{-- La plantilla incluye dos bloques de código Livewire (@livewireStyles y @livewireScripts), que son necesarios para integrar Livewire en la aplicación. Livewire es un marco de Laravel para construir aplicaciones web dinámicas sin tener que recargar la página. --}}
        @livewireStyles
    </head>
    <body class="font-sans antialiased">

        <div class="h-32 bg-teal-600">
        </div>

        {{-- Se agrega el $slot donde irá todo el contenido de la página dentro del layout llamado "chat" --}}
        {{-- La plantilla tiene una sección con el contenido de la página, que se incluye dentro del elemento {{ $slot }}. Esto permite que la plantilla sea reutilizable y se pueda incluir el contenido de la página en diferentes ubicaciones. --}}
        <div class="absolute left-0 top-6 w-screen">
            <div class="container max-w-screen-xl mx-auto">
                {{ $slot }}
            </div>
        </div>

        {{-- La plantilla tiene dos secciones de "empilamiento" (@stack), que se utilizan para añadir contenido adicional a la plantilla en las ubicaciones especificadas. En este caso, se utiliza para incluir modales y código JavaScript adicional en la página. --}}
        @stack('modals')

        {{-- La plantilla incluye dos bloques de código Livewire (@livewireStyles y @livewireScripts), que son necesarios para integrar Livewire en la aplicación. Livewire es un marco de Laravel para construir aplicaciones web dinámicas sin tener que recargar la página. --}}
        @livewireScripts

        @stack('js')
    </body>
</html>
