<?
	/**
	 * Excepcion lanzada cuando ocurrio un error de escritura/lectura de archivos
	 * 
	 * @exception 
	 * @package system.io
	 */
	class IOException extends Exception
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