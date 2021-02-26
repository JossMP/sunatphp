<?php
require_once('../vendor/autoload.php');
$tc = new \jossmp\sunat\tipo_cambio();

echo $tc->ultimo_tc();
