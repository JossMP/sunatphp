<?php

namespace jossmp\sunat;

class tipo_cambio
{
	var $curl = NULL;
	function __construct($config = array())
	{
		$this->curl = (new \jossmp\navigate\RequestCurl())->getCurl();

		$this->curl->setConnectTimeout(10);

		$this->curl->setReferer("https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias");
		$this->curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		$this->curl->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.146 Safari/537.36");
		if (isset($config["proxy"])) {
			$host = (isset($config["proxy"]["host"])) ? $config["proxy"]["host"] : NULL;
			$port = (isset($config["proxy"]["port"])) ? $config["proxy"]["port"] : NULL;
			$type = (isset($config["proxy"]["type"])) ? $config["proxy"]["type"] : NULL;
			$user = (isset($config["proxy"]["user"])) ? $config["proxy"]["user"] : NULL;
			$pass = (isset($config["proxy"]["pass"])) ? $config["proxy"]["user"] : NULL;
			if ($host !== NULL && $port !== NULL && $type !== NULL) {
				$this->set_proxy($host, $port, $type, $user, $pass);
			}
		}
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

	function ultimo_tc()
	{
		$url = "https://www.sunat.gob.pe/a/txt/tipoCambio.txt";
		$response = $this->curl->get($url);
		if ($this->curl->getHttpStatusCode() == 200 && $response != "") {
			$arr = explode('|', trim($response));
			if (count($arr) >= 3) {
				$fecha  = \DateTime::createFromFormat("d/m/Y", trim($arr[0]));
				$result = [
					"fecha"  => trim($fecha->format("Y-m-d")),
					"compra" => trim($arr[1]),
					"venta"  => trim($arr[2]),
				];
				return new \jossmp\response\obj([
					'success' => true,
					'result'  => $result,
				]);
			}
			return new \jossmp\response\obj(array(
				'success' 	=> 	false,
				'message' 	=> 	'No se ha podido obtener resultado.'
			));
		}
		return new \jossmp\response\obj(array(
			'success' 	=> 	false,
			'message' 	=> 	'SUNAT no responde.'
		));
	}
	function consulta($mes = NULL, $anio = NULL)
	{
		$mes 	= ($mes == NULL) ? date("m") : $mes;
		$anio 	= ($anio == NULL) ? date("Y") : $anio;

		$url = 'https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias';

		return new \jossmp\response\obj(array(
			'success' 	=> 	false,
			'message' 	=> 	'OPCION NO DISPONIBLE...'
		));
	}
}
