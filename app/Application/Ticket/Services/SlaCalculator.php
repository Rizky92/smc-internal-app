<?php

namespace App\Application\Ticket\Services;

use App\Application\Ticket\ValueObjects\SlaDeadline;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Models\Helpdesk\SlaPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * SlaCalculator — Application Service.
 *
 * Tanggung jawab tunggal: menghitung deadline SLA berdasarkan
 * priority dan waktu pembuatan tiket.
 *
 * Cache policy digunakan agar tidak query DB setiap kali tiket dibuat.
 * TTL 1 jam cukup karena sla_policies jarang berubah.
 */
final class SlaCalculator
{
    private const CACHE_TTL = 3600; // 1 jam dalam detik

    public function calculate(string $priority, Carbon $createdAt): SlaDeadline
    {
        [$responseHours, $resolutionHours] = $this->getHoursForPriority($priority);

        return SlaDeadline::calculate(
            createdAt: $createdAt,
            priority: $priority,
            responseHours: $responseHours,
            resolutionHours: $resolutionHours,
        );
    }

    /**
     * Ambil konfigurasi jam dari DB dengan cache.
     * Fallback ke nilai default di enum jika baris tidak ditemukan.
     *
     * @return array{int, int} [responseHours, resolutionHours]
     */
    private function getHoursForPriority(string $priority): array
    {
        $cacheKey = "sla_policy_{$priority}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($priority) {
            $policy = SlaPolicy::forPriority($priority);

            return [
                $policy?->response_hours ?? TicketPriority::defaultResponseHours($priority),
                $policy?->resolution_hours ?? TicketPriority::defaultResolutionHours($priority),
            ];
        });
    }

    /** Buang cache ketika admin mengubah sla_policies di UI */
    public function invalidateCache(?string $priority = null): void
    {
        if ($priority) {
            Cache::forget("sla_policy_{$priority}");

            return;
        }

        $priorities = ['critical', 'high', 'medium', 'low'];
        foreach ($priorities as $p) {
            Cache::forget("sla_policy_{$p}");
        }
    }
}
