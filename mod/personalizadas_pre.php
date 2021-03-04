<?php

include('uniclaretiana/funciones.php');
	/*
	Copyright (C) 2013  John F. Arroyave Gutiérrez
						unix4you2@gmail.com

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/
			/*
			Title: Funciones personalizadas en precarga
			Ubicacion *[/personalizadas_pre.php]*.  Archivo que contiene la declaracion de variables y funciones por parte del usuario o administrador del sistema que deben ser cargadas al inicio de la aplicacion

			IMPORTANTE:
			Estas funciones son ejecutadas despues de las inclusiones basicas pero antes de las inclusiones de marcos superiores y estilos de bootstrap en modo fullscreen
            */

//Valida si el estudiante no tiene ficha de datos registrada
    if ($PCOSESS_LoginUsuario!="" && $PCO_Accion=="PCO_VerMenu")
        {
            $RegistroUsuario=PCO_EjecutarSQL("SELECT usuario_interno FROM ".$TablasCore."usuario WHERE login=? ","$PCOSESS_LoginUsuario")->fetch();

            $Estudiante=PCO_EjecutarSQL("SELECT id,confirmacion_datos,pege_id FROM app_estudiante WHERE login='{$PCOSESS_LoginUsuario}' ")->fetch();

            $ExisteEstudiante = (isset($Estudiante['id']))? true : false;

            $RegistroProceso=PCO_EjecutarSQL("SELECT valor FROM ".$TablasApp."configuraciones WHERE clave='proceso_abierto'")->fetch();
            $EstadoProceso=$RegistroProceso['valor'];

            //$RegistroProceso=PCO_EjecutarSQL("SELECT validacion_final FROM ".$TablasApp."estudiante_estado WHERE estp_id='proceso_abierto'")->fetch();
            //$EstadoProceso=$RegistroProceso['valor'];

                    if ($RegistroUsuario["usuario_interno"]=="0" && $ExisteEstudiante)
                        {

                            if($EstadoProceso== 'No')
                            {

                                header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:20:1:id:$id&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                                die();

                            } else {

                                ///SI no actualizado los datos lo deja en el formulario de actualizar los y hasta que no lo haga no lo deja pasar
                                $id=$Estudiante['id'];

                                 header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:9:1:id:$id&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                                die();
                            }

                        }

                        elseif ($RegistroUsuario["usuario_interno"]=="1" && $ExisteEstudiante)
                        {
                            if($EstadoProceso== 'No')
                            {
                              //Validar estado

                              //Validar que no haya seleccionado ceremonia

                              //Redireccionar a proceso cerrado
                              header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:20:1:id:$id&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                              die();

                            } else {

                               ///Al encontrar datos actualizados del estudiante lo envia a escoger el PENSUM
                               if($Estudiante['confirmacion_datos'] == '0'){
                                 //redireccionar
                                 $id = $Estudiante["id"];
                                 echo"<form name='redireccion' action='index.php' method='POST'>
                                       <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                                       <input type='hidden' name='PCO_Objeto' value='frm:9:1'>
                                       <input type='hidden' name='id' value='$id'>
                                       <input type='Hidden' name='PCO_Campo' value='id'>
                                       <input type='Hidden' name='PCO_Valor' value='$id'>
                                       <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                                       <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                                     </form>
                                       <script language='JavaScript'>
                                           document.redireccion.submit();
                                       </script>";

                               }else{
                                  $estadoEstudiante=PCO_EjecutarSQL("SELECT id,estp_id,pege_id,nombre FROM app_estudiante_estado WHERE login=? ","$PCOSESS_LoginUsuario");

                                  while($registro = $estadoEstudiante->fetch())
                                  {
                                     $id=$registro['id'];
                                     $estp_id=$registro['estp_id'];
                                     $pege_id=$registro['pege_id'];

                                     $url = "validacion_plan_estudio/" . $estp_id;

                                     $validacion =  LANKIConsumirAPI($url);

                                     //Inicio Actualizar modalidad / nivel del programa
                                     $moda_id=$validacion['data']['moda_id'];

                                     if($moda_id=="3")//3 universitario
                                     {
                                         $nivel_academico="Pregrado";
                                     }
                                     elseif ($moda_id=="6" || $moda_id=="7" || $moda_id=="18") ///6 especializacion, 7 maestria, 18 doctorado segun tabla academico.validar
                                     {
                                         $nivel_academico="Posgrado";
                                     }

                                     PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET nivel='$nivel_academico' WHERE login='$PCOSESS_LoginUsuario' AND estp_id='$estp_id' ");
                                     //Inicio Actualizar modalidad / nivel del programa

                                     if($validacion['cumple_plan_estudio'] == "1"){
                                         $PlanEstudio="Si";
                                         //Actualizar créditos aprobados del plan de estudio
                                         $EstpCreditosAprobados = $validacion['data']['creditosaprobados'];
                                         PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET estp_creditosaprobados='$EstpCreditosAprobados' WHERE login='$PCOSESS_LoginUsuario' AND estp_id='$estp_id' ");
                                       }else{
                                         $PlanEstudio="No";
                                       }


                                     /// FIN Valida el plande estudio para cada programa

                                     //Valida liquidaciones pendientes
                                    $url = "validacion_liquidacion_pendiente/" . $pege_id;

                                       $validacion =  LANKIConsumirAPI($url);

                                       if($validacion['cumple_liquidaciones_pendientes'] == "1"){
                                           $LiquidacionesPendiente="Si";
                                       }else{
                                         $LiquidacionesPendiente="No";
                                       }

                                       //Valida recibo derecho de grado
                                      $url = "validacion_existe_recibo_derecho_grado/" . $estp_id;
                                      $validacion =  LANKIConsumirAPI($url);

                                       //print_r($validacion);
                                        //die();
                                       if($validacion['recibos_pendientes']["cantidad"] == '1')
                                       {
                                           //Notificar al estudiante para que genere el pago
                                           //GENERADO - POR PAGAR
                                           $ReciboEstado = "GENERADO";
                                           $ReciboGrado = 'No';
                                       }
                                       elseif($validacion['recibos_pendientes']["cantidad"] > '1')
                                       {
                                         //Notificar a RCA y al estudiante para que deje un solo recibo activo para el proceso
                                         //EN CONFLICTO
                                         $ReciboEstado = "EN CONFLICTO";
                                         $ReciboGrado = 'No';
                                       }
                                       elseif($validacion['recibos_pagados']["cantidad"] == '1')
                                       {
                                         //Notificar a RCA y al CAT que el estudiante ya realizo el pago
                                         //PAGADO
                                         $ReciboEstado = "PAGADO";
                                         $ReciboGrado = 'Si';

                                         //Cambia estado a 0 para cargar solo el PEMSUN requerido
                                         PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET estado='0' WHERE login='$PCOSESS_LoginUsuario' AND pege_id='$pege_id' AND estp_id='$estp_id'");

                                       }
                                       else
                                       {
                                         //SIN GENERAR
                                         $ReciboEstado = "SIN GENERAR";
                                         $ReciboGrado = 'No';
                                       }
                                       //$ReciboEstado = "PAGADO";
                                       PCO_EjecutarSQLUnaria("UPDATE app_estudiante_estado SET plan_estudios='$PlanEstudio', liquidacion_pendiente='$LiquidacionesPendiente',recibo_concepto_grado='$ReciboGrado',recibo_concepto_estado='$ReciboEstado' WHERE login='$PCOSESS_LoginUsuario' AND id='$id' ");
                                  }

                                  header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:11:1&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                                  die();
                               }

                            }


                        }
                        elseif ($RegistroUsuario["usuario_interno"]=="0" && !$ExisteEstudiante)
                        {
                             if($EstadoProceso== 'No')
                                {

                                    header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:20:1:id:$id&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                                    die();

                                } else {
                                     header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:8:1&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                                    die();
                                }
                        }
                        else
                        {
                                //echo "<script language='JavaScript'> window.location='index.php'; </script>";
                             //Redirecciona a Registrarse como solicitante o prestador
                            //header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:8:1&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                            //die();
                        }


        }