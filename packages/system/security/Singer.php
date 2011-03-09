<?php

import("system.security.SignKey");

/**
 * Clase para firmar y validar firmas
 *
 * @package system
 * @subpackage security
 * @author Oscar Gentilezza ogentilezza@dreamdesigner.com.ar
 * 
 */

class Singer extends Main {
	
	private $int_key;
		
	/**
	 * Constructor
	 *
	 * @param SingKey $key		La semilla
	 */
	
	function __construct(SingKey $key) {
		$this->int_key  = $key->getKey();	
	}
	
	/**
	 * Devuelve una key generada de la semilla (ante firma)
	 *
	 * @param string $dato
	 * @return string
	 */
	
	private function keyFrom (&$dato) {
		return crypt($dato,$this->int_key);
	}

	/**
	 * Firma (digitalmente) un dato (string)
	 *
	 * @param string $dato
	 * @return string			En sha1
	 */
	
	public function firma (&$dato) {
		return sha1($this->keyFrom($dato));
	}
	
	/**
	 * Valida un dato no firmado con la que tendría que ser su firma
	 *
	 * @param string $dato
	 * @param string $firma		Firma digital (sha1)
	 * @return boolean
	 */
	
	public function validaDato (&$dato, $firma) {
		return $firma===$this->firma($dato); 
	}
	
	/**
	 * Devuelve una serie de datos pasados como parametros (todos) con una unica firma en sha1
	 *
	 * @return string		Sha1
	 */
	
	public function getDatosFirmados () {
		
		$datos =  func_get_args();
		$pagedkey = "";
		
		foreach ($datos as $dato)  {
			$pagedkey .= $this->keyFrom($dato);
		}
		
		return $this->firma($pagedkey);
		
	}
		
}