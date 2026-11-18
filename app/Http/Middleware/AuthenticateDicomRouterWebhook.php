<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SATUSEHAT DICOM Router mengirim HTTP Basic Auth dari `webhook_user` dan
 * `webhook_password` di router.conf, bukan kredensial user aplikasi ini,
 * sehingga middleware `auth.basic` bawaan Laravel tidak bisa dipakai.
 */
class AuthenticateDicomRouterWebhook
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $username = (string) config('dicom-router.webhook.username');
        $password = (string) config('dicom-router.webhook.password');

        // Endpoint sengaja dibuka tanpa autentikasi.
        if ($username === '' && $password === '') {
            return $next($request);
        }

        $diharapkan = $username.':'.$password;
        $diberikan = ((string) $request->getUser()).':'.((string) $request->getPassword());

        abort_if(! hash_equals($diharapkan, $diberikan), 401, 'Kredensial webhook DICOM Router tidak valid.');

        return $next($request);
    }
}
