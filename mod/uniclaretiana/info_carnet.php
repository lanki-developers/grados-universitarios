<?php

// include_once '../../core/configuracion.php';
// // Inicia las conexiones con la BD y las deja listas para las operaciones
// include_once '../../core/conexiones.php';
// // Incluye definiciones comunes de la base de datos
// include_once '../../inc/practico/def_basedatos.php';
// // Incluye archivo con algunas funciones comunes usadas por la herramienta
// include_once '../../core/comunes.php';

$estudiantes=PCO_EjecutarSQL("SELECT e.login,
CONCAT(e.primer_nombre,' ',e.segundo_nombre,' ',e.primer_apellido ,' ',e.segundo_apellido) AS Nombres,e.documento AS Cedula,e.rh,e.nivel,
a.foto,a.pensum_estudiante_id,p.prog_nombre, p.unid_nombre AS cat
FROM app_estudiante AS e
JOIN app_pregrado_adjuntos AS a ON e.login=a.login
JOIN app_estudiante_pensum AS p ON a.pensum_estudiante_id = p.id
WHERE e.aseguro=1 AND e.nivel='Pregrado'
UNION
SELECT e.login,
CONCAT(e.primer_nombre,' ',e.segundo_nombre,' ',e.primer_apellido ,' ',e.segundo_apellido) AS Nombres,e.documento AS Cedula,e.rh,e.nivel,
a.foto,a.pensum_estudiante_id,p.prog_nombre, p.unid_nombre AS cat
FROM app_estudiante AS e
JOIN app_posgrado_adjuntos AS a ON e.login=a.login
JOIN app_estudiante_pensum AS p ON a.pensum_estudiante_id = p.id
WHERE e.aseguro=1 AND e.nivel='Posgrado'
ORDER BY cat, nivel, prog_nombre");
?>
<p align="center"><b>INFORMACION CARNET </b></p>
<br>
<table border="1" cellspacing="0" cellpadding="0" width="99%" align="CENTER">
	 <tr align="CENTER" bgcolor="#C0C0C0">
	    <td><font size="1"><b>#</b></font></td>
		<td idth="30%"><font size="1"><b>Nombres</b></font></td>
		<td><font size="1"><b>Cedula</b></font></td>
		<td><font size="1"><b>Rh</b></font></td>
		<td width="15%"><font size="1"><b>CAT</b></font></td>
		<td width="10%"><font size="1"><b>Nivel</b></font></td>
		<td width="15%"><font size="1"><b>Programa</b></font></td>
    	<td><font size="1"><b>Foto</b></font></td>
	 </tr>
	<?php $contador=1;
	while ($row = $estudiantes->fetch())
    	 {
    	    $foto="";
    	    $cat_id="";
    	    $login=$row['login'];


 	        $foto=explode("|",$row['foto']);
          if ($foto[1]=="image/png" || $foto[1]=="image/jpeg")
              $foto=$foto[0];

    	 ?>
    	 <tr align="CENTER">
    	    <td><font size="1"><?php echo $contador; ?></font></td>
    			<td idth="30%"><font size="1"><?php echo $row['Nombres']; ?></td>
    			<td><font size="1"><?php echo $row['Cedula']; ?></td>
    			<td><font size="1"><?php echo $row['rh']; ?></td>
    			<td><font size="1"><?php echo $row['cat']; ?></td>
    			<td><font size="1"><?php echo $row['nivel']; ?></td>
    			<td><font size="1"><?php echo $row['prog_nombre']; ?></td>
    			<td width="20%"><font size="1"><?php
    		    if ($foto!="")
    		        echo '<img src="'.$foto.'" width="100%" />';
    		    else
    		        echo 'Archivo de foto no encontrado o no v&aacute;lido';
    		    ?></td>
    	 </tr>
    	 <?php
    	 $contador=$contador+1;
    	 } ?>
</table>
<br>
