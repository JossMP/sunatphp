<?php
	require_once("./src/autoload.php");
	
	$company = new \Sunat\Sunat( true, true );
	$ruc = "20169004359";
	$dni = "44274795";
	
	$search1 = $company->search( $ruc );
	$search2 = $company->search( $dni );
	
	var_dump($search1);
	var_dump($search2);
	
	if( $search1->success == true )
	{
		echo "Empresa: " . $search1->result->RazonSocial;
	}
	
	if( $search2->success == true )
	{
		echo "Persona: " . $search1->result->RazonSocial;
	}
	
	// Mostrar en formato XML/JSON
	echo $search1->json();
	echo $search1->xml('empresa');
	
?>
