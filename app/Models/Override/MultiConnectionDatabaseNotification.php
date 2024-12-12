<?php


namespace App\Models\Override;

use Illuminate\Notifications\DatabaseNotification as DatabaseNotification;

class MultiConnectionDatabaseNotification extends DatabaseNotification
{
    protected $connection = 'mysql_smc';

    protected $table = 'notifications';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    /**
     * Get the notifiable entity that the notification belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
