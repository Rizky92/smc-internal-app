<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>{{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('css/home.css') }}" />
    </head>

    <body>
        <!-- This example requires Tailwind CSS v3.0+ -->
        <div class="isolate bg-white">
            <div class="absolute inset-x-0 top-[-10rem] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[-20rem]">
                <svg
                    class="relative left-[calc(50%-11rem)] -z-10 h-[21.1875rem] max-w-none -translate-x-1/2 rotate-[30deg] sm:left-[calc(50%-30rem)] sm:h-[42.375rem]"
                    viewBox="0 0 1155 678"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill="url(#45de2b6b-92d5-4d68-a6a0-9b9b2abad533)"
                        fill-opacity=".3"
                        d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
                    <defs>
                        <linearGradient id="45de2b6b-92d5-4d68-a6a0-9b9b2abad533" x1="1155.49" x2="-78.208" y1=".177" y2="474.645" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#34d399"></stop>
                            <stop offset="1" stop-color="#34d399"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <main>
                <div class="relative px-6 lg:px-8 grid place-content-center h-screen">
                    <div class="max-w-3xl">
                        <h1 class="text-4xl font-bold tracking-tight sm:text-center sm:text-6xl text-emerald-900">
                            {{ config('app.name') }}
                        </h1>
                        <div class="mt-8 flex gap-x-4 sm:justify-center">
                            @auth
                                <a
                                    href="{{ route('admin.dashboard') }}"
                                    class="inline-block rounded-full bg-emerald-600 px-8 py-4 text-2xl font-semibold uppercase leading-7 text-white shadow-sm ring-1 ring-emerald-600 hover:bg-emerald-700 hover:ring-emerald-700">
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-block rounded-full bg-emerald-600 px-8 py-4 text-2xl font-semibold uppercase leading-7 text-white shadow-sm ring-1 ring-emerald-600 hover:bg-emerald-700 hover:ring-emerald-700">
                                    Login
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
