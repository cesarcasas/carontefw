<?
	/**
	 * Excepcion lanzada cuando no existe un determinado metodo de un objeto
	 * @exception 
	 * @package system.lang
	 */
	class NoSuchMethodException extends Exception
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