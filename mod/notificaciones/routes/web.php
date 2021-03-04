<?php

use Illuminate\Support\Facades\Route;
use App\Mail\ContactanosMailable;
use App\Mail\LiquidacionGeneradaMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('contactanos', function () {
//     $correo = new ContactanosMailable;
//     Mail::to('nestorsramosarteaga@gmail.com')->send($correo);
//     return "Mensaje enviado";
// });

// Route::get('liquidaciones-generadas', function () {
//
//     $detalles = [
//         'subject' => "TEST Sistema de Grados",
//         'estudiante' => [
//             'nombre' => "Armando Casas",
//             'email_institucional' => "acasas@miuniclaretiana.edu.co",
//             'email_personal' => "armandocasas321@hotmail.com",
//             'programa' => 'TRABAJO SOCIAL',
//             'cat' => 'CAT MEDELLIN',
//         ],
//         'liquidacion' => [
//             'id' => "345778",
//             'referencia' => "4567891",
//             'estado' => "PENDIENTE",
//         ],
//     ];
//
//     $correo = new LiquidacionGeneradaMailable($detalles);
//     Mail::to($detalles['estudiante']['email_institucional'])
//         ->cc([
//             $detalles['estudiante']['email_personal'],
//             Config::get('custom_vars.rcaEmail'),
//         ])
//         ->bcc([
//             Config::get('custom_vars.adminEmail'),
//             Config::get('custom_vars.testEmail'),
//         ])
//         ->send($correo);
//     return "Mensaje enviado !!!*";
// });
