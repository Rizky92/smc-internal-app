<?php

namespace App\Domain\Ticket\ValueObjects;

use App\Domain\Ticket\Enums\TicketStatus;
use Carbon\Carbon;

/**
 * TicketSla — Domain Value Object.
 * Menyimpan data SLA dan menangani semua kalkulasi terkait tenggat waktu.
 */
final class TicketSla
{
    private Carbon $createdAt;
    private ?Carbon $responseDue;
    private ?Carbon $resolutionDue;
    private string $status;
    private ?Carbon $breachedAt;

    public function __construct(
        Carbon $createdAt,
        ?Carbon $resolutionDue,
        ?Carbon $responseDue = null,
        string $status = TicketStatus::Open,
        ?Carbon $breachedAt = null
    ) {
        $this->createdAt = $createdAt;
        $this->resolutionDue = $resolutionDue;
        $this->responseDue = $responseDue;
        $this->status = $status;
        $this->breachedAt = $breachedAt;
    }

    /**
     * Factory method untuk menghitung deadline baru (digunakan saat CreateTicket).
     */
    public static function calculate(
        Carbon $createdAt,
        int $responseHours,
        int $resolutionHours
    ): self {
        return new self(
            $createdAt,
            $createdAt->copy()->addHours($resolutionHours),
            $createdAt->copy()->addHours($responseHours),
            TicketStatus::Open
        );
    }

    public function getResolutionDue(): ?Carbon
    {
        return $this->resolutionDue;
    }

    public function getResponseDue(): ?Carbon
    {
        return $this->responseDue;
    }

    public function isBreached(): bool
    {
        if ($this->breachedAt !== null) {
            return true;
        }

        if ($this->resolutionDue !== null && now()->gt($this->resolutionDue)) {
            return true;
        }

        return false;
    }

    public function isActive(): bool
    {
        return !in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    /**
     * Persentase SLA resolusi yang telah terpakai (0–100).
     */
    public function percentage(): int
    {
        if ($this->resolutionDue === null || !$this->isActive()) {
            return 0;
        }

        $total = $this->createdAt->diffInMinutes($this->resolutionDue);
        $elapsed = $this->createdAt->diffInMinutes(now());

        if ($total <= 0) {
            return 100;
        }

        return min(100, (int) round(($elapsed / $total) * 100));
    }

    /**
     * Sisa waktu SLA resolusi dalam format human-readable.
     */
    public function remainingLabel(): string
    {
        if ($this->resolutionDue === null || !$this->isActive()) {
            return '—';
        }

        if (now()->gt($this->resolutionDue)) {
            $hours = (int) now()->diffInHours($this->resolutionDue);
            return "Breach {$hours}j lalu";
        }

        $totalMinutes = (int) now()->diffInMinutes($this->resolutionDue);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($hours >= 24) {
            $days = (int) $this->resolutionDue->diffInDays(now());
            return "{$days} hari";
        }

        return $hours > 0 ? "{$hours}j {$minutes}m" : "{$minutes} mnt";
    }

    /** Severity SLA untuk pewarnaan UI: ok | warn | breach */
    public function severity(): string
    {
        if ($this->isBreached()) {
            return 'breach';
        }

        if ($this->percentage() >= 75) {
            return 'warn';
        }

        return 'ok';
    }
}
