<?php
	header('Content-Type: text/plain');

	require ("../src/autoload.php");

	$cliente = new \Sunat\Sunat();
	
	$ruc = ( isset($_REQUEST["nruc"]))? $_REQUEST["nruc"] : false;
	echo $cliente->search( $ruc, true );
?>
