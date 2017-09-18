<?php
	require_once("vendor/autoload.php");
	//require_once("src/curl.php");
	//require_once("src/sunat.php");
	
	use service\Sunat;
	$cliente = new Sunat();
	$ruc="20549500553"; // RUC de 11 digitos
	$dni="00000000"; // DNI de 8 digitos
	print_r ( $cliente->search( $ruc ) );
	print_r ( $cliente->search( $dni ) );
?>
