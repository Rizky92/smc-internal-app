<?php

namespace App\Domain\Ticket\Enums;

class TicketPriority
{
    public const Critical = 'critical';

    public const High = 'high';

    public const Medium = 'medium';

    public const Low = 'low';

    public static function all(): array
    {
        return [
            self::Critical,
            self::High,
            self::Medium,
            self::Low,
        ];
    }

    public static function label(string $value): string
    {
        return [
            self::Critical => 'Critical',
            self::High     => 'High',
            self::Medium   => 'Medium',
            self::Low      => 'Low',
        ][$value] ?? $value;
    }

    public static function color(string $value): string
    {
        return [
            self::Critical => '#E24B4A',
            self::High     => '#EF9F27',
            self::Medium   => '#378ADD',
            self::Low      => '#888780',
        ][$value] ?? '#888780';
    }

    /**
     * SLA default dalam jam (fallback jika sla_policies kosong di DB).
     */
    public static function defaultResolutionHours(string $value): int
    {
        return [
            self::Critical => 4,
            self::High     => 8,
            self::Medium   => 24,
            self::Low      => 72,
        ][$value] ?? 24;
    }

    public static function defaultResponseHours(string $value): int
    {
        return [
            self::Critical => 1,
            self::High     => 2,
            self::Medium   => 4,
            self::Low      => 8,
        ][$value] ?? 4;
    }

    public static function sortOrder(string $value): int
    {
        return [
            self::Critical => 1,
            self::High     => 2,
            self::Medium   => 3,
            self::Low      => 4,
        ][$value] ?? 3;
    }
}
