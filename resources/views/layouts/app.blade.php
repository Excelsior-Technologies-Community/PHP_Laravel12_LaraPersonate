<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @if(session()->has('impersonate_original_id'))
            <div class="bg-gradient-to-r from-red-600 to-amber-600 text-white text-center py-2.5 px-4 flex items-center justify-between sticky top-0 z-50 shadow-md">
                <div class="text-xs font-black tracking-wider uppercase">
                    ⚠️ ATTENTION: Active Security Boundary Override • You are currently impersonating: <span class="underline select-all ml-1 font-bold">{{ Auth::user()->name }}</span>
                </div>
                <form action="{{ route('admin.impersonate.stop') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-white hover:bg-slate-100 text-red-600 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-md transition duration-200 shadow">
                        Leave Impersonation
                    </button>
                </form>
            </div>
        @endif

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            @if(session('error'))
                <div class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
                    <div class="bg-red-500 text-white p-4 rounded-xl text-xs font-bold shadow shadow-red-500/20">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>