<?php
	class Sunat{
		var $cc;  //Class cUrl
		var $path;
		function __construct()
		{
			$this->path = dirname(__FILE__);
			$this->cc = new cURL(true,'http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias',$this->path.'/cookies.txt');
		}
		function ProcesaNumRand()
		{
			$url="http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
			$numRand = $this->cc->post($url);
			return $numRand;
		}
		
		function BuscaDatosSunat($rucdni)
		{
			$rucdni = trim($rucdni);
			if( strlen($rucdni) == 8 )
			{
				return $this->Search4DNI($rucdni);
			}
			else if( strlen($rucdni) == 11 )
			{
				return $this->Search4RUC($rucdni);
			}
			return false;
		}
		
		function Search4RUC($ruc)
		{
			$captcha = $this->ProcesaNumRand();
			$rtn = array();
			if($ruc != "" && $captcha!=false)
			{
				$data = array(
					"nroRuc" => $ruc,
					"accion" => "consPorRuc",
					"numRnd" => $captcha
				);
				
				$url = "http://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
				$Page = $this->cc->post($url,$data);
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
					$patron='/<td class="bgn" colspan=1[ ]*>'.$v.':[ ]*<\/td>\r\n[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
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
		function Search4DNI($dni)
		{
			$ruc = $this->GeneraRuc($dni);
			if($ruc!=false)
			{
				return $this->Search4RUC($ruc);
			}
			return false;
		}
		
		function GeneraRuc( $dni="" )
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
	}
?>
