<?php
    require ("curl.php");
    require ("sunat.php");
    $cliente = new Sunat();
    $ruc="20552103816";
    $dni="XXXXXXXX";
    header('Content-Type: text/plain');
    echo "---------RUC----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($ruc), JSON_PRETTY_PRINT );
    echo "\n---------DNI----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($dni), JSON_PRETTY_PRINT );
?>
