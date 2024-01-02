<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KirimHasilMCU extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private array $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('emails.kirim-hasil-mcu')
            ->attach(base_path().\DIRECTORY_SEPARATOR.'analysis.txt');
    }

    public function markEmailSent(): void {}

    protected function findData(): void
    {
        //
    }
}
