<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}" />
        <title>Login - {{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('css/login.a1.css') }}" />
    </head>

    <body>
        <div class="flex min-h-full items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md space-y-8">
                <div>
                    <img class="mx-auto h-12 w-auto" src="{{ asset('img/logo.png') }}" alt="Samarinda Medika Citra" />
                    <h1 class="text-center text-emerald-700 mt-2">Samarinda Medika Citra</h1>
                    <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-stone-900">
                        Silahkan login ke
                        <br />
                        {{ config('app.name') }}
                    </h2>
                </div>
                <form class="mt-8 space-y-6" action="#" method="POST" action="{{ route('login') }}" autocomplete="off">
                    @error('user')
                        <span class="mt-4 block text-center text-sm text-red-500 font-medium">
                            {{ $message }}
                        </span>
                    @enderror

                    <input autocomplete="false" name="__hidden" type="text" style="display: none" />
                    @csrf
                    <input type="hidden" name="remember" value="true" />
                    <div class="-space-y-px rounded-md shadow-sm">
                        <div>
                            <label for="username" class="sr-only">Username</label>
                            <input
                                id="username"
                                name="user"
                                type="password"
                                autocomplete="off"
                                autofocus
                                required
                                class="relative block w-full appearance-none rounded-none rounded-t-md border border-stone-300 px-3 py-2 text-stone-900 placeholder-stone-500 focus:z-10 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500 sm:text-sm"
                                placeholder="Username" />
                        </div>
                        <div>
                            <label for="password" class="sr-only">Password</label>
                            <input
                                id="password"
                                name="pass"
                                type="password"
                                autocomplete="off"
                                required
                                class="relative block w-full appearance-none rounded-none rounded-b-md border border-stone-300 px-3 py-2 text-stone-900 placeholder-stone-500 focus:z-10 focus:border-emerald-500 focus:outline-none focus:ring-emerald-500 sm:text-sm"
                                placeholder="Password" />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="group relative flex w-full justify-center rounded-md border border-transparent bg-emerald-600 py-2 px-4 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
