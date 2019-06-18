<?php
	namespace CURL;
	class cURL
	{
		protected $_useragent = 'Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:53.0) Gecko/20100101 Firefox/53.0';
		protected $_url;
		protected $_followlocation;
		protected $_timeout;
		protected $_httpheaderData = array();
		protected $_httpheader = array('Expect:');
		protected $_maxRedirects;
		protected $_post = false;
		protected $_postFields;
		protected $_referer = "https://www.google.com/";

		protected $_session;
		protected $_webpage;
		protected $_includeHeader;
		protected $_noBody;
		protected $_status;
		protected $_custom_request 	= null;
		
		protected $_binary = false;
		protected $_binary_fields;

		protected $_cookie = false;
		protected $_cookie_file_path;
		
		protected $_proxy 		= false;
		protected $_proxy_host 	= '';
		protected $_proxy_port 	= '';
		protected $_proxy_type 	= CURLPROXY_HTTP;
		protected $_proxy_user 	= null;
		protected $_proxy_pass 	= null;
		
		protected $_auth 		= false;
		protected $_auth_name 	= null;
		protected $_auth_pass 	= null;
		protected $_auth_type 	= null;

		public function __construct( $followlocation = true, $timeOut = 30, $maxRedirecs = 4, $binary = false, $includeHeader = false, $noBody = false )
		{
			$this->_followlocation = $followlocation;
			$this->_timeout = $timeOut;
			$this->_maxRedirects = $maxRedirecs;
			$this->_noBody = $noBody;
			$this->_includeHeader = $includeHeader;
			$this->_binary = $binary;

			$this->_cookie_file_path = __DIR__ .'/cookie.txt';
			$this->s = curl_init();
		}
		
		public function __destruct()
		{
			curl_close( $this->s );
		}
		
		/************************************/
		/* FUNCTIONS PROXY */
		/************************************/
		public function useProxy( $use )
		{
			$this->_proxy = false;
			if($use == true) $this->_proxy = true;
		}
		public function setProxyHost( $host )
		{
			$this->_proxy_host = $host;
		}
		public function setProxyPort( $port )
		{
			$this->_proxy_port = $port;
		}
		public function setProxyType( $type = CURLPROXY_HTTP )
		{
			// CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A o CURLPROXY_SOCKS5_HOSTNAME
			$this->_proxy_type = $type;
		}
		public function setProxyUser( $proxy_user = null )
		{
			$this->_proxy_user = $proxy_user;
		}
		public function setProxyPass( $proxy_pass = null )
		{
			$this->_proxy_pass = $proxy_pass;
		}
		/************************************/
		/* FUNCTIONS AUTH */
		/************************************/
		public function useAuth( $use )
		{
			$this->_auth = false;
			if($use == true) $this->_auth = true;
		}

		public function setAuthName( $name )
		{
			$this->_auth_name = $name;
		}
		public function setAuthPass( $pass )
		{
			$this->_auth_pass = $pass;
		}
		public function setAuthType( $type = CURLAUTH_ANY )
		{
			$this->_auth_type = $type;
		}
		
		/************************************/
		/* FUNCTIONS COOKIE */
		/************************************/
		public function useCookie( $use = false )
		{
			$this->_cookie = false;
			if($use == true) $this->_cookie = true;
		}
		public function setCookiFileLocation( $path )
		{
			$this->_cookie_file_path = $path;
			if ( !file_exists($this->_cookie_file_path) )
			{
				file_put_contents($this->_cookie_file_path,"");
			}
		}
		
		/************************************/
		/* FUNCTIONS CURL */
		/************************************/
		public function setReferer( $referer )
		{
			$this->_referer = $referer;
		}
		public function setHttpHeader( $httpheader=array() )
		{
			$this->_httpheader = array();
			foreach( $httpheader as $i=>$v )
			{
				$this->_httpheaderData[$i]=$v;
			}
			foreach( $this->_httpheaderData as $i=>$v )
			{
				$this->_httpheader[]=$i.":".$v;
			}
		}
		public function setPost( $postFields = array() )
		{
			$this->_binary = false;
			$this->_post = true;
			$this->_postFields = http_build_query($postFields);
		}

		public function setBinary( $postBinaryFields = "" )
		{
			$this->_post = false;
			$this->_binary = true;
			$this->_binary_fields = $postBinaryFields;
		}
		public function setFields( $Fields = "" )
		{
			$this->_post = true;
			$this->_binary = false;
			$this->_binary_fields = $Fields;
		}

		public function setUserAgent( $userAgent )
		{
			$this->_useragent = $userAgent;
		}
		
		public function setCustomRequest( $custom_request = "POST" )
		{
			// GET | HEAD | POST | CONNECT | DELETE | UPDATE | ....
			$this->_custom_request = $custom_request;
		}
		
		public function createCurl( $url = null )
		{
			if($url != null)
			{
				$this->_url = $url;
			}

			//$this->s = curl_init();
			curl_setopt($this->s, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->s, CURLOPT_URL, $this->_url);
			curl_setopt($this->s, CURLOPT_HTTPHEADER, $this->_httpheader);
			curl_setopt($this->s, CURLOPT_TIMEOUT, $this->_timeout);
			curl_setopt($this->s, CURLOPT_MAXREDIRS, $this->_maxRedirects);
			curl_setopt($this->s, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->s, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
			curl_setopt($this->s, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			
			if( $this->_cookie == true )
			{
				curl_setopt($this->s, CURLOPT_COOKIEJAR,$this->_cookie_file_path);
				curl_setopt($this->s, CURLOPT_COOKIEFILE,$this->_cookie_file_path);
			}

			if($this->_proxy == true)
			{
				if( $this->_proxy_host != '' && $this->_proxy_port != '' )
				{
					curl_setopt($this->s, CURLOPT_HTTPPROXYTUNNEL, 0);
					curl_setopt($this->s, CURLOPT_PROXY, $this->_proxy_host.':'.$this->_proxy_port);
					curl_setopt($this->s, CURLOPT_PROXYTYPE, $this->_proxy_type);
					if( $this->_proxy_user!=null && $this->_proxy_pass!=null )
					{
						curl_setopt($this->s, CURLOPT_PROXYUSERPWD, $this->_proxy_user.':'.$this->_proxy_pass);
					}
				}
			}
			
			if( $this->_auth == true )
			{
				curl_setopt($this->s, CURLOPT_USERPWD, $this->_auth_name.':'.$this->_auth_pass);
				if( $this->_auth_type != null )
				{
					//curl_setopt($this->s, CURLOPT_UNRESTRICTED_AUTH, true);
					curl_setopt($this->s, CURLOPT_HTTPAUTH, $this->_auth_type);
				}
			}

			if( $this->_post )
			{
				curl_setopt($this->s, CURLOPT_POST, true);
				curl_setopt($this->s, CURLOPT_POSTFIELDS,$this->_postFields);
			}

			if( $this->_binary )
			{
				curl_setopt($this->s, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($this->s, CURLOPT_POSTFIELDS, $this->_binary_fields);
			}
			
			if( $this->_custom_request != null )
			{
				curl_setopt($this->s, CURLOPT_CUSTOMREQUEST, $this->_custom_request);
				curl_setopt($this->s, CURLOPT_POSTFIELDS, $this->_binary_fields);
			}

			if( $this->_includeHeader )
			{
				curl_setopt($this->s, CURLOPT_VERBOSE, true);
				curl_setopt($this->s, CURLOPT_HEADER, true);
			}

			if( $this->_noBody )
			{
				curl_setopt($this->s, CURLOPT_NOBODY, true);
			}

			curl_setopt( $this->s, CURLOPT_USERAGENT, $this->_useragent );
			curl_setopt( $this->s, CURLOPT_REFERER, $this->_referer );
			$this->_webpage = curl_exec( $this->s );
			$this->_status = curl_getinfo( $this->s, CURLINFO_HTTP_CODE );
			
			/* reset */
			//curl_close( $this->s );
		}

		public function getHttpStatus()
		{
			return $this->_status;
		}
		// simplificado
		public function connect( $url )
		{
			$this->createCurl( $url );
			return $this->_webpage;
		}
		public function send( $url, $post = null )
		{
			if( is_array($post) && count($post)!=0 )
			{
				$this->setPost( $post );
			}
			else if( is_string($post) )
			{
				$this->setFields( $post );
				$this->setCustomRequest( "POST" );
			}
			$this->createCurl( $url );
			return $this->_webpage;
		}
		public function sendBinary( $url, $binary="" )
		{
			if( $binary != "" )
			{
				$this->setBinary( $binary );
				$this->setHttpHeader( array('Content-Length'=>strlen($this->_binary_fields)) );
				$this->setHttpHeader( array('Content-Type'=>'application/json;charset=UTF-8') );
			}
			$this->createCurl( $url );
			return $this->_webpage;
		}
	}
?>
