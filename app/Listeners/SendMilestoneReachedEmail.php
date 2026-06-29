<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MilestoneReached;
use App\Mail\MilestoneReachedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendMilestoneReachedEmail implements ShouldQueue
{
    public function handle(MilestoneReached $event): void
    {
        Mail::to($event->user->email)->send(
            new MilestoneReachedMail($event->user, $event->milestonePoints),
        );
    }
}
