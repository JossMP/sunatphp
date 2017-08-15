# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú], puedes ver una demo [aqui].
### Metodo de Uso
```sh
<?php
    require ("curl.php");
    require ("sunat.php");
    $cliente = new Sunat();
    $ruc = "20549500553";
    $dni = "00000000";
    //---------RUC----------
    print_r ( $cliente->search($ruc) );
    //---------DNI----------
    print_r ( $cliente->search($dni) );
?>
```
como resultado la funcion search nos retornara un array con los datos obtenidos, de no obtener los datos o aver algun error en el formato del ruc o dni nos retorna un mesaje de error

```sh
Array
(
    [success] => 1
    [result] => Array
        (
            [RUC] => 20549500553
            [RazonSocial] => ASERCO EB EMPRESA INDIVIDUAL DE RESPONSABILIDAD LIMITADA
            [Telefono] => 4260637 / 999354939
            [Condicion] => HABIDO
            [NombreComercial] => -
            [Tipo] => EMPRESA INDIVIDUAL DE RESP. LTDA
            [Inscripcion] => 06/09/2012
            [Estado] => ACTIVO
            [Direccion] => AV. PASEO DE LA REPUBLICA NRO. 291 INT. 903 (PLAZA GRAU) LIMA - LIMA - LIMA
            [SistemaEmision] => MANUAL
            [ActividadExterior] => SIN ACTIVIDAD
            [SistemaContabilidad] => MANUAL
            [EmisionElectronica] => -
            [PLE] => -
        )

)
```
en caso de error:
```sh
Array
(
    [success] => 0
    [msg] => Nro de RUC o DNI no valido.
)
```

[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
[aqui]: <https://geekdev.ml/demos>
