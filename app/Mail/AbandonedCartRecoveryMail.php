<?php

namespace App\Mail;

use App\Models\CartRecoveryReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedCartRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CartRecoveryReminder $reminder,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Вы забыли товары в корзине DinoJelly')
            ->view('emails.abandoned-cart-recovery', [
                'reminder' => $this->reminder,
                'recoveryUrl' => route('cart.recover', $this->reminder->token),
            ]);
    }
}
