<?php

namespace App\Observers;

use App\Models\Ticket;
use Filament\Notifications\Notification;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $agent = $ticket->assignedTo;

        Notification::make()
            ->title('A ticket has been assigned to you')
            ->sendToDatabase($agent);

        // $agent->notify(
        //     Notification::make()
        //         ->title('A ticket has been assigned to you')
        //         ->toDatabase(),
        // );
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        //
    }
}
