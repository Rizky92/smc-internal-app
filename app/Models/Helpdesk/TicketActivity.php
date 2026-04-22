<?php

namespace App\Models\Helpdesk;

use App\Database\Eloquent\Model;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'ticket_activities';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'causer_id',
        'type',
        'description',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    const TYPE_LABELS = [
        'created'          => 'Tiket dibuat',
        'assigned'         => 'Teknisi di-assign',
        'status_changed'   => 'Status diperbarui',
        'priority_changed' => 'Prioritas diperbarui',
        'sla_breached'     => 'SLA dilanggar',
        'note_added'       => 'Catatan ditambahkan',
        'escalated'        => 'Tiket dieskalasi',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /** Null berarti aksi dilakukan sistem (SLA breach otomatis, dll) */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id', 'id_user');
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getCauserDisplayNameAttribute(): string
    {
        return $this->causer ? $this->causer->nama : 'Sistem';
    }
}
