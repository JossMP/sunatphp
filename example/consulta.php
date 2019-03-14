<?php
	header('Content-Type: text/plain');

	require ("../src/autoload.php");

	$company = new \Sunat\Sunat( true, true );
	
	$ruc = ( isset($_REQUEST["nruc"]))? $_REQUEST["nruc"] : false;
	$search1 = $company->search( $ruc );
	
	echo $search1->json();
	
?>
