<?php

define('SECURITY_KEY_SIMPLE',1);
define('SECURITY_KEY_EXT',2);
define('SECURITY_KEY_MD5',3);
define('SECURITY_KEY_BLOWFISH',4);

/**
 * Objeto "semilla" para usar clases de encriptacion o validacion por intercambio de llaves
 *
 * @package system
 * @subpackage security
 * @author Oscar Gentilezza (ogentilezza@dreamdesigner.com.ar)
 */

class SingKey extends Main {
	
	private $key;
	private $type;
	
	private $validators = array(
		1 => '#^[\w\.\-\_\·\!\#]{2}$#i',
		2 => '#^[\w\.\-\_\·\!\#]{3}$#i',
		3 => '#^\$1\$[\w\.\-\_\·\!\#]{9}$#i',
		4 => '#^\$[2][a]?\$[\w\.\-\_\·\!\#]{12,13}$#i',
	);
	
	/**
	 * Contructor
	 *
	 * @param int $tipo			Si es simple, extendida, md5 o bluefish (los defines son SECURITY_KEY_*)
	 * @param string $clave		La semilla en si.
	 */
	
	function __construct($tipo = null, $clave = null) {
		$this->type = $tipo?$tipo:SECURITY_KEY_SIMPLE;
		if ($clave) $this->setKey($clave);
	}
	
	/**
	 * Setea la semilla validando el formato
	 *
	 * @param string $key
	 */
	
	public function setKey ($key) {
		
		if (preg_match($this->validators[$this->type],$key)) {
			$this->key = $key;
		} else {
			throw new Exception("Formato de semilla incorrecta");
		}
		
	}
	
	/**
	 * Devuelve la semilla
	 *
	 * @return string
	 */
	
	public function getKey() {
		if (!$this->key) throw new Exception("No se ha definido la semilla");
		return $this->key;
	}
	
}