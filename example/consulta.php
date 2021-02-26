<?php
header('Content-Type: text/plain');

require("../vendor/autoload.php");
$cookie = array(
	'cookie' 		=> array(
		'use' 		=> true,
		'file' 		=> __DIR__ . "/cookie.txt"
	)
);

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

$company = new \jossmp\sunat\ruc($config);

$ruc = (isset($_REQUEST["nruc"])) ? $_REQUEST["nruc"] : false;

$search1 = $company->consulta($ruc);

echo $search1->json(NULL, true);
