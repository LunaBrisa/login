<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerifyRegisterMail extends Mailable
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject(
            'Verificación de correo'
        )->view('emails.verify-register');
    }
}