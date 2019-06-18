# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú] desde php.

### Metodo de Uso Consultas Sunat
```sh
<?php
	require_once("./src/autoload.php");
	$cookie = array(
		'cookie' 		=> array(
			'use' 		=> true,
			'file' 		=> __DIR__ . "/cookie.txt"
		)
	);
	$config = array(
		'representantes_legales' 	=> true,
		'cantidad_trabajadores' 	=> true,
		'establecimientos' 			=> true,
		'cookie' 					=> $cookie
	);
	
	$sunat = new \Sunat\ruc( $config );
	
	$ruc = "20169004359";
	$dni = "44274795";
	
	$search1 = $sunat->consulta( $ruc );
	$search2 = $sunat->consulta( $dni );
?>
```
### Error en busquedas / sin resultados
en caso de no haber encontrado resultados $search->success es false
```sh
<?php
	if( $search->success==false )
	{
		echo "ERROR : " . $search->message;
	}
?>
```

### Datos que se obtienen
```sh
<?php
	...
	$search->result->ruc
    $search->result->razon_social
    $search->result->condicion
    $search->result->nombre_comercial
    $search->result->tipo
    $search->result->fecha_inscripcion
    $search->result->estado
    $search->result->direccion 					// Solo Empresas
    $search->result->sistema_emision
    $search->result->actividad_exterior
    $search->result->sistema_contabilidad
    $search->result->oficio						// Solo Personas
    $search->result->emision_electronica
    $search->result->comprobante_electronico 	// array
    $search->result->ple
    $search->result->inicio_actividades
    $search->result->actividad_economica 		// array
    $search->result->establecimientos 			// array
    $search->result->representantes_legales 	// array
    $search->result->cantidad_trabajadores 		// array

?>
```

### Metodo de Uso Consulta Tipo cambio ( USD => PEN )

```sh
	require_once("./src/autoload.php");
	$cookie = array(
		'cookie' 		=> array(
			'use' 		=> true,
			'file' 		=> __DIR__ . "/cookie.txt"
		)
	);
	$config = array(
		'cookie' 					=> $cookie
	);
	
	$test = new \Sunat\tipo_cambio( $config );

	$search = $test->consulta('02','2019');
```

### Mostrar Resultados en JSON / XML
```sh
<?php
	...
	if( $search->success == true )
	{
		echo $search->json( );
		echo $search->json( 'callback' ); // para llamadas desde js
	}
	
	if( $search->success == true )
	{
		echo $search->xml( ); 
		echo $search->xml( 'persona' ); // define nodo raiz
	}
?>
```

### Instalacion mediante composer
```sh
	composer require -o "jossmp/sunatphp"
```

```sh
<?php
    require ("./vendor/autoload.php");
    ...
?>
```

### Pre-requisitos
```sh
- cURL
- PHP 5.2.0 o superior
```


Tambien puede interesarte muestra clase para buscar datos de personas mediante el DNI: [Ver repositorio]
Donaciones: [PayPal]


Copyright (C), 2018 Josue Mazco GNU General Public License 3 (http://www.gnu.org/licenses/)

[Ver repositorio]: <https://github.com/JossMP/datos-peru/>
[Ver demo]: <https://demo.peruanosenlinea.com/>
[PayPal]: <https://www.paypal.me/JossMP>
[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
