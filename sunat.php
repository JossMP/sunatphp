<?php
	class Sunat{
		var $cc;  // cUrl
		function __construct()
		{
			$this->cc = new cURL();
		}
		function getNumRand()
		{
			$url="http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
			$numRand = $this->cc->send($url);
			return $numRand;
		}
		function getDataRUC( $ruc )
		{
			$numRand = $this->getNumRand();
			$rtn = array();
			if($ruc != "" && $numRand!=false)
			{
				$data = array(
					"nroRuc" => $ruc,
					"accion" => "consPorRuc",
					"numRnd" => $numRand
				);

				$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
				$Page = $this->cc->send($url,$data);

				//RazonSocial
				$patron='/<input type="hidden" name="desRuc" value="(.*)">/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if(isset($matches[0]))
				{
					$RS = utf8_encode(str_replace('"','', ($matches[0][1])));
					$rtn = array("RUC"=>$ruc,"RazonSocial"=>trim($RS));
				}

				//Telefono
				$patron='/<td class="bgn" colspan=1>Tel&eacute;fono\(s\):<\/td>[ ]*-->\r\n<!--\t[ ]*<td class="bg" colspan=1>(.*)<\/td>/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( isset($matches[0]) )
				{
					$rtn["Telefono"] = trim($matches[0][1]);
				}

				// Condicion Contribuyente
				$patron='/<td class="bgn"[ ]*colspan=1[ ]*>Condici&oacute;n del Contribuyente:[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>[\r\n\t[ ]+]*(.*)[\r\n\t[ ]+]*<\/td>/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if( isset($matches[0]) )
				{
					$rtn["Condicion"] = strip_tags(trim($matches[0][1]));
				}

				$busca=array(
					"NombreComercial" 		=> "Nombre Comercial",
					"Tipo" 					=> "Tipo Contribuyente",
					"Inscripcion" 			=> "Fecha de Inscripci&oacute;n",
					"Estado" 				=> "Estado del Contribuyente",
					"Direccion" 			=> "Direcci&oacute;n del Domicilio Fiscal",
					"SistemaEmision" 		=> "Sistema de Emisi&oacute;n de Comprobante",
					"ActividadExterior"		=> "Actividad de Comercio Exterior",
					"SistemaContabilidad" 	=> "Sistema de Contabilidad",
					"Oficio" 				=> "Profesi&oacute;n u Oficio",
					"ActividadEconomica" 	=> "Actividad\(es\) Econ&oacute;mica\(s\)",
					"EmisionElectronica" 	=> "Emisor electr&oacute;nico desde",
					"PLE" 					=> "Afiliado al PLE desde"
				);
				foreach($busca as $i=>$v)
				{
					$patron='/<td class="bgn"[ ]*colspan=1[ ]*>'.$v.':[ ]*<\/td>\r\n[\t]*[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
					$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
					if(isset($matches[0]))
					{
						$rtn[$i] = trim(utf8_encode( preg_replace( "[\s+]"," ", ($matches[0][1]) ) ) );
					}
				}
			}
			if( count($rtn) > 2 )
			{
				return $rtn;
			}
			return false;
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
				/*if ( strlen($valor) == 8 ) // DNI
				{
					$suma = 0;
					for ($i=0; $i<strlen($valor)-1;$i++)
					{
						$digito = $valor[$i];
						if ( $i==0 )
						{
							$suma += ($digito*3);
							echo $digito." x 3 = ".($digito*3)."\n";
						}
						else if ( $i==1 )
						{
							$suma += ($digito*2);
							echo $digito." x 2 = ".($digito*2)."\n";
						}
						else
						{
							$suma += ($digito*(strlen($valor)-$i+1));
							echo $digito." x ".(strlen($valor)-$i+1)." = ".($digito*(strlen($valor)-$i+1))."\n";
						}
					}
					echo "suma=".$suma."\n";
					$resto = $suma % 11;
					echo "modulo=".$resto."\n";
					if ( $resto == 1)
					{
						$resto = 11;
					}
					if ( $resto + ( $valor[strlen($valor)-1] ) == 11 )
					{
						return true;
					}
				}
				else */
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
		function search( $ruc_dni, $inJSON = false )
		{
			if( strlen(trim($ruc_dni))==8 )
			{
				$ruc_dni = $this->dnitoruc($ruc_dni);
			}
			if( strlen($ruc_dni)==11 && $this->valid($ruc_dni) )
			{
				$info = $this->getDataRUC($ruc_dni);
				if( $info!=false )
				{
					$rtn = array(
						"success"=>true,
						"result"=>$info
					);
				}
				else
				{
					$rtn = array(
						"success"=>false,
						"msg"=>"No se ha encontrado resultados."
					);
				}
				return ($inJSON==true)?json_encode($rtn,JSON_PRETTY_PRINT):$rtn;
			}

			$rtn = array(
				"success"=>false,
				"msg"=>"Nro de RUC o DNI no valido."
			);
			return ($inJSON==true)?json_encode($rtn,JSON_PRETTY_PRINT):$rtn;
		}
	}
