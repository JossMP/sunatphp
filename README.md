# SUNAT PERU
Clase php para consultar los datos de la web de [Sunat Perú] desde php.

### Metodo de Uso Consultas Sunat
```sh
<?php
	require_once("vendor/autoload.php");

	$config = [
		'representantes_legales' 	=> true,
		'cantidad_trabajadores' 	=> true,
		'establecimientos' 			=> true,
		'deuda' 					=> true,
	];

	$sunat = new \jossmp\sunat\ruc($config);

	$ruc = "20169004359";
	$dni = "44274795";

	$search1 = $sunat->consulta($ruc);
	$search2 = $sunat->consulta($dni);

	if ($search1->success == true) {
		echo "\n";
		echo "Empresa: " . $search1->result->razon_social . "\n";
		echo $search1->json(NULL, true);
		echo "\n\n";
	}

	if ($search2->success == true) {
		echo "\n";
		echo "Persona: " . $search1->result->razon_social . "\n";
		echo $search2->json(NULL, true);
		echo "\n\n";
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
	$search->result->ruc;
	$search->result->razon_social;
	$search->result->direccion;
	$search->result->departamento;
	$search->result->provincia;
	$search->result->distrito;
	$search->result->estado;
	$search->result->condicion;
	$search->result->tipo;
	$search->result->nombre_comercial;
	$search->result->fecha_inscripcion;
	$search->result->sistema_emision;
	$search->result->actividad_exterior;
	$search->result->sistema_contabilidad;
	$search->result->comprobante_impreso; // List(Array)
	$search->result->comprobante_electronico;
	$search->result->ple;
	$search->result->inicio_actividades;
	$search->result->actividad_economica; // List(Array)
	$search->result->oficio;
	$search->result->ubigeo;
	$search->result->dir_tipo_via;
	$search->result->dir_cod_zona;
	$search->result->dir_tipo_zona;
	$search->result->dir_num;
	$search->result->dir_interior;
	$search->result->dir_lote;
	$search->result->dir_dpto;
	$search->result->dir_manzana;
	$search->result->dir_km;
	$search->result->dir_nomb_via;
	$search->result->emision_electronica; // date
	$search->result->telefono;
	$search->result->establecimientos; // List
	$search->result->cantidad_trabajadores; // List
	$search->result->representantes_legales; // List
	$search->result->deuda_coactiva; // List
	$search->result->fecha_registro;
	$search->result->fecha_actualizacion;
	$search->result->completo;
	$search->result->contribuyente;
	$search->result->contribuyente_tipo_doc;
	$search->result->contribuyente_num_doc;
?>
```

### Metodo de Uso Consulta Tipo cambio ( USD => PEN )

```sh
<?php
	require_once("vendor/autoload.php");
	$tc = new \jossmp\sunat\tipo_cambio();

	$search = $tc->ultimo_tc();

	// $search = $tc->consulta('02','2019'); // No disponible
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
- PHP 5.4.0 o superior
- jossmp/navigate
- jossmp/response
```


Tambien puede interesarte muestra clase para buscar datos de personas mediante el DNI: [Ver repositorio]
Donaciones: [PayPal]


Copyright (C), 2018 Josue Mazco GNU General Public License 3 (http://www.gnu.org/licenses/)

[Ver repositorio]: <https://github.com/JossMP/datos-peru/>
[Ver demo]: <https://git.tryout.top/sunat/>
[PayPal]: <https://www.paypal.me/JossMP>
[Sunat Perú]: <http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias>
