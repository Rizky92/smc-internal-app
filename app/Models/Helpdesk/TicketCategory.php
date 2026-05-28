<?php

namespace App\Models\Helpdesk;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\EloquentEagerLimit\Relations\HasMany;

class TicketCategory extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'ticket_categories';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'code',
        'color',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getTextColorAttribute(): string
    {
        // Konversi hex ke RGB, tentukan luminance, pilih hitam atau putih
        $hex = ltrim($this->color, '#');
        [$r, $g, $b] = array_map('hexdec', str_split($hex, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#1a1a1a' : '#ffffff';
    }
}
