<?php

namespace App\Application\Ticket\Services;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\ValueObjects\TicketSla;
use App\Models\Helpdesk\SlaPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * SlaCalculator — Application Service.
 */
final class SlaCalculator
{
    private const CACHE_TTL = 3600; // 1 jam dalam detik

    public function calculate(string $priority, Carbon $createdAt): TicketSla
    {
        [$responseHours, $resolutionHours] = $this->getHoursForPriority($priority);

        return TicketSla::calculate(
            createdAt: $createdAt,
            responseHours: $responseHours,
            resolutionHours: $resolutionHours,
        );
    }

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
