<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EstudianteEstado;
use App\Models\CAT;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Mail\LiquidacionSinGenerarMailable;

class EnviarLiquidacionSinGenerar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liquidaciones:sin_generar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar la notificacion de las liquidaciones pendientes de ser generadas';

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
      $estudiantes_estados = EstudianteEstado::whereIn('recibo_concepto_estado',['SIN GENERAR',''])
      ->where('datos','Si')
      ->where('plan_estudios','Si')
      ->where('liquidacion_pendiente', 'Si')
      ->where('recibo_concepto_grado','No')
      ->where('documentos_adjuntos','Si')
      ->with('estudiante')
      ->with('estudiantePensum.cat')
      ->whereHas('estudiantePensum',function ($query) {
          return $query->where('seleccionado', '=', 'Si')->where('estado', '=', 1);
      })->get();

      $estudiantes_estados = $estudiantes_estados->sortBy('estudiantePensum.unid_nombre')->sortBy('estudiantePensum.prog_nombre');

      $cats = CAT::all();

      //dd($estudiantes_estados);
      //
      $listado_html  = '<table class="table table-striped">
        <thead>
          <tr>
            <th scope="col">ESTP ID</th>
            <th scope="col">PEGE ID</th>
            <th scope="col">DOCUMENTO</th>
            <th scope="col">NOMBRE</th>
            <th scope="col">EMAIL</th>
            <th scope="col">PROGRAMA</th>
            <th scope="col">CAT</th>
          </tr>
        </tead>
        <tbody>';

      foreach ($estudiantes_estados as $item)
      {
        $url = Config::get('custom_vars.urlApigrados') . 'validacion_existe_recibo_derecho_grado/' . $item->estp_id;
        $response = Http::withHeaders([
                        'Authorization' => '$5$api_grados$IwKxgZ5xTzoF17y2F6xqHP.zmwyO5GX7efl438Ksn8B'
                    ])->get($url);

        // $estudiante = [
        //   'estp_id' => $item->estp_id,
        //   'pege_id' => $item->pege_id,
        //   'documento' => $item->estudiante->documento,
        //   'nombre' => $item->estudiante->primer_nombre . ' ' .
        //               $item->estudiante->segundo_nombre . ' ' .
        //               $item->estudiante->primer_apellido . ' ' .
        //               $item->estudiante->segundo_apellido,
        //   'email_institucional' => $item->login,
        //   'programa' =>  $item->estudiantePensum->prog_nombre,
        //   'cat' => $item->estudiantePensum->unid_nombre,
        //   'email_cat' => (isset($item->estudiantePensum->cat->correo)?$item->estudiantePensum->cat->correo:Config::get('custom_vars.catEmail')),
        // ];

        $liquidacion = $response->body();
        $liquidacion  = json_decode($liquidacion,true);

        if($liquidacion['recibos_pendientes']['cantidad'] == 0)
        {
          //Crear listado de estudiantes

          $listado_html .= "<tr>".
            "<td>".$item->estp_id."</td>".
            "<td>".$item->pege_id."</td>".
            "<td>".$item->estudiante->documento."</td>".
            "<td>".$item->estudiante->primer_nombre . ' ' .
                        $item->estudiante->segundo_nombre . ' ' .
                        $item->estudiante->primer_apellido . ' ' .
                        $item->estudiante->segundo_apellido."</td>".
            "<td>".$item->login."</td>".
            "<td>".$item->estudiantePensum->prog_nombre."</td>".
            "<td>".$item->estudiantePensum->unid_nombre."</td>".
          "</tr>";


          //Notificar al estudiante y CAT que el recibo ya esta generado y disponible para pago
          //$correo = new LiquidacionSinGenerarMailable($detalles);

          // Mail::to(Config::get('custom_vars.rcaEmail'))
          //     ->cc([
          //         $detalles['estudiante']['email_cat'],
          //         Config::get('custom_vars.adminEmail')
          //     ])
          //     ->bcc([
          //         'nestorsramsarteaga@gmail.com',
          //     ])
          //     ->send($correo);

          // echo date("Y-m-d H:i:s") . "-> Recibo sin generar a " . $detalles['estudiante']['email_institucional'] . PHP_EOL;
          // sleep(2);
        }

      }

      $listado_html .= "</tbody>
        </table>";

      foreach ($cats as $cat) {
        $cc[] = $cat->correo;
      }
      $cc[] = Config::get('custom_vars.adminEmail');

      $detalles = [
        'subject' => "[TEST][Consolidado liquidaciones sin generar]",
        'listado' => $listado_html,
      ];

      //Notificar al estudiante y CAT que el recibo ya esta generado y disponible para pago
      $correo = new LiquidacionSinGenerarMailable($detalles);

      Mail::to(Config::get('custom_vars.rcaEmail'))
          ->cc([$cc,Config::get('custom_vars.adminEmail')])
          ->bcc([
              'nestorsramosarteaga@gmail.com',
          ])
          ->send($correo);

      echo date("Y-m-d H:i:s") . "-> Consolidado de liquidaciones sin generar enviado!" . PHP_EOL;
      // sleep(2);

    }
}
