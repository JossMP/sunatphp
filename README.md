# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú] desde php.

### Metodo de Uso
```sh
<?php
	require_once( __DIR__ . "/src/autoload.php" );
	
	$company = new \Sunat\Sunat( true, true );
	
	$ruc = "20169004359";
	$dni = "44274795";
	
	$search1 = $company->search( $ruc );
	$search2 = $company->search( $dni );
	
	if( $search1->success == true )
	{
		echo "Empresa: " . $search1->result->razon_social;
	}
	
	if( $search2->success == true )
	{
		echo "Persona: " . $search1->result->razon_social;
	}
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
	$search = $essalud->search( $dni );
	$search = $mintra->search( $dni );
	
	$search->result->ruc;
	$search->result->razon_social;
	$search->result->condicion;
	$search->result->nombre_comercial;
	$search->result->tipo;
	$search->result->fecha_inscripcion;
	$search->result->estado;
	$search->result->direccion; 			// Solo Empresas
	$search->result->sistema_emision;
	$search->result->actividad_exterior;
	$search->result->oficio; 				// Solo Personas
	$search->result->actividad_economica;
	$search->result->sistema_contabilidad;
	$search->result->emision_electronica;
	$search->result->ple;
	
	$search->result->emision_electronica;	// array
	$search->result->cantidad_trabajadores;	// array
?>
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
		echo PHP_EOL . $search->xml( ); 
		echo PHP_EOL . $search->xml( 'persona' ); // define nodo raiz
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
[Ver demo]: <https://www.peruanosenlinea.com/busca-personas-por-el-dni/>
[PayPal]: <https://www.paypal.me/JossMP>
[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
