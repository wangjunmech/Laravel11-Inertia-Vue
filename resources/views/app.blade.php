<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Font-Awesome CDN link --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
 
    <!-- 本地 Tailwind -->
    {{-- <link rel="stylesheet" href="../../tailwindcss.min.css"> --}}
    @vite('resources/js/app.js')
    @inertiaHead
    @routes
</head>

<body class="font-Montserrat bg-slate-100 text-slate-900 dark:bg-slate-700 dark:text-white">

    @inertia


</body>

</html>
