<?php

namespace App\Domain\Ticket\Repositories;

use App\Models\Helpdesk\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ini adalah "port" di Clean Architecture.
 * Domain layer tidak tahu apakah implementasinya Eloquent, PDO, atau API.
 * Application layer hanya bergantung pada interface ini — bukan Eloquent langsung.
 */
interface TicketRepositoryInterface
{
    public function findById(int $id): ?Ticket;

    public function findByTicketNumber(string $ticketNumber): ?Ticket;

    /**
     * Daftar tiket dengan filter dinamis dan paginasi.
     *
     * @param array{
     *     status?: string,
     *     priority?: string,
     *     category_id?: int,
     *     department_id?: int,
     *     assignee_id?: int,
     *     search?: string,
     * } $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function save(Ticket $ticket): Ticket;

    /** Tiket yang SLA-nya breach tapi belum di-mark (untuk scheduled job) */
    public function findBreachedAndUnmarked(): Collection;

    /** Tiket yang mendekati breach untuk notifikasi early warning */
    public function findNearBreachActive(int $withinHours = 2): Collection;

    /** Tiket unassigned yang sudah lebih dari N jam */
    public function findUnassignedOlderThan(int $hours): Collection;

    /** Statistik summary untuk dashboard */
    public function getSummaryStats(): array;

    /** Performa per teknisi untuk laporan */
    public function getTechnicianStats(Carbon $from, Carbon $to): Collection;
}
