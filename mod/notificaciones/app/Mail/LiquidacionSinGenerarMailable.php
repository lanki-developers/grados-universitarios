<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LiquidacionSinGenerarMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $listado;
    //public $correo_cat;
    //public $estudiante;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
      $this->subject = $data['subject'];
      $this->listado = $data['listado'];
      //$this->correo_cat = $data['correo_cat'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.liquidaciones.sin_generar');
    }
}
