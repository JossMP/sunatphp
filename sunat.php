<?php
	include("curl.php");
	class Sunat{
		var $cc;  //Class cUrl
		var $path;
		function __construct()
		{
			$this->path = dirname(__FILE__);
			$this->cc = new cURL(true,'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias',$this->path.'/cookies.txt');
		}
		function ProcesaNumRand()
		{
			$data = array(
				"accion"=>"random"
			);
			$url="http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha";
			$numRand = $this->cc->post($url,$data);
			return $numRand;
		}
		
		function BuscaDatosSunat($ruc)
		{
			$captcha = $this->ProcesaNumRand();
			$rtn = array();
			if($ruc != "" && $captcha!=false)
			{
				$data = array(
					//"accion" => "consPorTipdoc",
					//"tipdoc" =>1, //DNI
					"nroRuc" => $ruc,
					"accion" => "consPorRuc",
					"numRnd" => $captcha
				);
				
				$url = "http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
				$Page = $this->cc->post($url,$data);
				$patron='/<input type="hidden" name="desRuc" value="(.*)">/';
				$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
				if(isset($matches[0]))
				{
					$RS = str_replace('"','', ($matches[0][1]));
					$rtn = array("RUC"=>$ruc,"RazonSocial"=>trim($RS));
				}
				$busca=array(
					"Tipo" 					=> "Tipo Contribuyente",
					"Inscripcion" 			=> "Fecha de Inscripci&oacute;n",
					"Estado" 				=> "Estado del Contribuyente",
					"Direccion" 			=> "Direcci&oacute;n del Domicilio Fiscal",
					"SistemaEmision" 		=> "Sistema de Emisi&oacute;n de Comprobante",
					"ActividadExterior"		=> "Actividad de Comercio Exterior",
					"SistemaContabilidad" 	=> "Sistema de Contabilidad",
					"Oficio" 				=> "Profesi&oacute;n u Oficio",
					//"ActividadEconomica" 	=> "Actividad\(es\) Econ&oacute;mica\(s\)",
					"EmisionElectronica" 	=> "Emisor electr&oacute;nico desde",
					"PLE" 					=> "Afiliado al PLE desde"
				);
				$rtn = array();
				foreach($busca as $i=>$v)
				{
					$patron='/<td class="bgn" colspan=1>'.$v.':[ ]*<\/td>\r\n[ ]+<td class="bg" colspan=[1|3]+>(.*)<\/td>/';
					$output = preg_match_all($patron, $Page, $matches, PREG_SET_ORDER);
					if(isset($matches[0]))
					{
						$rtn[$i] = trim(preg_replace( "[\s+]"," ", ($matches[0][1]) ) );
					}
				}
			}
			return $rtn;
		}
	}
	
	$test = new Sunat();
	echo json_encode( $test->BuscaDatosSunat("10442747950"), JSON_PRETTY_PRINT );
?>
