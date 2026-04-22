<?php

namespace App\Domain\Ticket\Enums;

class TicketStatus
{
    public const Open = 'open';

    public const Progress = 'progress';

    public const Waiting = 'waiting';

    public const Resolved = 'resolved';

    public const Closed = 'closed';

    public static function all(): array
    {
        return [
            self::Open,
            self::Progress,
            self::Waiting,
            self::Resolved,
            self::Closed,
        ];
    }

    public static function label(string $value): string
    {
        return [
            self::Open     => 'Open',
            self::Progress => 'In Progress',
            self::Waiting  => 'Menunggu Konfirmasi',
            self::Resolved => 'Resolved',
            self::Closed   => 'Closed',
        ][$value] ?? $value;
    }

    public static function color(string $value): string
    {
        return [
            self::Open     => '#185FA5',
            self::Progress => '#BA7517',
            self::Waiting  => '#534AB7',
            self::Resolved => '#3B6D11',
            self::Closed   => '#5F5E5A',
        ][$value] ?? '#5F5E5A';
    }

    /** Status yang boleh dijadikan transisi dari status ini */
    public static function allowedTransitions(string $current): array
    {
        switch ($current) {
            case self::Open:
                return [self::Progress, self::Waiting];
            case self::Progress:
                return [self::Waiting, self::Resolved];
            case self::Waiting:
                return [self::Progress, self::Resolved];
            case self::Resolved:
                return [self::Closed];
            case self::Closed:
            default:
                return [];
        }
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::allowedTransitions($from));
    }

    public static function isTerminal(string $value): bool
    {
        return $value === self::Closed;
    }

    public static function isActive(string $value): bool
    {
        return ! in_array($value, [self::Resolved, self::Closed]);
    }
}
