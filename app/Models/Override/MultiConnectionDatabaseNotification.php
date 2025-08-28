<?php

namespace App\Models\Override;

use Illuminate\Notifications\DatabaseNotification;

class MultiConnectionDatabaseNotification extends DatabaseNotification
{
    protected $connection = 'mysql_smc';

    protected $keyType = 'string';

    protected $primaryKey = 'id';

    protected $table = 'notifications';

    public function notifiable()
    {
        return $this->morphTo();
    }
}