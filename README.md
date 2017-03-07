# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú]
### Metodo de Uso
```sh
<?php
    require ("curl.php");
    require ("sunat.php");
    $cliente = new Sunat();
    $ruc="20552103816";
    $dni="XXXXXXXX";
    header('Content-Type: application/json');
    echo "---------RUC----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($ruc), JSON_PRETTY_PRINT );
    echo "\n---------DNI----------\n";
    echo json_encode( $cliente->BuscaDatosSunat($dni), JSON_PRETTY_PRINT );
?>
```
[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
