<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TopupVerifikasiEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $mLoket;
    protected $mResponse;
    
    public function __construct($mLoket, $mResponse)
    {
        $this->mLoket = $mLoket;
        $this->mResponse = $mResponse;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mCode = $this->mResponse['data']['request_code'];
        $mStatus = $this->mResponse['data']['status_verifikasi'];
        $mSaldo = $this->mResponse['data']['verifikasi_saldo'];
        $mKet = $this->mResponse['data']['ket_verifikasi'];
        $mTanggal = $this->mResponse['data']['tgl_verifikasi'];
        
        return $this->view('admin.emails.topup_verifikasi')
            ->with([
                'kodeloket' => $this->mLoket,
                'code' => $mCode,
                'status' => $mStatus,
                'saldo' => $mSaldo,
                'ket' => $mKet,
                'tanggal' => $mTanggal,
            ]);
    }
}
