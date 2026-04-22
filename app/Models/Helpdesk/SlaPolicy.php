<?php

namespace App\Models\Helpdesk;

use App\Database\Eloquent\Model;
use Carbon\Carbon;

class SlaPolicy extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'sla_policies';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'priority',
        'response_hours',
        'resolution_hours',
    ];

    protected function casts(): array
    {
        return [
            'response_hours'   => 'integer',
            'resolution_hours' => 'integer',
        ];
    }

    public static function forPriority(string $priority): ?self
    {
        return static::where('priority', $priority)->first();
    }

    public function getResolutionDeadline(Carbon $from): Carbon
    {
        return $from->copy()->addHours($this->resolution_hours);
    }

    public function getResponseDeadline(Carbon $from): Carbon
    {
        return $from->copy()->addHours($this->response_hours);
    }
}
