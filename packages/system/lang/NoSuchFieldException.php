<?
	/**
	 * Excepcion lanzada cuando no existe una determinada propiedad de un objeto
	 * @exception 
	 * @package system.lang
	 */
	class NoSuchFieldException extends Exception
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