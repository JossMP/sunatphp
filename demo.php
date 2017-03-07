<?php
    require ("curl.php");
    require ("sunat.php");
    $cliente = new Sunat();
    $ruc="20552103816";
    $dni="44274795";
    header('Content-Type: application/json');
    echo "---------RUC----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($ruc), JSON_PRETTY_PRINT );
    echo "\n---------DNI----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($dni), JSON_PRETTY_PRINT );
?>
