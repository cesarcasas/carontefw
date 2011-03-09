<?
	/**
	 * Excepcion lanzada cuando no se encontro un archivo o es inaccesible.
	 * 
	 * @exception 
	 * @package system.io
	 */
	
	import("system.io.IOException");
	
	class FileNotFoundException extends IOException
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