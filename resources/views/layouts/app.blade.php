
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>North College Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100 font-sans">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        @include('components.sidebar')

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col">

            <!-- NAVBAR -->
            @include('components.navbar')

            <!-- CONTENT -->
            <main class="p-6">

                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>

