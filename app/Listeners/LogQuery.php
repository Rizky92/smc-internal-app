<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use Str;

class LogQuery
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Database\Events\QueryExecuted $event
     * @return void
     */
    public function handle($event)
    {
        $sql = Str::of($event->sql)
            ->replaceArray('?', $event->bindings);

        Log::info($sql);
    }
}
