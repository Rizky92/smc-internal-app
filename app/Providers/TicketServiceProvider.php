<?php

namespace App\Providers;

use App\Application\Ticket\Actions\AddCommentAction;
use App\Application\Ticket\Actions\AssignTicketAction;
use App\Application\Ticket\Actions\CreateTicketAction;
use App\Application\Ticket\Actions\UpdateStatusAction;
use App\Application\Ticket\Events\TicketCreated;
use App\Application\Ticket\Services\SlaCalculator;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Infrastructure\Ticket\Repositories\EloquentTicketRepository;
use App\Listeners\Ticket\SendTicketCreatedNotification;
use Illuminate\Support\ServiceProvider;

/**
 * TicketServiceProvider — satu tempat untuk:
 *   1. Bind interface ke implementasi konkret
 *   2. Daftarkan event listener
 *
 * Daftarkan di config/app.php → providers[] atau bootstrap/providers.php (Laravel 11+).
 */
class TicketServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Interface → Implementasi
        $this->app->bind(
            TicketRepositoryInterface::class,
            EloquentTicketRepository::class,
        );

        // Actions — dibuat via container agar dependency-nya auto-resolved
        $this->app->bind(CreateTicketAction::class, fn ($app) => new CreateTicketAction(
            $app->make(TicketRepositoryInterface::class),
            $app->make(SlaCalculator::class),
        ));

        $this->app->bind(UpdateStatusAction::class, fn ($app) => new UpdateStatusAction(
            $app->make(TicketRepositoryInterface::class),
        ));

        $this->app->bind(AssignTicketAction::class, fn ($app) => new AssignTicketAction(
            $app->make(TicketRepositoryInterface::class),
        ));

        $this->app->bind(AddCommentAction::class, fn ($app) => new AddCommentAction(
            $app->make(TicketRepositoryInterface::class),
        ));

        // SlaCalculator adalah singleton — cache-nya berlaku sepanjang request
        $this->app->singleton(SlaCalculator::class);
    }

    public function boot(): void
    {
        // Event → Listener mapping
        $this->app['events']->listen(
            TicketCreated::class,
            SendTicketCreatedNotification::class
        );
    }
}
