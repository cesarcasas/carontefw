<?
	/**
	 * Excepcion lanzada cuando un índice de un array o string esta fuera del rango
	 * 
	 * @package system.lang
	 * @exception 
	 */
	class IndexOutOfBoundsException extends Exception
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