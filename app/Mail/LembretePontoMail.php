<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LembretePontoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dataFalta;

    public function __construct($dataFalta)
    {
        $this->dataFalta = $dataFalta;
    }

    public function build()
    {
        return $this->subject('Aviso: Falta de registo de ponto')
                    ->view('mails.lembrete_ponto');
    }
}