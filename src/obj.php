<?php
	namespace response;
	class obj
	{
		public function __construct( array $value = array() )
		{
			if ( is_array ( $value ) )
			{
				foreach($value as $i=>$v)
				{
					if(is_array($v))
						$this->{$i} = new obj( $v );
					else
						$this->{$i} = $v;
				}
			}
		}
		public function __set( $name, $value )
		{
			$this->{$name} = $value;
		}

		public function __get($name)
		{
			if( isset($this->{$name}) )
			{
				return $this->{$name};
			}
			return $this->{$name} = new obj();
		}

		/**  Desde PHP 5.1.0  */
		public function __isset($name)
		{
			return isset( $this->{$name} );
		}

		/**  Desde PHP 5.1.0  */
		public function __unset($name)
		{
			unset( $this->{$name} );
		}
		public function __toString()
		{
			return "";
		}
		/** Desde PHP 5.4.0 **/
		public function json( $callback = null, $pretty = false )
		{
			if( $callback!=null )
			{
				return ( $pretty ) ? $callback . '(' . json_encode($this, JSON_PRETTY_PRINT) .');' : $callback . '(' . json_encode($this) .');';
			}
			return ( $pretty ) ? json_encode($this, JSON_PRETTY_PRINT) : json_encode($this);
		}
		
		
		function xmlChild( &$xml, $name, $value )
		{
			if( !is_array($value) && !is_object($value) )
			{
				$xml->addChild( $name, $value );
			}
			else
			{
				$node = $xml->addChild( $name );
				foreach($value as $i=>$v)
				{
					$this->xmlChild($node, $i, $v );
				}
			}
			return $xml;
		}
		public function xml( $root = 'root' )
		{
			$xml = new \SimpleXMLElement('<'.$root.'/>');
			foreach($this as $i=>$v)
			{
				$this->xmlChild($xml,$i,$v);
			}
			return $xml->asXML();
		}
	}
?>
