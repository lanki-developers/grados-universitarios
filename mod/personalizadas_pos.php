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
			Title: Funciones personalizadas
			Ubicacion *[/personalizadas_pos.php]*.  Archivo que contiene la declaracion de variables y funciones por parte del usuario o administrador del sistema que deben ser cargadas justo antes de finalizar la aplicacion

			Codigo de ejemplo:
				(start code)
					<?php if ($PCO_Accion=="Mi_accion_XYZ") 
						{
							// Mis operaciones a realizar
						}
					?>
				(end)

			Comentario:
			Agregue en este archivo las funciones o acciones que desee vincular a menues especificos o realizacion de operaciones internas.
			Utilice el condicional para diferenciar la accion recibida y ser asi ejecutada. Puede vincularlos mediante forms.

            Por favor considere la construccion de un nuevo modulo antes que implementar rutinas sobre este archivo
            Please consider to build a new module before to deploy rutines in this file
            */

//Redirecciona los usuarios cuando ingresan y tienen plantilla al formulario de menu segun su rol
    if ($PCO_Accion=="PCO_VerMenu") 
    	{
            $RegistroUsuario=PCO_EjecutarSQL("SELECT plantilla_permisos FROM ".$TablasCore."usuario WHERE login=? ","$PCOSESS_LoginUsuario")->fetch();
            if ($RegistroUsuario["plantilla_permisos"]=="plantilla_estudiante")
               
                {
                    //Actualiza estado de datos en sistema.
                    $estudiante_estado=PCO_EjecutarSQL("SELECT aseguro FROM app_estudiante WHERE login='$PCOSESS_LoginUsuario'")->fetch();
                    $aseguro=$estudiante_estado["aseguro"];
                    
                    if($aseguro==1)
                    {
                        PCO_EjecutarSQLUnaria("UPDATE app_estudiante_estado SET datos='Si' WHERE login='$PCOSESS_LoginUsuario' ");
                    }
                    
                	PCO_CargarFormulario(11,1);
                    //echo "<script language='JavaScript'> window.location='index.php'; </script>";
                    //header("Location: index.php?PCO_Accion=PCO_CargarObjeto&PCO_Objeto=frm:11:1&Presentar_FullScreen=1&Precarga_EstilosBS=1");
                    //die();
                }
                else{
                    //echo "<script language='JavaScript'> window.location='index.php'; </script>";
                }
        }