<?
	/**
	 * Exception lanzada cuando hubo un error en un componente
	 *
	 * @exception 
	 * @package system.gui.components
	 */
	class ComponentException extends Exception
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
		/**
		 * Obtener objeto como un string.
		 * 
		 * @return string
		 */
		public function __toString()
		{
			return $this->getMessage();
		}
	}
?>