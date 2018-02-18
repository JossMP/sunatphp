<?php
	function isLoad( $name )
	{
		if ( !extension_loaded($name) )
		{
			return false;
			// ---
			$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
			if ( !dl($prefix . $name . '.' . PHP_SHLIB_SUFFIX) )
			{
				exit();
			}
		}
		return true;
	}
	
	if( !isLoad("curl") )
	{
		echo "cURL no Cargado...\n";
		exit();
	}
	
	// --------------------------------
	
	require_once("./src/autoload.php");
	
	$cliente = new \Sunat\Sunat( true, true );
	$ruc = "20169004359"; // RUC de 11 digitos
	$dni = "00000000"; // DNI de 8 digitos
	
	echo $cliente->search( $ruc, true ); // json
	//echo $cliente->search( $dni, true ); // json
	//print_r ( $cliente->search( $ruc ) ); // array
	
?>
