<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EstudianteEstado;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Mail\LiquidacionGeneradaMailable;

class EnviarLiquidacionesGeneradas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liquidaciones:generadas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar las liquidaciones generadas del proceso de grados';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      //SIN GENERAR
      $estudiantes_estados = EstudianteEstado::where('recibo_concepto_estado','SIN GENERAR')
      ->where('datos','Si')
      ->where('plan_estudios','Si')
      ->where('liquidacion_pendiente', 'Si')
      ->where('recibo_concepto_grado','No')
      ->with('estudiante')
      ->with('estudiantePensum')
      ->with('estudiantePensum.cat')
      ->whereHas('estudiantePensum',function ($query) {
          return $query->where('seleccionado', '=', 'Si')->where('estado', '=', 1);
      })->get();

      //dd($estudiantes_estados);

      foreach ($estudiantes_estados as $item)
      {
        $url = Config::get('custom_vars.urlApigrados') . 'validacion_existe_recibo_derecho_grado/' . $item->estp_id;
        $response = Http::withHeaders([
                        'Authorization' => '$5$api_grados$IwKxgZ5xTzoF17y2F6xqHP.zmwyO5GX7efl438Ksn8B'
                    ])->get($url);

        $estudiante = [
          'estp_id' => $item->estp_id,
          'pege_id' => $item->pege_id,
          'nombre' => $item->estudiante->primer_nombre . ' ' .
                      $item->estudiante->segundo_nombre . ' ' .
                      $item->estudiante->primer_apellido . ' ' .
                      $item->estudiante->segundo_apellido,
          'email_institucional' => $item->login,
          'documento' => $item->estudiante->documento,
          'programa' =>  $item->estudiantePensum->prog_nombre,
          'cat' => $item->estudiantePensum->unid_nombre,
          'email_cat' => (isset($item->estudiantePensum->cat->correo)?$item->estudiantePensum->cat->correo:Config::get('custom_vars.catEmail')),
        ];

        $liquidacion = $response->body();
        $liquidacion  = json_decode($liquidacion,true);
        //
        // die();
        if($liquidacion['recibos_pendientes']['cantidad'] == 1)
        {

          $detalles = [
            'subject' => "[TEST][Liquidación Generada]",
            'estudiante' => $estudiante,
            'liquidacion' => [
              'id' => $liquidacion['recibos_pendientes']['data'][0]['liqu_id'],
              'referencia' => $liquidacion['recibos_pendientes']['data'][0]['liqu_referencia'],
              'estado' => $liquidacion['recibos_pendientes']['data'][0]['liqu_estado'],
              'tipo' => $liquidacion['recibos_pendientes']['data'][0]['liqu_tipoliquidacion'],
              'concepto' => $liquidacion['recibos_pendientes']['data'][0]['coma_descripcion'],
            ]
          ];
          //Actualizar estado a GENRADO
          $estudiante_estado = EstudianteEstado::find($item->id);
          $estudiante_estado->recibo_concepto_estado = "GENERADO";
          //$estudiante_estado->save();

          //Notificar al estudiante y CAT que el recibo ya esta generado y disponible para pago
          //$correo = new LiquidacionGeneradaMailable($detalles);
          //$detalles['estudiante']['email_institucional']
          // Mail::to(Config::get('custom_vars.rcaEmail'))
          //     ->cc([
          //         $detalles['estudiante']['email_cat'],
          //         Config::get('custom_vars.rcaEmail'),
          //         Config::get('custom_vars.adminEmail')
          //     ])
          //     ->bcc([
          //         'nestorsramosarteaga@gmail.com',
          //     ])
          //     ->send($correo);
          echo date("Y-m-d H:i:s") . "-> Mensaje enviado a " . $detalles['estudiante']['email_institucional'] . PHP_EOL;
        }
        elseif($liquidacion['recibos_pendientes']['cantidad'] > 1)
        {
          //Notificar a RCA y al estudiante para que deje un solo recibo activo para el proceso
          //EN CONFLICTO
          $ReciboEstado = "EN CONFLICTO";
        }
        elseif($liquidacion['recibos_pagados']['cantidad'] == 1)
        {
          //Notificar a RCA y al CAT que el estudiante ya realizo el pago
          //PAGADO
          $ReciboEstado = "PAGADO";
          $ReciboGrado = 'Si';
        }
        else
        {
          //SIN GENERAR
          $ReciboEstado = "SIN GENERAR";
          $ReciboGrado = 'No';
        }
        //

        //
      }

      //print_r($liquidacion);

      //die();


    }
}
