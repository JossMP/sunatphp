<?php
	header('Content-Type: text/plain');

	require ("curl.php");
	require ("sunat.php");

	$cliente = new Sunat();
	$ruc="00000000000"; // RUC de 11 digitos
	$dni="00000000"; //  DNI de 8 digitos

	print_r ($cliente->search( $ruc ));

	print_r ($cliente->search( $dni ));
?>
