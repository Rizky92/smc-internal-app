<?php

namespace App\Models\Helpdesk;

use App\Database\Eloquent\Concerns\Searchable;
use App\Database\Eloquent\Model;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\ValueObjects\TicketSla;
use App\Models\Aplikasi\User;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use Searchable;
    use SoftDeletes;

    protected $connection = 'mysql_smc';

    protected $table = 'tickets';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    const STATUS_OPEN     = 'open';
    const STATUS_PROGRESS = 'progress';
    const STATUS_WAITING  = 'waiting';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED   = 'closed';

    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_HIGH     = 'high';
    const PRIORITY_MEDIUM   = 'medium';
    const PRIORITY_LOW      = 'low';

    const STATUS_LABELS = [
        self::STATUS_OPEN     => 'Open',
        self::STATUS_PROGRESS => 'In Progress',
        self::STATUS_WAITING  => 'Menunggu Konfirmasi',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_CLOSED   => 'Closed',
    ];

    const PRIORITY_LABELS = [
        self::PRIORITY_CRITICAL => 'Critical',
        self::PRIORITY_HIGH     => 'High',
        self::PRIORITY_MEDIUM   => 'Medium',
        self::PRIORITY_LOW      => 'Low',
    ];

    const PRIORITY_COLORS = [
        self::PRIORITY_CRITICAL => '#E24B4A',
        self::PRIORITY_HIGH     => '#EF9F27',
        self::PRIORITY_MEDIUM   => '#378ADD',
        self::PRIORITY_LOW      => '#888780',
    ];

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'category_id',
        'priority',
        'status',
        'department_id',
        'location',
        'reporter_id',
        'reporter_name',
        'reporter_phone',
        'assignee_id',
        'created_by',
        'sla_due_at',
        'sla_response_due_at',
        'first_responded_at',
        'sla_breached_at',
        'assigned_at',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_due_at'          => 'datetime',
            'sla_response_due_at' => 'datetime',
            'first_responded_at'  => 'datetime',
            'sla_breached_at'     => 'datetime',
            'assigned_at'         => 'datetime',
            'resolved_at'         => 'datetime',
            'closed_at'           => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'department_id', 'id');
    }

    /** User yang melaporkan (jika punya akun) */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id', 'id_user');
    }

    /** Teknisi yang sedang mengerjakan */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id_user');
    }

    /** Alias untuk assignee, berguna saat proses reassignment */
    public function previousAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id_user');
    }

    /** User yang menginput tiket (bisa berbeda dengan reporter) */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    /**
     * Komentar yang bisa dilihat pelapor (bukan internal)
     */
    public function publicComments(): HasMany
    {
        return $this->hasMany(TicketComment::class)
            ->where('is_internal', false)
            ->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function sla(): TicketSla
    {
        return new TicketSla(
            $this->created_at,
            $this->sla_due_at,
            $this->sla_response_due_at,
            $this->status,
            $this->sla_breached_at
        );
    }

    public function scopeOpen(Builder $query): void
    {
        $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', self::STATUS_PROGRESS);
    }

    public function scopeWaiting(Builder $query): void
    {
        $query->where('status', self::STATUS_WAITING);
    }

    public function scopeResolved(Builder $query): void
    {
        $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeClosed(Builder $query): void
    {
        $query->where('status', self::STATUS_CLOSED);
    }

    /** Tiket yang masih aktif (belum resolved / closed) */
    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('status', [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function scopeCritical(Builder $query): void
    {
        $query->where('priority', self::PRIORITY_CRITICAL);
    }

    public function scopeByPriority(Builder $query, string $priority): void
    {
        $query->where('priority', $priority);
    }

    public function scopeUnassigned(Builder $query): void
    {
        $query->whereNull('assignee_id');
    }

    public function scopeAssignedTo(Builder $query, int $userId): void
    {
        $query->where('assignee_id', $userId);
    }

    public function scopeSlaBreached(Builder $query): void
    {
        $query->whereNotNull('sla_breached_at')
            ->active();
    }

    public function scopeSlaNearBreach(Builder $query, int $withinHours = 2): void
    {
        $query->whereNull('sla_breached_at')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<=', now()->addHours($withinHours))
            ->active();
    }

    public function scopeByDepartment(Builder $query, int $departmentId): void
    {
        $query->where('department_id', $departmentId);
    }

    public function scopeByCategory(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    protected function searchColumns(): array
    {
        return [
            'ticket_number',
            'title',
            'description',
            'reporter_name',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isProgress(): bool
    {
        return $this->status === self::STATUS_PROGRESS;
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function isAssigned(): bool
    {
        return ! is_null($this->assignee_id);
    }

    // ----------------------------------------------------------------
    // SLA helpers
    // ----------------------------------------------------------------

    public function isSlaBreached(): bool
    {
        return $this->sla()->isBreached();
    }

    /**
     * Persentase SLA yang telah terpakai (0–100).
     * Dipakai untuk progress bar di UI.
     */
    public function getSlaPercentageAttribute(): int
    {
        return $this->sla()->percentage();
    }

    /**
     * Sisa waktu SLA dalam format human-readable.
     * Contoh: "2 jam 30 mnt", "Breach 4 jam lalu"
     */
    public function getSlaRemainingLabelAttribute(): string
    {
        return $this->sla()->remainingLabel();
    }

    /** Severity SLA untuk pewarnaan UI: ok | warn | breach */
    public function getSlaSeverityAttribute(): string
    {
        return $this->sla()->severity();
    }

    // ----------------------------------------------------------------
    // Accessors
    // ----------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY_LABELS[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITY_COLORS[$this->priority] ?? '#888780';
    }

    /**
     * Nama pelapor — dari relasi User jika ada, fallback ke kolom reporter_name.
     * Dipakai di view tanpa perlu kondisional manual.
     */
    public function getReporterDisplayNameAttribute(): string
    {
        return ($this->reporter ? $this->reporter->name : null) ?? $this->reporter_name ?? 'Tidak diketahui';
    }

    /**
     * Generate nomor tiket berikutnya: ITS-XXXX
     * Dipanggil dari CreateTicketAction sebelum insert.
     */
    public static function generateTicketNumber(): string
    {
        $last = static::withTrashed()
            ->where('ticket_number', 'like', 'ITS-%')
            ->orderByDesc('id')
            ->value('ticket_number');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'ITS-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
