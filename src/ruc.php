<?php

namespace jossmp\sunat;

class ruc
{
	const URL_NUM_RAND = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=random";
	const URL_CAPTCHA  = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image";
	const URL_CONSULT  = "https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias";

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
		$this->curl->setReferer("https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias");
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

	/* ---------------------------------------------------- */
	/* ----------------- Inicio de codigo ----------------- */
	/* ---------------------------------------------------- */
	public function get_num_rand()
	{
		$numRand = $this->curl->get(self::URL_NUM_RAND);
		if ($this->curl->getHttpStatusCode() == 200 && $numRand != "")
			return $numRand;
		return false;
	}

	public function consulta_for_num_rand($ruc)
	{
		$numRand = $this->get_num_rand();
		if ($numRand !== false) {
			$data = array(
				'modo'      => '1',
				'rbtnTipo'  => '1',
				'contexto'  => 'ti-it',
				'tQuery'    => 'on',
				'accion'    => 'consPorRuc',
				'actReturn' => '1',
				'nroRuc'    => $ruc,
				'numRnd'    => $numRand
			);

			$response = $this->curl->post(self::URL_CONSULT, $data);
			if ($this->curl->getHttpStatusCode() == 200 && $response != "") {
				if (mb_detect_encoding($response, "UTF-8, ISO-8859-1") != "UTF-8") {
					//return  iconv("ISO-8859-1", "utf-8", $response);
					return new \jossmp\response\obj([
						'success' => true,
						'result'  => utf8_encode($response),
					]);
				}
				return new \jossmp\response\obj([
					'success' => true,
					'result'  => $response,
				]);
			}
			return new \jossmp\response\obj([
				'success' => false,
				'type'    => 'connect',
				'message' => 'No se ha podido obtener el captcha',
			]);
		}
		return new \jossmp\response\obj([
			'success' => false,
			'type'    => 'captcha',
			'message' => 'No se ha podido obtener el captcha',
		]);
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
			return $response;
		} else if ($local->success == true) {
			return $local;
		}
		return $response;
	}

	public function get_consulta($ruc)
	{
		$response = $this->consulta_for_num_rand($ruc);
		if ($response->success == true) {
			$this->company = new \jossmp\sunat\model\company();
			$this->company->set_ruc($ruc);
			$process = $this->process($response->result);
			if ($process->success == true) {
				if ($this->company->get_razon_social() != NULL) {

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

					$response = new \jossmp\response\obj(array(
						'success' 	=> 	true,
						'result' 	=> 	$this->company
					));

					$this->save_local_json($ruc, $response->json());
					return $response;
				}
			} else {
				return $process;
			}
		}

		return new \jossmp\response\obj([
			'success' => false,
			'type'    => 'connect',
			'message' => 'Sunat no responde.',
		]);
	}

	private function process($response)
	{
		libxml_use_internal_errors(true);

		$doc = new \DOMDocument('1.0', 'UTF-8');
		$doc->strictErrorChecking = false;

		@$doc->loadHTML($response);

		libxml_use_internal_errors(false);

		$xml = simplexml_import_dom($doc);
		$data = $xml->xpath("//div[@class='list-group']/div/div[@class='row']");
		if (count($data) <= 0) {
			return new \jossmp\response\obj([
				'success' => false,
				'type'    => 'empty',
				'message' => 'no se encontraron datos',
			]);
		}

		$fail = 0;

		foreach ($data as $obj) {
			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Número de RUC:') {
				if (isset($obj->div[1]->h4) && !empty(trim($obj->div[1]->h4))) {
					$list = explode('-', trim($obj->div[1]->h4));
					if (count($list) >= 2) {
						$this->company->set_ruc(trim($list[0]));
						$this->company->set_razon_social(trim($list[1]));
					} else {
						$fail++;
					}
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Tipo Contribuyente:') {
				if (isset($obj->div[1]->p) && !empty(trim($obj->div[1]->p))) {
					$this->company->set_tipo(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Tipo de Documento:') {
				if (isset($obj->div[1]->p) && !empty(trim($obj->div[1]->p))) {
					$list = explode('-', trim($obj->div[1]->p));
					if ($list >= 2) {
						$this->company->set_contribuyente(trim($list[1]));
						$list2 = explode(' ', trim($list[0]));
						if ($list2 >= 2) {
							$this->company->set_contribuyente_tipo_doc(trim($list2[0]));
							$this->company->set_contribuyente_num_doc(trim($list2[1]));
						}
					} else {
						$fail++;
					}
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Nombre Comercial:') {
				if (isset($obj->div[1]->p) && !empty(trim($obj->div[1]->p))) {
					$this->company->set_nombre_comercial(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Fecha de Inscripción:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_fecha_inscripcion(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
				if (isset($obj->div[2]->h4) && trim($obj->div[2]->h4) == 'Fecha de Inicio de Actividades:') {
					if (!empty(trim($obj->div[3]->p))) {
						$this->company->set_inicio_actividades(trim($obj->div[3]->p));
					} else {
						$fail++;
					}
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Estado del Contribuyente:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_estado(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Condición del Contribuyente:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_condicion(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Domicilio Fiscal:') {
				if (!empty(trim($obj->div[1]->p))) {
					$val = $this->fix_direccion(trim($obj->div[1]->p));
					$this->company->set_direccion($val['direccion']);
					$this->company->set_departamento($val['departamento']);
					$this->company->set_provincia($val['provincia']);
					$this->company->set_distrito($val['distrito']);
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Sistema Emisión de Comprobante:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_sistema_emision(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
				if (isset($obj->div[2]->h4) && trim($obj->div[2]->h4) == 'Actividad Comercio Exterior:') {
					if (!empty(trim($obj->div[3]->p))) {
						$this->company->set_actividad_exterior(trim($obj->div[3]->p));
					} else {
						$fail++;
					}
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Sistema Contabilidiad:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_sistema_contabilidad(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Actividad(es) Económica(s):') {
				if (!empty($obj->div[1]->table->tbody->tr)) {
					$ae = [];
					foreach ($obj->div[1]->table->tbody->tr as $tr) {
						$td_ae = explode('-', trim($tr->td));
						if (count($td_ae) >= 3) {
							$ae[] = [
								'tipo' => trim($td_ae[0]),
								'cod'  => trim($td_ae[1]),
								'desc' => trim($td_ae[2]),
							];
						}
					}
					$this->company->set_actividad_economica($ae);
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Comprobantes de Pago c/aut. de impresión (F. 806 u 816):') {
				if (!empty($obj->div[1]->table->tbody->tr)) {
					$cp = [];
					foreach ($obj->div[1]->table->tbody->tr as $tr) {
						$cp[] = trim($tr->td);
					}
					$this->company->set_comprobante_impreso($cp);
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Sistema de Emisión Electrónica:') {
				if (!empty($obj->div[1]->table->tbody->tr)) {
					$cp = [];
					foreach ($obj->div[1]->table->tbody->tr as $tr) {
						$ce = trim($tr->td);
						$list = explode('DESDE', $ce);
						if (count($list) >= 2) {
							$desde    = \DateTime::createFromFormat("d/m/Y", trim(end($list)));
							unset($list[count($list) - 1]);
							$comprobante = implode("DESDE", $list);
							$cp[] = [
								'comprobante' => trim($comprobante),
								'inicio'      => $desde->format("Y-m-d"),
							];
						} else {
							$cp[] = [
								'comprobante' => $ce,
								'inicio'      => '-',
							];
						}
					}
					$this->company->set_comprobante_electronico($cp);
				} else {
					$fail++;
				}
			}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Emisor electrónico desde:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_emision_electronica(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}

			//if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Comprobantes Electrónicos:') {
			//	if (!empty(trim($obj->div[1]->p))) {
			//		$this->company->set_comprobante_electronico(trim($obj->div[1]->p));
			//	} else {
			//      $fail++;
			//	}
			//}

			if (isset($obj->div[0]->h4) && trim($obj->div[0]->h4) == 'Afiliado al PLE desde:') {
				if (!empty(trim($obj->div[1]->p))) {
					$this->company->set_ple(trim($obj->div[1]->p));
				} else {
					$fail++;
				}
			}
		}
		return new \jossmp\response\obj([
			'success' => true,
			'type'    => 'count',
			'message' => $fail . ' datos no encontrados',
		]);
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
				"nroRuc"   => $valid->result->ruc,
				"desRuc"   => "",
			);

			$rtn = $this->curl->post(self::URL_CONSULT, $data);

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
				'tamanioPagina' => 500,
			);
			$rtn = $this->curl->post(self::URL_CONSULT, $data);
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
			$rtn = $this->curl->post(self::URL_CONSULT, $data);
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

			$response = $this->curl->post(self::URL_CONSULT, $data);
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
