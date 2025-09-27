<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TopupEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $mJenis;
    protected $mResponse;

    public function __construct($mJenis,$mResponse)
    {
        //
        $this->mJenis = $mJenis;
        $this->mResponse = $mResponse;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mTanggal = $this->mResponse['data']['tgl_request'];
        $mSaldo = $this->mResponse['data']['request_saldo'];
        $mUsername = $this->mResponse['data']['username'];
        $mLoket = $this->mResponse['data']['kode_loket'];
        $mCatatan = $this->mResponse['data']['ket_request'];

        return $this->view('admin.emails.topup')
            ->with([
                'saldo' => $mSaldo,
                'tanggal' => $mTanggal,
                'username' => $mUsername,
                'loket' => $mLoket,
                'catatan' => $mCatatan,
            ]);
    }
}
