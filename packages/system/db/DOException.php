<?php
/**
 * Exception lanzada cuando hay un error de DataObject
 *
 * @exception 
 * @package system.db
 */
class DOException extends Exception
{	
	/**
	 * Constructor
	 *
	 * @param string $msg Mensaje
	 * @param integer $errorCode Codigo de error que reportó la base de datos.
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
		return '[DataObject Error] '.$this->getMessage();
	}
}