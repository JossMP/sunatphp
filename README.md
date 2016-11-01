# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú]
### Metodo de Uso
```sh
<?php
    require ("curl.php");
    require ("sunat.php");
    $cliente = new Sunat();
    $ruc="10442747950";
    echo json_encode( $cliente->BuscaDatosSunat($ruc), JSON_PRETTY_PRINT );
?>
```
[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
