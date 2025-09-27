<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SisaSaldoEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $isiPesan;
    
    public function __construct($isiPesan)
    {
        $this->isiPesan = $isiPesan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('admin.emails.sisasaldo')
            ->with([
                'isiPesan' => $this->isiPesan
            ]);
    }
}
