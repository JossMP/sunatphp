<?php
	require_once("./src/autoload.php");
	
	$cliente = new \Sunat\Sunat(true, true);
	$ruc = "20549500553"; // RUC de 11 digitos
	$dni = "00000000"; // DNI de 8 digitos
	
	print_r ( $cliente->search( $ruc ) );
	print_r ( $cliente->search( $dni ) );
?>
