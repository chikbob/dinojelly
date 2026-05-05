<?php

namespace App\Jobs;

use App\Mail\AbandonedCartRecoveryMail;
use App\Models\CartRecoveryReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartRecoveryEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $reminderId,
    ) {}

    public function handle(): void
    {
        $reminder = CartRecoveryReminder::query()
            ->with('user')
            ->find($this->reminderId);

        if (! $reminder || $reminder->status !== 'pending' || ! $reminder->email) {
            return;
        }

        Mail::to($reminder->email)->send(new AbandonedCartRecoveryMail($reminder));

        $reminder->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
