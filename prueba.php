<?php
	require_once("./src/autoload.php");
	//require_once("./vendor/autoload.php");
	
	$cookie = array(
		'cookie' 		=> array(
			'use' 		=> true,
			'file' 		=> __DIR__ . "/cookie.txt"
		)
	);
	$config = array(
		'representantes_legales' 	=> true,
		'cantidad_trabajadores' 	=> true,
		'establecimientos' 			=> true,
		'cookie' 					=> $cookie
	);
	$sunat = new \Sunat\ruc( $config );
	
	$ruc = "20169004359";
	$dni = "44274795";
	
	$search1 = $sunat->consulta( $ruc );
	$search2 = $sunat->consulta( $dni );
	
	if( $search1->success == true )
	{
		echo "Empresa: " . $search1->result->razon_social;
	}
	
	if( $search2->success == true )
	{
		echo "Persona: " . $search1->result->razon_social;
	}
	
	// Mostrar en formato JSON
	echo $search1->json( );
	echo $search2->json( NULL, true ); // pretty format
?>
