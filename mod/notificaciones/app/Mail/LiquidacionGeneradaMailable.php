<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LiquidacionGeneradaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $liquidacion;
    public $estudiante;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->subject = $data['subject'];
        $this->liquidacion = $data['liquidacion'];
        $this->estudiante = $data['estudiante'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.liquidaciones.generada');
    }
}
