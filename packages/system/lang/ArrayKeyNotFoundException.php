<?
	/**
	 * Excepcion lanzada cuando una clave en un array no existe
	 * 
	 * @package system.lang
	 * @exception 
	 */
	class ArrayKeyNotFoundException extends Exception
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