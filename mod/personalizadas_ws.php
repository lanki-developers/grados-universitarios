<?php
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
			Title: Funciones personalizadas para WebServices
			Ubicacion *[/personalizadas_ws.php]*.  Archivo que contiene la declaracion de variables y funciones por parte del usuario o administrador del sistema para la ejecucion de webservices

			Codigo de ejemplo:
				(start code)
					<?php if ($WS=="Mi_WebService") 
						{
							// Mis operaciones a realizar
						}
					?>
				(end)

			Comentario:
			Este archivo solamente es ejecutado ante el llamado de webservices con llave correcta.
			Si desea personalizar funciones de uso general para los usuarios deberia utilizar el archivo personalizadas.php
			Utilice el condicional para diferenciar el web service solicitado y ser asi ejecutado.
			
			Los WebServices disponibles y predefinidos por Practico se encuentran definidos en core/ws_funciones.php pero
			no deberia ser editado pues ante cualquier actualizacion el archivo cambiaria borrando sus web-services.  Use solo este archivo para
			sus funciones personalizadas para webservices garantizando su disponibilidad despues de cada actualizacion de la herramienta.

			El consumo de los web services requiere el envio de los siguientes parametros minimos a la raiz de Practico en cada llamado:
			
			* WS=1: Siempre iniciado en 1 indica a Practico que debe activar el modo de WebServices
			* PCO_WSKey: La llave generada para consumir los WebServices, la llave de paso de instalacion es incluida por defecto
			* PCO_WSId: El identificador unico del metodo o funcion de webservices a llamar
			* OTROS: Parametros adicionales requeridos por la funcion pueden ser enviados por URL o metodo POST al llamar el WebService.

			Ejemplo:  www.sudominio.com/practico/?PCO_WSOn=1&PCO_WSKey=AFSX345DF&PCO_WSId=verificar_credenciales
			*/

//Valida si el estudiante no tiene ficha de datos registrada
   /*
    if ($PCOSESS_LoginUsuario!="" && $PCO_Accion=="PCO_VerMenu")
        {
            if ((!$UsuarioEsSolicitante && !$UsuarioEsPrestador) && $RegistroUsuario["usuario_interno"]=="0")
                {
                    //Redirecciona a Registrarse como solicitante o prestador
                    header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:11:0&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                    die();
                }
                
        } */
        
        
// ################# CONSULTA INFORMACION del PENSUM !!!!!!!!!!!!!!!!!!!!!!!!!!11
if ($PCO_WSId=="InfoPensum") 
	{
        $Pensum=PCO_EjecutarSQL("SELECT prog_codigoicfes,estp_creditosaprobados FROM ".$TablasApp."estudiante_pensum WHERE id='$IdPensum'")->fetch();
        $codigo=$Pensum["prog_codigoicfes"];
        $AprobadosEstudiante=$Pensum["estp_creditosaprobados"];
        $Programa=PCO_EjecutarSQL("SELECT creditos FROM ".$TablasApp."programas WHERE codigo_rca='$codigo'")->fetch();
        $CreditosRequeridos=$Programa["creditos"];
        /*
        $Registro=PCO_EjecutarSQL("SELECT saldo,sobregiro,cupo_sobregiro FROM ".$TablasApp."bancos WHERE id='$IdBanco' AND empresa_id='{$PCOSESS_JBSEmpresaId}'")->fetch();
        ob_clean();
	    echo json_encode($Registro);
        die(); */
        if ($AprobadosEstudiante!="" && $CreditosRequeridos!="")
                {
                  if ($AprobadosEstudiante>=$CreditosRequeridos)
                    {
                        
                        $Registros = array(
                          array( 'label' => 'cumple', 'value' => "cumple"),
                          array( 'label' => 'AprobadosEstudiante', 'value' => $AprobadosEstudiante ),
                          array( 'label' => 'CreditosRequeridos', 'value' => $CreditosRequeridos ),
                        );
                        
                        echo json_encode( $Registros );
                        die();
                    }
                    else {
                        $Registros = array(
                          array( 'label' => 'cumple', 'value' => "nocumple"),
                          array( 'label' => 'AprobadosEstudiante', 'value' => $AprobadosEstudiante ),
                          array( 'label' => 'CreditosRequeridos', 'value' => $CreditosRequeridos ),
                        );
                        echo json_encode( $Registros );
                        die();
                    }
                    
                    
                }
                else 
                    {
                        $FaltaAlgunodelosCreditos="SinCreditos";
                        echo json_encode($FaltaAlgunodelosCreditos);
                        die();
                    }
	}