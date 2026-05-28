<?php

namespace App\Infrastructure\Ticket\Repositories;

use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Models\Helpdesk\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * EloquentTicketRepository — implementasi konkret dari TicketRepositoryInterface.
 * Tinggal di Infrastructure layer karena bergantung pada Eloquent (framework detail).
 *
 * Binding ke interface dilakukan di TicketServiceProvider:
 *   $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
 */
final class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function findById(int $id): ?Ticket
    {
        return Ticket::with([
            'category',
            'department',
            'reporter',
            'assignee',
            'createdBy',
        ])->find($id);
    }

    public function findByTicketNumber(string $ticketNumber): ?Ticket
    {
        return Ticket::where('ticket_number', $ticketNumber)->first();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Ticket::with(['category', 'department', 'assignee', 'reporter'])
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->paginate($perPage);
    }

    public function save(Ticket $ticket): Ticket
    {
        $ticket->save();

        return $ticket;
    }

    public function findBreachedAndUnmarked(): Collection
    {
        return Ticket::active()
            ->whereNull('sla_breached_at')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->with(['assignee', 'department', 'category'])
            ->get();
    }

    public function findNearBreachActive(int $withinHours = 2): Collection
    {
        return Ticket::active()
            ->whereNull('sla_breached_at')
            ->whereNotNull('sla_due_at')
            ->whereBetween('sla_due_at', [now(), now()->addHours($withinHours)])
            ->with(['assignee'])
            ->get();
    }

    public function findUnassignedOlderThan(int $hours): Collection
    {
        return Ticket::open()
            ->whereNull('assignee_id')
            ->where('created_at', '<', now()->subHours($hours))
            ->with(['department', 'category'])
            ->get();
    }

    public function getSummaryStats(): array
    {
        $counts = Ticket::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'open'            => $counts['open'] ?? 0,
            'progress'        => $counts['progress'] ?? 0,
            'waiting'         => $counts['waiting'] ?? 0,
            'resolved'        => $counts['resolved'] ?? 0,
            'closed'          => $counts['closed'] ?? 0,
            'unassigned'      => Ticket::active()->unassigned()->count(),
            'sla_breached'    => Ticket::active()->slaBreached()->count(),
            'sla_near_breach' => Ticket::slaNearBreach(2)->count(),
        ];
    }

    public function getTechnicianStats(Carbon $from, Carbon $to): Collection
    {
        return DB::table('tickets')
            ->join('users', 'users.id', '=', 'tickets.assignee_id')
            ->whereBetween('tickets.created_at', [$from, $to])
            ->whereNotNull('tickets.assignee_id')
            ->selectRaw("
                users.id,
                users.name,
                COUNT(*) as total_assigned,
                SUM(CASE WHEN tickets.status IN ('resolved','closed') THEN 1 ELSE 0 END) as total_resolved,
                SUM(CASE WHEN tickets.sla_breached_at IS NOT NULL THEN 1 ELSE 0 END) as total_sla_breach,
                AVG(
                    CASE WHEN tickets.resolved_at IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)
                    ELSE NULL END
                ) as avg_resolution_minutes
            ")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_resolved')
            ->get();
    }
}
