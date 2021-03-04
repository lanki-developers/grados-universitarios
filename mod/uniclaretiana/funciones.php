<?php

function LANKIConsumirAPI($url){

	$url = "http://172.16.0.81/apigrados/public/api/" . $url;

  // create curl resource
	$curlHandle = curl_init();

	// set url
	curl_setopt($curlHandle, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);

	//Establecer cabeceras
	$headers = array(
		'Content-type: application/json',
		'Authorization: $5$api_grados$IwKxgZ5xTzoF17y2F6xqHP.zmwyO5GX7efl438Ksn8B',
	);

	//Agregar header a la petición
	curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

	// $output contains the output string
	$output = curl_exec($curlHandle);

	$result = json_decode($output, true);
	// close curl resource to free up system resources
	curl_close($curlHandle);

	return $result;
}
