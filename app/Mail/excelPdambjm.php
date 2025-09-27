<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class excelPdambjm extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $pesanEmail;
    protected $isTransaksi;

    public function __construct($pesanEmail, $isTransaksi)
    {
        $this->pesanEmail = $pesanEmail;
        $this->isTransaksi = $isTransaksi;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date = date_create(date('Y-m-d'));
        date_sub($date, date_interval_create_from_date_string('1 days'));
        
        $dateNow = date_format($date, 'Y-m-d');
        
        $fileName = 'pdambjm_'.$dateNow.'.xls';

        if($this->isTransaksi){
            $filePath = storage_path('exports/'.$fileName);
            return $this->view('admin.emails.pdambjm')
                ->attach($filePath)
                ->with([
                    'pesanEmail' => $this->pesanEmail
                ]);
        }else{
            return $this->view('admin.emails.pdambjm')
                ->with([
                    'pesanEmail' => $this->pesanEmail
                ]);
        }

        //$filePath = env('HOST_ADDRESS','').'/images/kopkar/koperasi_logo.gif';
    }
}
