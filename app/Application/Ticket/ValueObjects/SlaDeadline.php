<?php

namespace App\Application\Ticket\ValueObjects;

use Carbon\Carbon;

/**
 * Value Object — immutable, tidak punya identity.
 * Merepresentasikan sepasang deadline SLA (response + resolution)
 * dan semua kalkulasi turunannya.
 */
final class SlaDeadline
{
    public Carbon $responseDue;

    public Carbon $resolutionDue;

    public function __construct(
        Carbon $responseDue,
        Carbon $resolutionDue
    ) {
        $this->responseDue = $responseDue;
        $this->resolutionDue = $resolutionDue;
    }

    public static function calculate(
        Carbon $createdAt,
        string $priority,
        int $responseHours,
        int $resolutionHours
    ): self {
        return new self(
            $createdAt->copy()->addHours($responseHours),
            $createdAt->copy()->addHours($resolutionHours)
        );
    }

    /** Berapa persen waktu SLA yang sudah terpakai (0–100) */
    public function percentageElapsed(Carbon $from, Carbon $now): int
    {
        $total = $from->diffInMinutes($this->resolutionDue);
        $elapsed = $from->diffInMinutes($now);

        if ($total <= 0) {
            return 100;
        }

        return min(100, (int) round(($elapsed / $total) * 100));
    }

    /** Apakah deadline resolution sudah lewat */
    public function isResolutionBreached(Carbon $now): bool
    {
        return $now->gt($this->resolutionDue);
    }

    /** Apakah deadline response sudah lewat */
    public function isResponseBreached(Carbon $now): bool
    {
        return $now->gt($this->responseDue);
    }

    /** Apakah mendekati breach dalam N jam ke depan */
    public function isNearBreach(Carbon $now, int $withinHours = 2): bool
    {
        return ! $this->isResolutionBreached($now)
            && $now->copy()->addHours($withinHours)->gte($this->resolutionDue);
    }

    /** Level severity: ok | warn | breach */
    public function severity(Carbon $from, Carbon $now): string
    {
        if ($this->isResolutionBreached($now)) {
            return 'breach';
        }
        if ($this->percentageElapsed($from, $now) >= 75) {
            return 'warn';
        }

        return 'ok';
    }

    /** Label sisa waktu untuk ditampilkan di UI */
    public function remainingLabel(Carbon $now): string
    {
        if ($this->isResolutionBreached($now)) {
            $hours = (int) $now->diffInHours($this->resolutionDue);

            return "Breach {$hours}j lalu";
        }

        $totalMinutes = (int) $now->diffInMinutes($this->resolutionDue);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours >= 24) {
            return intdiv($hours, 24).' hari';
        }

        return $hours > 0 ? "{$hours}j {$minutes}m" : "{$minutes} mnt";
    }
}
