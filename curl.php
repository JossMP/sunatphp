<?php
	class cURL
	{
		var $headers;
		var $user_agent;
		var $compression;
		var $cookie_file;
		var $proxy;
		var $referer;
		var $info;
		var $error;
		var $url = false;
		
		var $request_cookies = '';
		var $response_cookies = '';
		var $content = '';

		function getInfo()
		{
			return $this->info;
		}
		function cURL($cookies=TRUE,$referer='http://www.google.com',$cookie='cookies.txt',$compression='gzip,deflate',$proxy='')
		{
			$this->headers[0] = "Accept-Encoding: gzip, deflate, sdch";
			$this->headers[] = "Accept-Language: es-419,es;q=0.8";
			$this->headers[] = "User-Agent: Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36";
			$this->headers[] = "Content-Type: application/x-www-form-urlencoded";
			$this->headers[] = "DNT: 1";
			$this->headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
			$this->headers[] = "X-Requested-With: XMLHttpRequest";
			$this->headers[] = "Connection: keep-alive";

			$this->user_agent = 'Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36';
			$this->compression=$compression;
			$this->proxy=$proxy;
			$this->cookies=$cookies;
			$this->referer=$referer;
			if($this->cookies == TRUE)
				$this->cookie($cookie);
		}
		function cookie($cookie_file)
		{
			if (file_exists($cookie_file))
			{
				$this->cookie_file=$cookie_file;
			}
			else
			{
				//fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
				//fclose($this->cookie_file);
				file_put_contents($cookie_file,"");
				$this->cookie_file=$cookie_file;
			}
		}
		function post( $url, array $post = array(), array $options = array() )
		{
			$defaults = array(
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_REFERER => $this->referer,
				CURLOPT_USERAGENT => $this->user_agent,
				CURLOPT_COOKIEFILE => $this->cookie_file,
				CURLOPT_COOKIEJAR => $this->cookie_file,
				CURLOPT_URL => $url,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FORBID_REUSE => true,
				CURLOPT_TIMEOUT => 250,
				CURLOPT_ENCODING => $this->compression,
				CURLOPT_HTTPHEADER => $this->headers,
				//CURLINFO_HEADER_OUT => false,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($post)
			);
			$ch = curl_init();
			curl_setopt_array($ch, ($options + $defaults));
			if(!$result = curl_exec($ch))
			{
				curl_close($ch);
				return false;
			}
			$this->error 	= curl_getinfo($ch,CURLINFO_HTTP_CODE);
			$this->url 		= curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
			//$this->url 		= curl_getinfo($ch,CURLINFO_REDIRECT_URL);
			if($this->error < 400)
			{
				curl_close($ch);
				return $result;
			}
			curl_close($ch);
			return false;
		}
		
		function get( $url, array $options = array() )
		{
			$defaults = array(
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_REFERER => $this->referer,
				CURLOPT_USERAGENT => $this->user_agent,
				CURLOPT_COOKIEFILE => $this->cookie_file,
				CURLOPT_COOKIEJAR => $this->cookie_file,
				CURLOPT_URL => $url,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FORBID_REUSE => true,
				CURLOPT_TIMEOUT => 250,
				CURLOPT_ENCODING => $this->compression,
				CURLOPT_HTTPHEADER => $this->headers
			);
			$ch = curl_init();
			curl_setopt_array($ch, ($options + $defaults));
			if(!$result = curl_exec($ch))
			{
				curl_close($ch);
				return false;
			}
			$this->error 	= curl_getinfo($ch,CURLINFO_HTTP_CODE);
			$this->url 		= curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
			//$this->url 		= curl_getinfo($ch,CURLINFO_REDIRECT_URL);
			if($this->error < 400)
			{
				curl_close($ch);
				return $result;
			}
			curl_close($ch);
			return false;
		}
		
		function referer($url = "https://www,google.com")
		{
			$this->referer=$url;
		}
		
		function error($error)
		{
			echo "<center><div style='width:500px;border: 3px solid #FFEEFF; padding: 3px; background-color: #FFDDFF;font-family: verdana; font-size: 10px'><b>cURL Error</b><br>$error</div></center>";
			die;
		}
		
		// ////////////////////////////////////////////////

		function set_cookies_string($cookies)
		{
			$this->response_cookies = 'Cookie: ' . $cookies . "\r\n";
		}

		private function get_cookies( $http_response_header = array() )
		{
			$cookies = "";
			if( is_array($http_response_header) )
			foreach($http_response_header as $s)
			{
				$patron = '/Set-Cookie: (.*)/';
				$output = preg_match_all($patron, $s, $matches, PREG_SET_ORDER);
				if(isset($matches[0]))
				{
					$cookies = trim($matches[0][1]);
				}
			}
			
			if($this->response_cookies != 'Cookie: ' . $cookies . "\r\n" && $cookies != "")
			{
				$this->response_cookies = 'Cookie: ' . $cookies . "\r\n";
			}
		}
		
		function get2( $url, array $options = array() )
		{
			$defaults = array(
				'method' 	=> 'GET',
				'header' 	=> join("\r\n", $this->headers) . "\r\n" . $this->response_cookies,
				'timeout' 	=> 600
			);
			$options += $defaults;
			$opts = array(
				'http' => $options
			);
			
			$context = stream_context_create($opts);
			$this->content = file_get_contents($url, false, $context);
			$this->get_cookies($http_response_header);
			
			return $this->content;
		}

		function post2( $url, $post_data, array $options = array() )
		{
			$post_content = array();
			foreach ($post_data as $key => $value)
			{
				$post_content[] = $key .'='.$value;
			}

			$defaults = array(
					'method' => 'POST',
					'header' => join("\r\n", $this->headers) . "\r\n" . $this->response_cookies,
					'content' => join('&', $post_content),
					'timeout' => 600
			);
			
			$options += $defaults;
			$opts = array(
				'http' => $options
			);
			
			$context = stream_context_create($opts);
			$this->content = file_get_contents($url, false, $context);
			$this->get_cookies($http_response_header);
			
			return $this->content;
		}
	}
?>
