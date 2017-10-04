# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú], puedes ver una demo [aqui].
### Instalacion
```sh
	composer require -o "jossmp/sunatphp"
```
### Metodo de Uso
```sh
<?php
	require_once("vendor/autoload.php");
	//require_once("src/autoload.php");
	$cliente = new \Sunat\Sunat();
	$ruc="20549500553"; // RUC de 11 digitos
	$dni="00000000"; // DNI de 8 digitos
	print_r ( $cliente->search( $ruc ) );
	print_r ( $cliente->search( $dni ) );
?>
```
como resultado la funcion search nos retornara un array con los datos obtenidos, de no obtener los datos o encontrar algun error en el formato del ruc o dni nos retorna un mesaje de error

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
Tambien puedes usar el 2do parametro de la funciona search para tener como respuesta un objeto JSON

```sh
	$cliente->search( $ruc ,true );
```


[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
[aqui]: <https://demos.geekdev.ml/sunat>
