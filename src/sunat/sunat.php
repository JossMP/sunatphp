<?php
	namespace Sunat;

	class ruc {
		var $curl = NULL;
		var $_legal = false;
		var $_trabs = false;
		var $_establecimientos = false;
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

			$this->_legal 	= (isset($config["representantes_legales"])) ? $config["representantes_legales"]:false;
			$this->_trabs 	= (isset($config["cantidad_trabajadores"])) ? $config["cantidad_trabajadores"]:false;
			$this->_establecimientos 	= (isset($config["establecimientos"])) ? $config["establecimientos"]:false;
			
			$this->curl->setReferer( "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/frameCriterioBusqueda.jsp" );
		}
		
		function getNumRand()
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
			$numRand = $this->curl->send($url);
			if( $this->curl->getHttpStatus()==200 && $numRand!="" )
				return $numRand;
			return false;
		}
		
		function consulta( $ruc )
		{
			if( (strlen($ruc)!=8 && strlen($ruc)!=11) || !is_numeric($ruc) )
			{
				$response = new \response\obj(array(
					'success' => false,
					'message' => 'Formato RUC/DNI no validos.'
				));
				return $response;
			}
			if( strlen( $ruc )==11 && is_numeric($ruc) && !$this->valid( $ruc ) )
			{
				$response = new \response\obj(array(
					'success' => false,
					'message' => 'RUC no valido'
				));
				return $response;
			}
			
			if( strlen( $ruc ) == 8 && is_numeric($ruc) )
			{
				$ruc = $this->dnitoruc($ruc);
			}
			
			$numRand = $this->getNumRand();
			
			if( $numRand !== false )
			{
				$data = array(
					'accion' => 'consPorRuc',
					'actReturn' =>  '1',
					'nroRuc' => $ruc,
					'numRnd' => $numRand
				);

				$url = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
				
				$response = $this->curl->send( $url, $data );
				
				if( $this->curl->getHttpStatus()==200 && $response!="" )
				{
					//RazonSocial
					$patron='/<input type="hidden" name="desRuc" value="(.*)">/';
					$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
					if(isset($matches[0]))
					{
						$RS = utf8_encode(str_replace('"','', ($matches[0][1])));
						$rtn = array(
							"ruc"=>$ruc,
							"razon_social"=>trim($RS)
						);
					}

					//Telefono
					$patron='/<td class="bgn" colspan=1>Tel&eacute;fono\(s\):<\/td>[ ]*-->\r\n<!--\t[ ]*<td class="bg" colspan=1>(.*)<\/td>/';
					$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
					if( isset($matches[0]) )
					{
						$rtn["telefono"] = trim($matches[0][1]);
					}

					// Condicion Contribuyente
					$patron='/<td class="bgn"[ ]*colspan=1[ ]*>Condici&oacute;n del Contribuyente:[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>[\r\n\t[ ]+]*(.*)[\r\n\t[ ]+]*<\/td>/';
					$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
					if( isset($matches[0]) )
					{
						$rtn["condicion"] = strip_tags(trim($matches[0][1]));
					}
					$busca=array(
						"nombre_comercial" 			=> "Nombre Comercial",
						"tipo" 						=> "Tipo Contribuyente",
						"fecha_inscripcion" 		=> "Fecha de Inscripci&oacute;n",
						"estado" 					=> "Estado del Contribuyente",
						"direccion" 				=> "Direcci&oacute;n del Domicilio Fiscal",
						"sistema_emision" 			=> "Sistema de Emisi&oacute;n de Comprobante",
						"actividad_exterior"		=> "Actividad de Comercio Exterior",
						"sistema_contabilidad" 		=> "Sistema de Contabilidad",
						"oficio" 					=> "Profesi&oacute;n u Oficio",
						"actividad_economica" 		=> "Actividad\(es\) Econ&oacute;mica\(s\)",
						"emision_electronica" 		=> "Emisor electr&oacute;nico desde",
						"comprobante_electronico" 	=> "Comprobantes Electr&oacute;nicos",
						"ple" 						=> "Afiliado al PLE desde"
					);
					foreach($busca as $i=>$v)
					{
						$patron='/<td class="bgn"[ ]*colspan=1[ ]*>'.$v.':[ ]*<\/td>[ ]*\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
						$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
						if(isset($matches[0]))
						{
							$rtn[$i] = trim(utf8_encode( preg_replace( "[\s+]"," ", ($matches[0][1]) ) ) );
						}
					}
					if( isset($rtn["comprobante_electronico"]) )
					{
						$nuevo = explode(',', $rtn["comprobante_electronico"]);
						if( is_array($nuevo))
						{
							$rtn["comprobante_electronico"] = $nuevo;
						}
						else
						{
							$rtn["comprobante_electronico"] = array( $rtn["comprobante_electronico"]);
						}
					}
					// Condicion Contribuyente
					$patron = '/<td width="(\d{2})%" colspan=1 class="bgn">Fecha de Inicio de Actividades:<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=1> (.*)<\/td>/';
					$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
					if( isset($matches[0][2]) )
					{
						$rtn["inicio_actividades"] = strip_tags(trim($matches[0][2]));
					}
					
					// Actividad Economica
					$patron='/<option value="00" > (.*) - (.*) <\/option>\r\n/';
					$rpta = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
					if( !empty($matches) )
					{
						$ae = array();
						foreach ($matches as $key => $value) 
						{
							$ae[] = array(
								'ciiu' 	=> utf8_encode(trim($value[1])),
								'descripcion' 	=> utf8_encode(trim($value[2]))
							);
						}
						$rtn["actividad_economica"] = $ae;
					}
					
					if( !empty($rtn) )
					{
						$establecimientos = array();
						if( $this->_establecimientos )
						{
							$establecimientos = $this->establecimientos( $ruc );
						}
						$rtn["establecimientos"] = $establecimientos;
						
						$legal = array();
						if( $this->_legal )
						{
							$legal = $this->RepresentanteLegal( $ruc );
						}
						$rtn["representantes_legales"] = $legal;
						
						$trabs = array();
						if( $this->_trabs )
						{
							$trabs = $this->numTrabajadores( $ruc );
						}
						$rtn["cantidad_trabajadores"] = $trabs;
						$response = new \response\obj(array(
							'success' 	=> 	true,
							'result' 	=> 	$rtn
						));
						return $response;
					}
					$response = new \response\obj(array(
						'success' 	=> 	false,
						'message' 	=> 	'No se encontraron datos suficientes.'
					));
					return $response;
				}
				else
				{
					$response = new \response\obj(array(
						'success' 	=> 	false,
						'message' 	=> 	'No se pudo conectar a sunat.'
					));
					return $response;
				}
			}
			$response = new \response\obj(array(
				'success' 	=> 	false,
				'message' 	=> 	'No se pudo conectar a sunat.'
			));
			return $response;
		}
		
		
		function numTrabajadores( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getCantTrab",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->curl->send( $url, $data );
			if( $rtn!="" && $this->curl->getHttpStatus()==200 )
			{
				$patron = "/<td align='center'>(.*)-(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>[\t|\s|\n]+<td align='center'>(.*)<\/td>/";
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$cantidad_trabajadores = array();
					$i = 1;
					foreach( $matches as $obj )
					{
						$cantidad_trabajadores[]=array(
							"periodo" 				=> $obj[1]."-".$obj[2],
							"anio" 					=> $obj[1],
							"mes" 					=> $obj[2],
							"total_trabajadores" 	=> $obj[3],
							"pensionista" 			=> $obj[4],
							"prestador_servicio" 	=> $obj[5]
						);
					}
					return $cantidad_trabajadores;
				}
			}
			return array();
		}
		
		function establecimientos( $ruc )
		{
			$url = 'https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias';
			$data = array(
				'nroRuc' => $ruc,
				'accion' => 'getLocAnex',
				'desRuc' 	=> ''
			);
			$rtn = $this->curl->send( $url, $data );
			if( $rtn!="" && $this->curl->getHttpStatus()==200 )
			{
				$patron = '/<td align="[center|left]+"[ ]*class=bg>\r\n[ ]*[\r\n]*[ ]*(.*)<\/td>/';
				$output = preg_match_all($patron, $rtn, $matches);
				if( !empty($matches[1]) )
				{
					if( count($matches[1])%4 == 0 )
					{
						$nuevo = array_chunk( $matches[1], 4 );
						$establecimientos = array();
						foreach($nuevo as $value)
						{
							$establecimientos[]=array(
								'codigo' 				=> utf8_encode(trim( $value[0] )),
								'tipo' 					=> utf8_encode(trim( $value[1] )),
								'Direccion' 			=> utf8_encode(trim( preg_replace('/[ ]*-/', ' -', $value[2]) )),
								'activida_economica'	=> utf8_encode(trim( $value[3] ))
							);
						}
					
						return $establecimientos;
					}
				}
			}
			return array();
		}
		
		function RepresentanteLegal( $ruc )
		{
			$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
			$data = array(
				"accion" 	=> "getRepLeg",
				"nroRuc" 	=> $ruc,
				"desRuc" 	=> ""
			);
			$rtn = $this->curl->send( $url, $data );
			if( $rtn!="" && $this->curl->getHttpStatus()==200 )
			{
				$patron = '/<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="center">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>[\t|\s|\n]+<td class=bg align="left">[\t|\s|\n]+(.*)<\/td>/';
				$output = preg_match_all($patron, $rtn, $matches, PREG_SET_ORDER);
				if( count($matches) > 0 )
				{
					$representantes_legales = array();
					$i = 1;
					foreach( $matches as $obj )
					{
						$representantes_legales[]=array(
							"tipodoc" 				=> trim($obj[1]),
							"numdoc" 				=> trim($obj[2]),
							"nombre" 				=> utf8_encode(trim($obj[3])),
							"cargo" 				=> utf8_encode(trim($obj[4])),
							"desde" 				=> trim($obj[5]),
						);
					}
					return $representantes_legales;
				}
			}
			return array();
		}
		
		function dnitoruc($dni)
		{
			if ($dni!="" || strlen($dni) == 8)
			{
				$suma = 0;
				$hash = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
				$suma = 5; // 10[NRO_DNI]X (1*5)+(0*4)
				for( $i=2; $i<10; $i++ )
				{
					$suma += ( $dni[$i-2] * $hash[$i] ); //3,2,7,6,5,4,3,2
				}
				$entero = (int)($suma/11);

				$digito = 11 - ( $suma - $entero*11);

				if ($digito == 10)
				{
					$digito = 0;
				}
				else if ($digito == 11)
				{
					$digito = 1;
				}
				return "10".$dni.$digito;
			}
			return false;
		}
		
		function valid($valor) // Script SUNAT
		{
			$valor = trim($valor);
			if ( $valor )
			{
				if ( strlen($valor) == 11 ) // RUC
				{
					$suma = 0;
					$x = 6;
					for ( $i=0; $i<strlen($valor)-1; $i++ )
					{
						if ( $i == 4 )
						{
							$x = 8;
						}
						$digito = $valor[$i];
						$x--;
						if ( $i==0 )
						{
							$suma += ($digito*$x);
						}
						else
						{
							$suma += ($digito*$x);
						}
					}
					$resto = $suma % 11;
					$resto = 11 - $resto;
					if ( $resto >= 10)
					{
						$resto = $resto - 10;
					}
					if ( $resto == $valor[strlen($valor)-1] )
					{
						return true;
					}
				}
			}
			return false;
		}
	}
