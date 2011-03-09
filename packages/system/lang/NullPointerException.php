<?
	/**
	 * Excepcion lanzada cuando se intenta usar null donde un objeto es requerido
	 * 
	 * @package system.lang
	 * @exception 
	 *
	 */
	class NullPointerException extends Exception
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