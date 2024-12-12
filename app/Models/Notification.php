<?php

namespace App\Models;

use App\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $connection = 'mysql_smc';

    protected $table = 'notifications';
}
