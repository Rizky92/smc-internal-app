<?php

namespace App\Models\Helpdesk;

use App\Database\Eloquent\Model;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql_smc';

    protected $table = 'ticket_comments';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'ticket_id',
        'id_user',
        'content',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** Hanya komentar yang terlihat pelapor */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_internal', false);
    }

    /** Hanya catatan internal IT */
    public function scopeInternal(Builder $query): void
    {
        $query->where('is_internal', true);
    }
}
