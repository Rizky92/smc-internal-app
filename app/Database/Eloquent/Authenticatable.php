<?php

namespace App\Database\Eloquent;

use Illuminate\Auth\Authenticatable as AuthenticatableConcerns;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * @use \Spatie\Permission\Traits\HasRoles
 */
class Authenticatable extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use AuthenticatableConcerns;
    use Authorizable;
    use CanResetPassword;
    use MustVerifyEmail;
}
