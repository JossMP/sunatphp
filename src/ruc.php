<?php

namespace jossmp\sunat;

class ruc
{
	const URL_CONSULT_MORE  = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";
	const URL_CONSULT  = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsmulruc/jrmS00Alias";
	const URL_FILE_ZIP = "https://www.sunat.gob.pe/cl-at-framework-unloadfile/descargaArchivoAlias";

	const UNZIP_FORMAT = 'Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen';

	var $curl = NULL;

	var $_deuda            = false; // deuda coactiva
	var $_legal            = false;
	var $_trabs            = false;
	var $_establecimientos = false;

	var $path_json      = NULL;
	var $refresh        = FALSE;
	var $check_local    = FALSE;
	var $local_dir_json = NULL;

	var $company = NULL;

	private $user_captcha   = FALSE;

	function __construct($config = array())
	{
		//$this->curl = new \jossmp\navigate\Curl();
		$this->curl = (new \jossmp\navigate\RequestCurl())->getCurl();

		$this->_trabs            = (isset($config["cantidad_trabajadores"])) ? $config["cantidad_trabajadores"] : true;
		$this->_establecimientos = (isset($config["establecimientos"])) ? $config["establecimientos"] : true;
		$this->_legal            = (isset($config["representantes_legales"])) ? $config["representantes_legales"] : true;
		$this->_deuda            = (isset($config["deuda"])) ? $config["deuda"] : true;

		$this->curl->setReferer(self::URL_CONSULT);
		$this->curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		$this->curl->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.146 Safari/537.36");
	}

	/* config */
	public function set_proxy($host = NULL, $port = NULL, $user = NULL, $pass = NULL, $type = CURLPROXY_HTTP)
	{
		if ($host !== NULL && $port !== NULL) {
			$this->curl->setProxy($host, $port, $user, $pass);
			$this->curl->setProxyType($type);
		}
	}

	public function set_cookie($cookie_file = NULL)
	{
		$file = sys_get_temp_dir() . '/cookie.txt';
		if ($cookie_file != NULL) {
			$path = dirname($cookie_file);
			if (is_dir($path))
				$file 	= $cookie_file;
		}
		$this->curl->setCookieFile($file);
		$this->curl->setCookieJar($file);
	}

	public function set_directory_json($path)
	{
		$this->check_local = TRUE;
		$this->local_dir_json = rtrim($path, '/');
	}

	public function save_local_json($ruc, $json)
	{
		if ($this->check_local == TRUE && is_dir($this->local_dir_json)) {
			$path_json = $this->local_dir_json . "/" . $ruc . ".json";
			file_put_contents($path_json, $json);
		}
	}

	public function get_local_json($ruc)
	{
		if ($this->check_local == TRUE) {
			$path_json = $this->local_dir_json . "/" . $ruc . ".json";
			if (file_exists($path_json)) {
				$json = file_get_contents($path_json);
				$obj = json_decode($json);
				$response = new \jossmp\response\obj($obj);
				return $response;
			} else {
				$response = new \jossmp\response\obj(array(
					'success' => false,
					'message'  => 'Archivo local no disponible'
				));
				return $response;
			}
		}

		$response = new \jossmp\response\obj(array(
			'success' => false,
			'message'  => 'Directorio local no definido'
		));
		return $response;
	}

	public function refresh($flag = FALSE)
	{
		$this->refresh = ($flag === TRUE) ? TRUE : FALSE;
	}

	function require_deuda($flag = false)
	{
		$this->_deuda = $flag;
	}
	function require_legal($flag = false)
	{
		$this->_legal  = $flag;
	}
	function require_trabs($flag = false)
	{
		$this->_trabs  = $flag;
	}
	function require_establecimientos($flag = false)
	{
		$this->_establecimientos  = $flag;
	}
	/* ---------------------------------------------------- */
	/* ----------------- Inicio de codigo ----------------- */
	/* ---------------------------------------------------- */

	/**
	 * decompressXmlFile
	 *
	 * @param mixed $zipContent
	 * @return string
	 */
	public function decompressXmlFile($zipContent)
	{
		$head = unpack(self::UNZIP_FORMAT, substr($zipContent, 0, 30));

		return (gzinflate(substr($zipContent, 30 + $head['namelen'] + $head['exlen'])));
	}

	public function get_link($ruc)
	{
		$url = self::URL_CONSULT . "?accion=consManual&txtRuc&selRuc=" . $ruc;

		$response = $this->curl->get($url);
		if ($response != "" && $this->curl->getHttpStatusCode() == 200) {
			$patron = '/data0_num_id=([\d]+)"/';
			$output = preg_match_all($patron, $response, $matches, PREG_SET_ORDER);
			if (isset($matches[0])) {
				$link_zip = 'https://www.sunat.gob.pe/cl-at-framework-unloadfile/descargaArchivoAlias?data0_num_id=' . $matches[0][1];
				return new \jossmp\response\obj(array(
					'success' => true,
					'result' => array('zip' => $link_zip),
				));
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'No se encontraron resultados',
			));
		}
		return new \jossmp\response\obj(array(
			'success' => false,
			'message' => 'Error de coneccion a SUNAT',
		));
	}

	public function get_data($ruc)
	{
		$response = $this->get_link($ruc);
		if ($response->success == true) {
			$url = trim($response->result->zip);

			$zipContent = $this->curl->get($url);

			$headers = $this->curl->getResponseHeaders();
			$content_type    = trim($headers['content-type']);
			if ($zipContent != "" && $this->curl->getHttpStatusCode() == 200 && $content_type == 'application/zip') {
				$csv = $this->decompressXmlFile($zipContent);

				$this->company = new \jossmp\sunat\model\company();
				$this->company->set_ruc($ruc);

				$response = $this->process($csv);

				return $response;
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'SUNAT no responde',
			));
		}
		return $response;
	}

	public function consulta($ruc)
	{
		if ((strlen($ruc) != 8 && strlen($ruc) != 11) || !is_numeric($ruc)) {
			$response = new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'Formato RUC/DNI no validos.'
			));
			return $response;
		}

		if (strlen($ruc) == 11 && is_numeric($ruc) && !$this->valid($ruc)) {
			$response = new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'numero de RUC no valido'
			));
			return $response;
		}

		if (strlen($ruc) == 8 && is_numeric($ruc)) {
			$ruc = $this->dnitoruc($ruc);
		}

		$local = $this->get_local_json($ruc);
		if ($local->success == true && $this->refresh == false) {
			return $local;
		}

		$response = $this->get_consulta($ruc);
		if ($response->success == true) {
			if ($local->success == true) {
				$local->result->load_data($response->result);
				$this->save_local_json($ruc, $local->json());
				return $local;
			}
			$this->save_local_json($ruc, $response->json());
			return $response;
		} else if ($local->success == true) {
			return $local;
		}
		return $response;
	}

	public function get_consulta($ruc)
	{
		$response = $this->get_data($ruc);
		if ($response->success == true) {
			$this->company->set_representantes_legales(array());
			if ($this->_legal) {
				$legal = $this->get_representante_legal($ruc);
				if ($legal->success == true) {
					$this->company->set_representantes_legales($legal->result);
				}
			}

			$this->company->set_deuda_coactiva(array());
			if ($this->_deuda) {
				$deuda = $this->get_deuda_coactiva($ruc);
				if ($deuda->success == true) {
					$this->company->set_deuda_coactiva($deuda->result);
				}
			}

			$this->company->set_cantidad_trabajadores(array());
			if ($this->_trabs) {
				$trabs = $this->get_num_trabajadores($ruc);
				if ($trabs->success == true) {
					$this->company->set_cantidad_trabajadores($trabs->result);
				}
			}

			$this->company->set_establecimientos(array());
			if ($this->_establecimientos) {
				$establecimientos = $this->get_establecimiento($ruc);
				if ($establecimientos->success == true) {
					$this->company->set_establecimientos($establecimientos->result);
				}
			}

			return new \jossmp\response\obj([
				'success' => true,
				'result' => $this->company,
			]);
		}
		return $response;
	}

	private function process($response)
	{
		$line = explode("\n", $response);
		if (isset($line[1]) && trim($line[1]) != '') {

			$data = explode("|", $line[1]);
			if (count($data) >= 25) {
				$this->company->set_ruc(trim($data[0], "- \t\n\r\0\x0B"));
				$this->company->set_razon_social(trim($data[1], "- \t\n\r\0\x0B"));
				$this->company->set_tipo(trim($data[2], "- \t\n\r\0\x0B"));
				$this->company->set_oficio(trim($data[3], "- \t\n\r\0\x0B"));
				$this->company->set_nombre_comercial(trim($data[4], "- \t\n\r\0\x0B"));
				$this->company->set_condicion(trim($data[5], "- \t\n\r\0\x0B"));
				$this->company->set_estado(trim($data[6], "- \t\n\r\0\x0B"));
				$this->company->set_fecha_inscripcion(trim($data[7], "- \t\n\r\0\x0B"));
				$this->company->set_inicio_actividades(trim($data[8], "- \t\n\r\0\x0B"));
				$this->company->set_departamento(trim($data[9], "- \t\n\r\0\x0B"));
				$this->company->set_provincia(trim($data[10], "- \t\n\r\0\x0B"));
				$this->company->set_distrito(trim($data[11], "- \t\n\r\0\x0B"));

				$this->company->set_direccion(trim($data[12], "- \t\n\r\0\x0B"));
				$this->company->set_telefono(trim($data[13], "- \t\n\r\0\x0B"));
				$this->company->set_fax(trim($data[14], "- \t\n\r\0\x0B"));
				$this->company->set_actividad_exterior(trim($data[15], "- \t\n\r\0\x0B"));
				// Actividad economica
				$ae = [];

				$ae[] = [
					"tipo" => 'Principal',
					"cod"  => '',
					"desc" => trim($data[16], "- \t\n\r\0\x0B")
				];

				if (trim($data[17], "- \t\n\r\0\x0B") != '' && trim($data[17], "- \t\n\r\0\x0B") != '-') {
					$ae[] = [
						"tipo" => 'Secundario',
						"cod"  => '',
						"desc" => trim($data[17], "- \t\n\r\0\x0B")
					];
				}
				if (trim($data[18], "- \t\n\r\0\x0B") != '' && trim($data[18], "- \t\n\r\0\x0B") != '-') {
					$ae[] = [
						"tipo" => 'Secundario',
						"cod"  => '',
						"desc" => trim($data[18], "- \t\n\r\0\x0B")
					];
				}
				$this->company->set_actividad_economica($ae);

				$this->company->set_afectado_rus(trim($data[19], "- \t\n\r\0\x0B"));
				$this->company->set_buen_contribuyente(trim($data[20], "- \t\n\r\0\x0B"));
				$this->company->set_agente_retencion(trim($data[21], "- \t\n\r\0\x0B"));
				$this->company->set_agente_perc_vta_int(trim($data[22], "- \t\n\r\0\x0B"));
				$this->company->set_agente_perc_com_liq(trim($data[23], "- \t\n\r\0\x0B"));

				return new \jossmp\response\obj([
					'success' => true,
					'type'    => 'count'
				]);
			}
		}
		return new \jossmp\response\obj(array(
			'success' => false,
			'message' => 'Imposible procesar la respuesta',
		));
	}

	/*** *** *** *** *** *** *** *** ***/
	/*** ***  Datos Adicionales  *** ***/
	/*** *** *** *** *** *** *** *** ***/
	public function get_num_trabajadores($num_doc)
	{
		$valid = $this->valida_doc($num_doc);
		if ($valid->success == true) {
			$data = array(
				"accion"   => "getCantTrab",
				"contexto" => "ti-it",
				"modo"     => "1",
				"nroRuc"   => trim($valid->result->ruc),
				"desRuc" => 'FOREST+%26+GROUP+CONTRACTORS+S.A.C.',
				//"desRuc"   => "",
			);

			$rtn = $this->curl->post(self::URL_CONSULT_MORE, $data);

			if ($rtn != "" && $this->curl->getHttpStatusCode() == 200) {

				libxml_use_internal_errors(true);

				$doc = new \DOMDocument('1.0', 'UTF-8');
				$doc->strictErrorChecking = false;

				@$doc->loadHTML($rtn);

				libxml_use_internal_errors(false);

				$xml = simplexml_import_dom($doc);
				$data = $xml->xpath("//div[@class='list-group']/div/div/div/table/tbody/tr");

				if (!empty($data)) {
					$cantidad_trabajadores = array();
					foreach ($data as $tr) {
						$periodo        = trim($tr->td[0]);
						$_periodo       = explode('-', $periodo);
						$anio           = ($_periodo[0]) ? $_periodo[0] : NULL;
						$mes            = ($_periodo[1]) ? $_periodo[1] : NULL;
						$total_trab     = trim($tr->td[1]);
						$pensionista    = trim($tr->td[2]);
						$prestador_serv = trim($tr->td[3]);

						$cantidad_trabajadores[] = array(
							"periodo" 				=> $periodo,
							"anio" 					=> $anio,
							"mes" 					=> $mes,
							"total_trabajadores" 	=> $total_trab,
							"pensionista" 			=> $pensionista,
							"prestador_servicio" 	=> $prestador_serv,
						);
					}
					if (count($cantidad_trabajadores) > 0) {
						return new \jossmp\response\obj(array(
							'success' => true,
							'result' => $cantidad_trabajadores,
						));
					}
				}
				return new \jossmp\response\obj(array(
					'success' => false,
					'message' => 'no se encontraron trabajadores registrados',
				));
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'No se pudo conectar a sunat.',
			));
		}
		return $valid;
	}

	public function get_establecimiento($num_doc)
	{
		$valid = $this->valida_doc($num_doc);
		if ($valid->success == true) {
			$data = array(
				"accion"        => "getLocAnex",
				"contexto"      => "ti-it",
				"modo"          => "1",
				'nroRuc'        => $valid->result->ruc,
				"desRuc"        => "",
			);
			$rtn = $this->curl->post(self::URL_CONSULT_MORE, $data);
			if ($rtn != "" && $this->curl->getHttpStatusCode() == 200) {

				libxml_use_internal_errors(true);

				$doc = new \DOMDocument('1.0', 'UTF-8');
				$doc->strictErrorChecking = false;

				@$doc->loadHTML($rtn);

				libxml_use_internal_errors(false);

				$xml = simplexml_import_dom($doc);
				$data = $xml->xpath("//div[@class='list-group-item']/table/tbody/tr");

				if (!empty($data)) {
					$establecimientos = array();
					foreach ($data as $tr) {
						$cod = trim($tr->td[0]);
						$cod_tipo = '--';
						$desc = trim($tr->td[1]);
						$ac = explode('.', $desc);
						if (count($ac) > 1) {
							$cod_tipo = $ac[0];
							unset($ac[0]);
							$desc = implode('.', $ac);
						}
						$val_dir = $this->fix_direccion(trim(preg_replace('/[ ]*-/', ' -', $tr->td[2])));

						$act_eco = trim($tr->td[3]);

						$establecimientos[] = array(
							'codigo'             => $cod,
							'cod_tipo'           => $cod_tipo,
							'tipo'               => $desc,
							'direccion'          => $val_dir['direccion'],
							'departamento'       => $val_dir['departamento'],
							'provincia'          => $val_dir['provincia'],
							'distrito'           => $val_dir['distrito'],
							'activida_economica' => $act_eco,
						);
					}
					if (count($establecimientos) > 0) {
						return new \jossmp\response\obj(array(
							'success' => true,
							'result' => $establecimientos,
						));
					}
				}

				return new \jossmp\response\obj(array(
					'success' => false,
					'message' => 'no se encontraron establecimientos registrados',
				));
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'No se pudo conectar a sunat.',
			));
		}
		return $valid;
	}

	public function get_representante_legal($num_doc)
	{
		$valid = $this->valida_doc($num_doc);
		if ($valid->success == true) {
			$data = array(
				"accion"   => "getRepLeg",
				"contexto" => "ti-it",
				"modo"     => "1",
				"desRuc"   => "",
				"nroRuc"   => $valid->result->ruc,
			);
			$rtn = $this->curl->post(self::URL_CONSULT_MORE, $data);
			if ($rtn != "" && $this->curl->getHttpStatusCode() == 200) {

				libxml_use_internal_errors(true);

				$doc = new \DOMDocument('1.0', 'UTF-8');
				$doc->strictErrorChecking = false;

				@$doc->loadHTML($rtn);

				libxml_use_internal_errors(false);

				$xml = simplexml_import_dom($doc);
				$data = $xml->xpath("//table[@class='table']/tbody/tr");

				if (!empty($data)) {
					$representantes_legales = array();
					foreach ($data as $tr) {
						$tipo_doc = trim($tr->td[0]);
						$num_doc  = trim($tr->td[1]);
						$nombre   = trim($tr->td[2]);
						$cargo    = trim($tr->td[3]);
						$desde    = \DateTime::createFromFormat("d/m/Y", trim($tr->td[4]));

						$representantes_legales[] = array(
							"tipodoc" 				=> $tipo_doc,
							"numdoc" 				=> $num_doc,
							"nombre" 				=> $nombre,
							"cargo" 				=> $cargo,
							"desde" 				=> $desde->format("Y-m-d"),
						);
					}
					if (count($representantes_legales) > 0) {
						return new \jossmp\response\obj(array(
							'success' => true,
							'result' => $representantes_legales,
						));
					}
				}
				return new \jossmp\response\obj(array(
					'success' => false,
					'message' => 'no se encontraron representantes legales',
				));
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'No se pudo conectar a sunat.',
			));
		}
		return $valid;
	}

	public function get_deuda_coactiva($num_doc)
	{
		$valid = $this->valida_doc($num_doc);
		if ($valid->success == true) {

			$data = array(
				"accion"   => "getInfoDC",
				"contexto" => "ti-it",
				"modo"     => "1",
				"nroRuc"   => $valid->result->ruc,
				"desRuc"   => "",
				"submit"   => "Deuda Coactiva",
			);

			$response = $this->curl->post(self::URL_CONSULT_MORE, $data);
			if ($response != "" && $this->curl->getHttpStatusCode() == 200) {

				libxml_use_internal_errors(true);

				$doc = new \DOMDocument('1.0', 'UTF-8');
				$doc->strictErrorChecking = false;

				@$doc->loadHTML($response);

				libxml_use_internal_errors(false);

				$xml = simplexml_import_dom($doc);
				$data = $xml->xpath("//table[@class='table']/tbody/tr");

				if (!empty($data)) {
					$deuda = array();
					foreach ($data as $tr) {
						$monto    = trim($tr->td[0]);
						$periodo  = trim($tr->td[1]);
						//$cobranza = trim($tr->td[2]);
						$cobranza = \DateTime::createFromFormat("d/m/Y", trim($tr->td[2]));
						$entidad  = trim($tr->td[3]);

						$deuda[] = array(
							"monto"        => $monto,
							"periodo"      => $periodo,
							"cobranza"     => $cobranza->format("Y-m-d"),
							"entidad"      => $entidad,
						);
					}
					if (count($deuda) > 0) {
						return new \jossmp\response\obj(array(
							'success' => true,
							'result' => $deuda,
						));
					}
				}
				return new \jossmp\response\obj(array(
					'success' => false,
					'message' => 'no se encontraron deuda coactiva',
				));
			}
			return new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'No se pudo conectar a sunat.'
			));
		}
		return $valid;
	}

	public function valida_doc($num_doc)
	{
		if ((strlen($num_doc) != 8 && strlen($num_doc) != 11) || !is_numeric($num_doc)) {
			$response = new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'Formato RUC/DNI no validos.'
			));
			return $response;
		}

		if (strlen($num_doc) == 11 && is_numeric($num_doc) && !$this->valid($num_doc)) {
			$response = new \jossmp\response\obj(array(
				'success' => false,
				'message' => 'RUC no valido'
			));
			return $response;
		}

		if (strlen($num_doc) == 8 && is_numeric($num_doc)) {
			$num_doc = $this->dnitoruc($num_doc);
		}
		return new \jossmp\response\obj(array(
			'success' => true,
			'result' => array('ruc' => $num_doc),
		));
	}


	public function dnitoruc($dni)
	{
		if ($dni != "" || strlen($dni) == 8) {
			$suma = 0;
			$hash = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
			$suma = 5; // 10[NRO_DNI]X (1*5)+(0*4)
			for ($i = 2; $i < 10; $i++) {
				$suma += ($dni[$i - 2] * $hash[$i]); //3,2,7,6,5,4,3,2
			}
			$entero = (int) ($suma / 11);

			$digito = 11 - ($suma - $entero * 11);

			if ($digito == 10) {
				$digito = 0;
			} else if ($digito == 11) {
				$digito = 1;
			}
			return "10" . $dni . $digito;
		}
		return false;
	}

	public function valid($valor) // Script SUNAT
	{
		$valor = trim($valor);
		if ($valor) {
			if (strlen($valor) == 11) // RUC
			{
				$suma = 0;
				$x = 6;
				for ($i = 0; $i < strlen($valor) - 1; $i++) {
					if ($i == 4) {
						$x = 8;
					}
					$digito = $valor[$i];
					$x--;
					if ($i == 0) {
						$suma += ($digito * $x);
					} else {
						$suma += ($digito * $x);
					}
				}
				$resto = $suma % 11;
				$resto = 11 - $resto;
				if ($resto >= 10) {
					$resto = $resto - 10;
				}
				if ($resto == $valor[strlen($valor) - 1]) {
					return true;
				}
			}
		}
		return false;
	}

	function fix_direccion($str_direccion)
	{
		$items = explode('-', $str_direccion);
		$cant = count($items);

		if ($cant < 3) {
			$str_direccion = trim(preg_replace("[\s+]", ' ', $str_direccion));
			if (trim($str_direccion) === '-') {
				$str_direccion = '';
			}
			return [
				'direccion'    => $str_direccion,
				'departamento' => '',
				'provincia'    => '',
				'distrito'     => '',
			];
		}

		$pieces = explode(' ', trim($items[$cant - 3]));
		list($len, $value) = $this->fix_departamento(end($pieces));
		$departamento = $value;
		$provincia = $items[$cant - 2];
		$distrito = $items[$cant - 1];
		array_splice($pieces, -1 * $len);
		$direccion = join(' ', $pieces);
		if ($cant > 3) {
			array_splice($items, -3);
			$direccion = trim(join(' ', $items)) . ' ' . trim($direccion);
		}
		return [
			'direccion'    => trim($direccion),
			'departamento' => trim($departamento),
			'provincia'    => trim($provincia),
			'distrito'     => trim($distrito),
		];
	}

	private function fix_departamento($departamento)
	{
		$departamento = strtoupper($departamento);
		$words = 1;
		switch ($departamento) {
			case 'DIOS':
				$departamento = 'MADRE DE DIOS';
				$words = 3;
				break;
			case 'MARTIN':
				$departamento = 'SAN MARTIN';
				$words = 2;
				break;
			case 'LIBERTAD':
				$departamento = 'LA LIBERTAD';
				$words = 2;
				break;
		}
		return [$words, $departamento];
	}
}
