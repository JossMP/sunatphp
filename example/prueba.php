<?php

require_once("../vendor/autoload.php");

$config = [
	'representantes_legales' 	=> true,
	'cantidad_trabajadores' 	=> true,
	'establecimientos' 			=> true,
	'deuda' 					=> true,
	'cookie' 					=> [
		'cookie' 		=> [
			'use' 		=> true,
			'file' 		=> __DIR__ . "/cookie.txt"
		]
	]
];

$sunat = new \jossmp\sunat\ruc($config);

$ruc = "20169004359";
$dni = "44274795";

$search1 = $sunat->consulta($ruc);
$search2 = $sunat->consulta($dni);

if ($search1->success == true) {
	echo "\n";
	echo "Empresa: " . $search1->result->razon_social . "\n";
	echo $search1->json(NULL, true);
	echo "\n\n";
}

if ($search2->success == true) {
	echo "\n";
	echo "Persona: " . $search1->result->razon_social . "\n";
	echo $search2->json(NULL, true);
	echo "\n\n";
}
