<?
	/**
	 * Excepcion lanzada cuando una clave de array no es válida o no es la esperada
	 * 
	 * @package system.lang
	 * @exception 
	 *
	 */
	class InvalidArrayKeyException extends InvalidArgumentException
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
	}
?>