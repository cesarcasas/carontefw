<?
	/**
	 * Excepcion lanzada cuando un argumento no es valido
	 * 
	 * @package system.lang
	 * @exception 
	 */
	class IllegalArgumentException extends Exception
	{
		/**
		 * Constructor
		 *
		 * @param string $msg Mensaje
		 */
		function __construct($msg = '')
		{
			parent::__construct($msg);
		}
		
		public function __toString()
		{
			return $this->getMessage();
		}
	}
?>