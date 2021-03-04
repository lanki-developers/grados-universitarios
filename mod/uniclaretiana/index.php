<?php

include('./funciones.php');

if ($PCO_Accion=="CrearEstudiante")

    {
        //$DatosEstudiante = PCO_CargarURL($ServidorBaseConsulta);
        $url = "personas/".$PCOSESS_LoginUsuario;
        //$url = "http://172.16.0.81/apigrados/public/api/personas/aramirez@miuniclaretiana.edu.co";
        //$$url = "http://172.16.0.81/apigrados/public/api/personas/".$PCOSESS_LoginUsuario;

        $estudiante =  LANKIConsumirAPI($url);

        if(is_null($estudiante['data']))
        {
            PCO_Mensaje("FALTAN DATOS: Correo electr&oacute;nico: $PCOSESS_LoginUsuario no se encuentra registrado","Comuniquese con", '', 'fa fa-fw fa-3x fa-refresh fa-spin', 'alert alert-dismissible alert-warning');
        }
        else
        {
                $estudiante = $estudiante['data'];

                $pege_id=$estudiante['pege_id'];
                $tipo_documento=$estudiante['persona_general']['tipo_documento_general']['tidg_abreviatura'];
                $documento=$estudiante['persona_general']['pege_documentoidentidad'];
                $primer_nombre=$estudiante['peng_primernombre'];
                $segundo_nombre=$estudiante['peng_segundonombre'];
                $primer_apellido=$estudiante['peng_primerapellido'];
                $segundo_apellido=$estudiante['peng_segundoapellido'];
                $fecha_expedicion=$estudiante['persona_general']['pege_fechaexpedicion'];
                $lugar_expedicion=$estudiante['persona_general']['pege_lugarexpedicion'];
                $fecha_nacimiento=$estudiante['peng_fechanacimiento'];
                $lugar_nacimiento=$estudiante['cige_idlugarnacimiento'];
                $correo=$PCOSESS_LoginUsuario;
                $direccion=$estudiante['persona_general']['pege_direccion'];
                $municipio=$estudiante['persona_general']['cige_idresidencia'];
                $barrio="";
                $celular=$estudiante['persona_general']['pege_telefonocelular'];
                $otro_telefono=$estudiante['persona_general']['pege_telefono'];
                $sexo=$estudiante['peng_sexo'];
                $rh=$estudiante['peng_rh'];

                //crear estudiante en la tabla
                PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante (login,pege_id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,sexo,fecha_nacimiento,lugar_nacimiento,tipo_documento,documento,lugar_expedicion,fecha_expedicion,rh,nivel)
                VALUES ('$PCOSESS_LoginUsuario','$pege_id','$primer_nombre','$segundo_nombre','$primer_apellido','$segundo_apellido','$sexo','$fecha_nacimiento','$lugar_nacimiento','$tipo_documento','$documento','$lugar_expedicion','$fecha_expedicion','$rh','$nivel')");
                $id = $ConexionPDO->lastInsertId();
                PCO_Auditar("Agrega estudiante $PCOSESS_LoginUsuario");

                ///Guardar los programas del estudiante
                foreach( $estudiante['estudiantes_pensums'] as $estudiante_pensum)
                    {
                     	$estp_id=$estudiante_pensum['estp_id'];
                     	$estp_creditosaprobados=$estudiante_pensum['estp_creditosaprobados'];
                     	$peun_id=$peun_id=$estudiante_pensum['peun_id'];
                     	$estp_estado=$estudiante_pensum['estp_estado'];
                     	$unpr_id=$estudiante_pensum['unidad_programa']['unpr_id'] ;
                    	$prog_id=$estudiante_pensum['unidad_programa']['programa']['prog_id'];
                    	$moda_id=$estudiante_pensum['unidad_programa']['programa']['moda_id'];
                     	$prog_nombre=$estudiante_pensum['unidad_programa']['programa']['prog_nombre'];
                     	$prog_codigoicfes=$estudiante_pensum['unidad_programa']['programa']['prog_codigoicfes'];
                     	$meto_descripcion=$estudiante_pensum['unidad_programa']['programa']['metodologia']['meto_descripcion'] ;
                    	$unid_id=$estudiante_pensum['unidad_programa']['unidad']['unid_id'];
                    	$unid_nombre=$estudiante_pensum['unidad_programa']['unidad']['unid_nombre'];
                    	$cige_id=$estudiante_pensum['unidad_programa']['unidad']['cige_id'];
                    	$unid_email=$estudiante_pensum['unidad_programa']['unidad']['unid_email'];

                        //3 universitario
                        if($moda_id=="3")
                        {
                            $nivel_academico="Pregrado";
                        }
                        ///6 especializacion, 7 maestria, 18 doctorado segun tabla academico.validar

                        elseif ($moda_id=="6" || $moda_id=="7" || $moda_id=="18")
                            {
                                $nivel_academico="Posgrado";
                            }


                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_pensum (login,pege_id,estp_id,estp_creditosaprobados,estp_estado,unpr_id,prog_id,prog_nombre,prog_codigoicfes,meto_descripcion,unid_id,unid_nombre,cige_id,unid_email,nivel) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$estp_creditosaprobados','$estp_estado','$unpr_id','$prog_id','$prog_nombre','$prog_codigoicfes','$meto_descripcion','$unid_id','$unid_nombre','$cige_id','$unid_email','$nivel_academico')");

                        ///Valida el plande estudio para cada programa

                        $url = "validacion_plan_estudio/" . $estp_id;

                          $validacion =  LANKIConsumirAPI($url);

                          if($validacion['cumple_plan_estudio'] == "1"){
                            $PlanEstudio="Si";
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
                        //Fin Valida liquidaciones pendientes

                        //inserta estudiante en tabla de estados del estudiante
                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_estado (login,pege_id,estp_id,nombre,plan_estudios,liquidacion_pendiente) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$prog_nombre','$PlanEstudio','$LiquidacionesPendiente')");

                    }

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
        }

        //PCO_CargarFormulario(9,1);
    }


//////Actulizar datos del estudiante_estado
if ($PCO_Accion=="ActualizarEstudiante")
{

    if($confirmacion_datos=="1")
    {
        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_estado SET datos='Si' WHERE login='$PCOSESS_LoginUsuario' ");
    }

    PCO_EjecutarSQLUnaria("UPDATE app_estudiante SET primer_nombre='$primer_nombre',segundo_nombre='$segundo_nombre',primer_apellido='$primer_apellido',segundo_apellido='$segundo_apellido',sexo='$sexo',fecha_nacimiento='$fecha_nacimiento',lugar_nacimiento='$lugar_nacimiento',tipo_documento='$tipo_documento',documento='$documento',lugar_expedicion='$lugar_expedicion',fecha_expedicion='$fecha_expedicion',direccion='$direccion',municipio='$municipio',barrio='$barrio',celular='$celular',otro_telefono='$otro_telefono',confirmacion_datos='$confirmacion_datos',rh='$rh',saber_pro='$saber_pro',icfes='$icfes',lugar_correspondencia='$lugar_correspondencia',observaciones='$observaciones',aseguro='$aseguro',login='$login' WHERE login='$PCOSESS_LoginUsuario' ");
    PCO_EjecutarSQLUnaria("UPDATE core_usuario SET usuario_interno='1' WHERE login='{$PCOSESS_LoginUsuario}'");
    PCO_Auditar("Actualiza datos estudiante $PCOSESS_LoginUsuario");

    ///actualiza estados
    //ActualizaEstados();

    //opcion definida Frm 10
    //PCO_CargarFormulario(11,1);
    echo "<script language='JavaScript'> window.location='index.php'; </script>";
}

///Selecciona el Pensum estudiante
if ($PCO_Accion=="SeleccionPensum")
    {
        //Identificamos variables necesarias
        $RegistroPensum=PCO_EjecutarSQL("SELECT id,cige_id,prog_codigoicfes,estado,seleccionado FROM app_estudiante_pensum WHERE id='$programa_id'")->fetch();
        //FAlta identificar el CAT, el programa pensum_estudiante_id,
        $estado=$RegistroPensum['estado'];
        $seleccionado=$RegistroPensum['seleccionado'];
        $cige_id=$RegistroPensum['cige_id'];
        $prog_codigoicfes=$RegistroPensum['prog_codigoicfes'];


        //consulta del CAT seleccionado
        $RegistroCat=PCO_EjecutarSQL("SELECT id FROM app_cat WHERE municipio=? ","$cige_id")->fetch();
        $cat_id=$RegistroCat['id'];
        //Identificar en programa que el cat y el icfes cumpla y retorna los pre-requisitos
        $RegistroProgramas=PCO_EjecutarSQL("SELECT tipo_requisitos FROM app_programas WHERE codigo_rca='$prog_codigoicfes' AND cat_id='$cat_id' ")->fetch();
        $tipo_requisitos=$RegistroProgramas['tipo_requisitos'];


            if($tipo_requisitos != "")
            {

                //agregar vqalidacion si no trae nada para el cat no ha sido agregado este pensum
                //realiza insercion en adjunto si es  posgrado
                if($tipo_requisitos == "Posgrado")
                {

                    if($estado == "1" && $seleccionado== "No"){
                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."posgrado_adjuntos (login,pensum_estudiante_id,cat_id,fecha)
                        VALUES ('$PCOSESS_LoginUsuario','$programa_id','$cat_id','$PCO_FechaOperacionGuiones')");
                        $id = $ConexionPDO->lastInsertId();

                        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET seleccionado='Si' WHERE id='$programa_id'");
                        PCO_Auditar("Seleccion el programa id:$programa_id ");
                        //PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_estado (login,pege_id,estp_id,nombre,plan_estudios,liquidacion_pendiente) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$prog_nombre','$PlanEstudio','$LiquidacionesPendiente')");

                        echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:12:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";

                      }

                      if($estado == "1" && $seleccionado== "Si"){

                          $posgrado_adjuntos=PCO_EjecutarSQL("SELECT id FROM app_posgrado_adjuntos WHERE login='$PCOSESS_LoginUsuario' AND cat_id='$cat_id'")->fetch();
                          $id=$posgrado_adjuntos['id'];

                           echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:12:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                      }
                }


                //realiza insercion en adjunto si es  pregrado
                if($tipo_requisitos == "Pregrado")
                {
                    if($estado == "1" && $seleccionado== "No"){
                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."pregrado_adjuntos (login,pensum_estudiante_id,cat_id,fecha)
                        VALUES ('$PCOSESS_LoginUsuario','$programa_id','$cat_id','$PCO_FechaOperacionGuiones')");
                        $id = $ConexionPDO->lastInsertId();

                        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET seleccionado='Si' WHERE id='$programa_id'");
                        PCO_Auditar("Seleccion el programa id:$programa_id ");
                        //PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_estado (login,pege_id,estp_id,nombre,plan_estudios,liquidacion_pendiente) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$prog_nombre','$PlanEstudio','$LiquidacionesPendiente')");

                        echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:13:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                       }

                      if($estado == "1" && $seleccionado== "Si"){

                          $posgrado_adjuntos=PCO_EjecutarSQL("SELECT id FROM app_pregrado_adjuntos WHERE login='$PCOSESS_LoginUsuario' AND cat_id='$cat_id'")->fetch();
                          $id=$posgrado_adjuntos['id'];

                           echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:13:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                      }
                }

            } else {
                    PCO_Mensaje("CAT PENSUM: Esta CAT no tiene pensum asignado. Informar a Registro","", '', 'fa fa-fw fa-3x fa-refresh fa-spin', 'alert alert-dismissible alert-danger');
            }

         }

//Ver adjuntos de los estudiantes
if ($PCO_Accion=="VerAdjuntos")
    {

        $estudiante=PCO_EjecutarSQL("SELECT login FROM app_estudiante WHERE id='$PCO_Valor'")->fetch();
        $PCOSESS_LoginUsuario=$estudiante['login'];
        $RegistroPensum=PCO_EjecutarSQL("SELECT id,cige_id,prog_codigoicfes,prog_nombre FROM app_estudiante_pensum WHERE login='$PCOSESS_LoginUsuario' AND estado='1' AND seleccionado='Si'" )->fetch();
        $NombrePensum=$RegistroPensum["prog_nombre"];    
        ///cONSULTA DE PENSUM 
        $cat=PCO_EjecutarSQL("SELECT id,cige_id,prog_codigoicfes FROM app_estudiante_pensum WHERE login='$PCOSESS_LoginUsuario' AND estado='1' AND seleccionado='Si'" )->fetch();

        $RegistroPensum['id'];
        
        ///CONSULTA DE CAT

        if($RegistroPensum['id']=="")
            {
                PCO_Mensaje("ESTUDIANTE SIN PENSUM: Estudiante: $PCOSESS_LoginUsuario no ha seleccionado ningun pensum","", '', 'fa fa-fw fa-3x fa-refresh fa-spin', 'alert alert-dismissible alert-warning');
            }
            else
                {
                $cige_id=$RegistroPensum['cige_id'];
                $prog_codigoicfes=$RegistroPensum['prog_codigoicfes'];
                

                //consulta del CAT seleccionado
                $RegistroCat=PCO_EjecutarSQL("SELECT id FROM app_cat WHERE municipio=? ","$cige_id")->fetch();

                $cat_id=$RegistroCat['id'];
                //Identificar en programa que el cat y el icfes cumpla y retorna los pre-requisitos
                $RegistroProgramas=PCO_EjecutarSQL("SELECT tipo_requisitos FROM app_programas WHERE codigo_rca='$prog_codigoicfes' AND cat_id='$cat_id' ")->fetch();
                $tipo_requisitos=$RegistroProgramas['tipo_requisitos'];
   

                if($tipo_requisitos=="Pregrado"){
                    $estudiante_pregrado=PCO_EjecutarSQL("SELECT id FROM app_pregrado_adjuntos WHERE login='$PCOSESS_LoginUsuario'")->fetch();
                    $id=$estudiante_pregrado["id"];

                    echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:13:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";

                          }

                if($tipo_requisitos=="Posgrado" || is_null($tipo_requisitos)){
                    $estudiante_pregrado=PCO_EjecutarSQL("SELECT id FROM app_posgrado_adjuntos WHERE login='$PCOSESS_LoginUsuario'")->fetch();
                    $id=$estudiante_pregrado["id"];
                
                    echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:12:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";

                          }
                }        
    }


if ($PCO_Accion=="Cla_ValorCampoTabla") 
    {
        if($condicion=="") $condicion="1=1";
        $registro=PCO_EjecutarSQL("SELECT $campo FROM $tabla WHERE $condicion LIMIT 1")->fetch();
        @ob_clean();
        if ($registro[0]!="")
            echo trim($registro[0]);
        else
            echo "";
        die();
    }


////Elimina adjuntos de posgrado
if ($PCO_Accion=="seleccion_adjunto_borrar")
    {
        //echo $PCO_Valor;
        $id=$PCO_Valor;
        PCO_CargarFormulario("17",1);
    }

if ($PCO_Accion=="EliminarAdjunto")
    {
       if($adjunto=="Plan estudios"){
          $campos="plan_estudios=''";
       }  
       
       if($adjunto=="Paz y salvo financiero"){
           $campos="paz_salvo_finaciero=''";
       } 
       
       if($adjunto=="Derechos grado"){
           $campos="pago_derechos_grado=''";
       } 
        
       PCO_EjecutarSQLUnaria("UPDATE app_posgrado_adjuntos SET $campos WHERE id='$id'");
       PCO_Auditar("borra adjunto :$adjunto  para posgrado id $id");
       echo "<script language='JavaScript'> window.location='index.php'; </script>";
    }
    

////Elimina adjuntos de pregrado

if ($PCO_Accion=="seleccion_adjunto_borrar_pregrado")
    {
        //echo $PCO_Valor;
        $id=$PCO_Valor;
        PCO_CargarFormulario("18",1);
    }

if ($PCO_Accion=="EliminarAdjuntoPregrado")
    {
        if($adjunto=="ingles"){
          $campos="ingles=''";
       } 
       
       if($adjunto=="Sustentacion grado"){
          $campos="sustentacion_grado=''";
       } 
        
        
       if($adjunto=="Plan estudios"){
          $campos="plan_estudios=''";
       }  
       
       if($adjunto=="Paz y salvo financiero"){
           $campos="paz_salvo_finaciero=''";
       } 
       
       if($adjunto=="Derechos grado"){
           $campos="pago_derechos_grado=''";
       } 
        
       PCO_EjecutarSQLUnaria("UPDATE app_pregrado_adjuntos SET $campos WHERE id='$id'");
       PCO_Auditar("borra adjunto :$adjunto  para pregrado id $id");
       echo "<script language='JavaScript'> window.location='index.php'; </script>";
    } 

//Permite seleccionar la ceremonia    
if ($PCO_Accion=="SeleccionarCeremonia")
    {    
    echo $PCO_Valor;
    die();
    
    }
    
    
    
    
    

///Selecciona el Pensum administracion
if ($PCO_Accion=="SeleccionPensumAdmin")
    {
        //Identificamos variables necesarias
        $RegistroPensum=PCO_EjecutarSQL("SELECT id,cige_id,prog_codigoicfes,estado,seleccionado,login FROM app_estudiante_pensum WHERE id='$programa_id'")->fetch();
        //FAlta identificar el CAT, el programa pensum_estudiante_id,
        $estado=$RegistroPensum['estado'];
        $seleccionado=$RegistroPensum['seleccionado'];
        $cige_id=$RegistroPensum['cige_id'];
        $prog_codigoicfes=$RegistroPensum['prog_codigoicfes'];
        $LoginEstudiante=$RegistroPensum['login'];


        //consulta del CAT seleccionado
        $RegistroCat=PCO_EjecutarSQL("SELECT id FROM app_cat WHERE municipio=? ","$cige_id")->fetch();
        $cat_id=$RegistroCat['id'];
        //Identificar en programa que el cat y el icfes cumpla y retorna los pre-requisitos
        $RegistroProgramas=PCO_EjecutarSQL("SELECT tipo_requisitos FROM app_programas WHERE codigo_rca='$prog_codigoicfes' AND cat_id='$cat_id' ")->fetch();
        $tipo_requisitos=$RegistroProgramas['tipo_requisitos'];


            if($tipo_requisitos != "")
            {

                //agregar vqalidacion si no trae nada para el cat no ha sido agregado este pensum
                //realiza insercion en adjunto si es  posgrado
                if($tipo_requisitos == "Posgrado")
                {

                    if($estado == "1" && $seleccionado== "No"){
                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."posgrado_adjuntos (login,pensum_estudiante_id,cat_id,fecha)
                        VALUES ('$LoginEstudiante','$programa_id','$cat_id','$PCO_FechaOperacionGuiones')");
                        $id = $ConexionPDO->lastInsertId();

                        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET seleccionado='Si' WHERE id='$programa_id'");
                        PCO_Auditar("Seleccion el programa id:$programa_id ");
                        //PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_estado (login,pege_id,estp_id,nombre,plan_estudios,liquidacion_pendiente) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$prog_nombre','$PlanEstudio','$LiquidacionesPendiente')");

                        echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:12:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";

                      }

                      if($estado == "1" && $seleccionado== "Si"){

                          $posgrado_adjuntos=PCO_EjecutarSQL("SELECT id FROM app_posgrado_adjuntos WHERE login='$LoginEstudiante' AND cat_id='$cat_id'")->fetch();
                          $id=$posgrado_adjuntos['id'];

                           echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:12:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                      }
                }


                //realiza insercion en adjunto si es  pregrado
                if($tipo_requisitos == "Pregrado")
                {
                    if($estado == "1" && $seleccionado== "No"){
                        PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."pregrado_adjuntos (login,pensum_estudiante_id,cat_id,fecha)
                        VALUES ('$LoginEstudiante','$programa_id','$cat_id','$PCO_FechaOperacionGuiones')");
                        $id = $ConexionPDO->lastInsertId();

                        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_pensum SET seleccionado='Si' WHERE id='$programa_id'");
                        PCO_Auditar("Seleccion el programa id:$programa_id ");
                        //PCO_EjecutarSQLUnaria("INSERT INTO ".$TablasApp."estudiante_estado (login,pege_id,estp_id,nombre,plan_estudios,liquidacion_pendiente) VALUES ('$PCOSESS_LoginUsuario','$pege_id','$estp_id','$prog_nombre','$PlanEstudio','$LiquidacionesPendiente')");

                        echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:13:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                       }

                      if($estado == "1" && $seleccionado== "Si"){

                          $posgrado_adjuntos=PCO_EjecutarSQL("SELECT id FROM app_pregrado_adjuntos WHERE login='$LoginEstudiante' AND cat_id='$cat_id'")->fetch();
                          $id=$posgrado_adjuntos['id'];

                           echo"<form name='redireccion' action='index.php' method='POST'>
                          <input type='hidden' name='PCO_Accion' value='PCO_CargarObjeto'>
                          <input type='hidden' name='PCO_Objeto' value='frm:13:1'>
                          <input type='hidden' name='id' value='$id'>
                          <input type='Hidden' name='PCO_Campo' value='id'>
                          <input type='Hidden' name='PCO_Valor' value='$id'>
                          <input type='Hidden' name='PCO_CampoBusquedaBD' value='id'>
                          <input type='Hidden' name='PCO_ValorBusquedaBD' value='$id'>
                        </form>
                          <script language='JavaScript'>
                              document.redireccion.submit();
                          </script>";
                      }
                }

            } else {
                    PCO_Mensaje("CAT PENSUM: Esta CAT no tiene pensum asignado. Informar a Registro","", '', 'fa fa-fw fa-3x fa-refresh fa-spin', 'alert alert-dismissible alert-danger');
            }

         }


if ($PCO_Accion=="VerInfocarnet")
    { 
        echo "<script language='JavaScript'> PCO_VentanaPopup('index.php?PCO_Accion=InformeCarnet&Presentar_FullScreen=1&Precarga_EstilosBS=0','carnet','toolbar=no, location=no, directories=0, directories=no, status=no, location=no, menubar=no ,scrollbars=yes, resizable=yes, fullscreen=no, titlebar=no, width=800, height=600'); </script>";
        echo '<script language="JavaScript">document.location="index.php"; </script>';
    }  

if ($PCO_Accion=="InformeCarnet")
    {
        include ("info_carnet.php");
    }   
   
    
//


                ///PCO_Mensaje("RECIBO LISTO PARA PAGO: ingrese acade","Fecha de pago hasta el 24 de febrero", '', 'fa fa-fw fa-3x fa-refresh fa-spin', 'alert alert-dismissible alert-warning');
///
/*

 echo "<script language='JavaScript'>		
        {
    		document.location='index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:17:1:id=$id';
    	}
        </script>";


header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:17:1:id:$id&Presentar_FullScreen=1&Precarga_EstilosBS=1");
  JBS_CONTA_LiquidarComisiones&empresa_id=$PCOSESS_JBSEmpresaId&fecha_liquidacion=$fecha_liquidacion&hora_liquidacion=$hora_liquidacion&DocumentoOrigen=$documento_origen&FechaInicioLiquidacion=$rango_inicial_liquidacion&FechaCierreLiquidacion=$rango_final_liquidacion';	


$RegistroPensum['id']
   echo "<script language='JavaScript'>
        {
    		document.location='index.php?PCO_Accion=JBS_CONTA_LiquidarComisiones&empresa_id=$PCOSESS_JBSEmpresaId&fecha_liquidacion=$fecha_liquidacion&hora_liquidacion=$hora_liquidacion&DocumentoOrigen=$documento_origen&FechaInicioLiquidacion=$rango_inicial_liquidacion&FechaCierreLiquidacion=$rango_final_liquidacion';
    	}
        </script>";

                echo "<script language='JavaScript'> window.location='index.php'; </script>";


        echo '<br><center><a href="index.php" class="btn btn-warning" >Regresar al escritorio</a></center>';



*/