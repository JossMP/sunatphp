<?php
	namespace Sunat;

	class tipo_cambio {
		var $curl = NULL;
		function __construct( $config = array() )
		{
			$this->curl = new \CURL\cURL();
			
			if( isset($config["proxy"]) )
			{
				$use 	= (isset($config["proxy"]["use"])) ? $config["proxy"]["use"] : FALSE;
				$host 	= (isset($config["proxy"]["host"])) ? $config["proxy"]["host"] : NULL;
				$port 	= (isset($config["proxy"]["port"])) ? $config["proxy"]["port"] : NULL;
				$type 	= (isset($config["proxy"]["type"])) ? $config["proxy"]["type"] : NULL;
				$user 	= (isset($config["proxy"]["user"])) ? $config["proxy"]["user"] : NULL;
				$pass 	= (isset($config["proxy"]["pass"])) ? $config["proxy"]["user"] : NULL;
				
				$this->curl->useProxy($use);
				$this->curl->setProxyHost($host);
				$this->curl->setProxyPort($port);
				$this->curl->setProxyType($type);
				
				$this->curl->setProxyUser($user);
				$this->curl->setProxyPass($pass);
			}
			if( isset($config["cookie"]) )
			{
				$use 	= (isset($config["cookie"]["use"])) ? $config["cookie"]["use"] : TRUE;
				$file 	= (isset($config["cookie"]["file"])) ? $config["cookie"]["file"] : 'cookie.txt';
				
				$this->curl->useCookie($use);
				$this->curl->setCookiFileLocation($file);
			}
			
			$this->curl->setReferer( "https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias" );
		}
		
		function consulta( $mes = NULL, $anio = NULL )
		{
			$mes 	= ( $mes==NULL ) ? date("m") : $mes;
			$anio 	= ( $anio==NULL ) ? date("Y") : $anio;
			
			if( strlen($mes)!=2 || !is_numeric($mes) )
			{
				$response = new \response\obj(array(
					'success' => false,
					'message' => 'Formato Mes no validos.'
				));
				return $response;
			}
			if( strlen($anio)!=4 || !is_numeric($anio) )
			{
				$response = new \response\obj(array(
					'success' => false,
					'message' => 'Formato Año no validos.'
				));
				return $response;
			}
			
			$url = 'https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias';
			
			$data = array(
				'mesElegido' 	=> $mes,
				'anioElegido' 	=> $anio,
				'mes' 			=> $mes,
				'anho' 			=> $anio,
				'accion' 		=> 'init',
				'email' 		=> ''
			);
			$header = array(
				'Upgrade-Insecure-Requests' 	=> '1'
			);
			$this->curl->setHttpHeader( $header );
			$response = $this->curl->send( $url, $data );
			$response = $this->curl->send( $url, $data );
			if( $this->curl->getHttpStatus()==200 && $response!="" )
			{
				libxml_use_internal_errors(true);
				$doc = new \DOMDocument();
				$doc->strictErrorChecking = FALSE;
				$doc->loadHTML( $response );
				libxml_use_internal_errors(false);

				$xml = simplexml_import_dom($doc);
				
				$dias = $xml->xpath("//table/tr/td[@class='H3']");
				$compra_venta = $xml->xpath("//table/tr/td[@class='tne10']");
				$fecha = $xml->xpath("//center/h3");
				$rtn = array();
				
				$periodo = $mes.'-'.$anio;
				if( !empty($fecha) )
				{
					$periodo = (string)$fecha[0];
				}
				if( !empty($dias) && !empty($compra_venta) && count((array)$dias) == count((array)$compra_venta)/2 )
				{
					foreach($dias as $i => $obj)
					{
						$rtn[$i]['fecha'] = str_pad(trim((string)$obj->strong),2,0,STR_PAD_LEFT) . ' - '. $periodo;
						//$rtn[$i]['fecha'] = str_pad(trim((string)$obj->strong),2,0,STR_PAD_LEFT) . '/'. $mes.'/'.$anio;
					}
					$cont = 0;
					foreach($compra_venta as $i=>$obj)
					{
						if( ($i+1)%2==0 )
						{
							$rtn[$cont]['venta'] = trim((string)$obj);
							$cont++;
						}
						else
						{
							$rtn[$cont]['compra'] = trim((string)$obj);
						}
					}
					$response = new \response\obj(array(
						'success' 	=> true,
						'result' 	=> $rtn
					));
					return $response;
				}
				$response = new \response\obj(array(
					'success' 	=> 	false,
					'message' 	=> 	'No se encontraron datos suficientes.'
				));
				return $response;
			}
			$response = new \response\obj(array(
				'success' 	=> 	false,
				'message' 	=> 	'No se pudo conectar a sunat.'
			));
			return $response;
		}
	}
