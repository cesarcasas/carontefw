<?
	/**
	 * Excepcion lanzada cuando no se encontro un elemento en un array.
	 * 
	 * @exception 
	 * @package system.utils
	 */
	class NoSuchElementException extends Exception
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