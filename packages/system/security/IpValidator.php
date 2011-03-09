<?php

/**
 * Clase que devuelve el ip original de donde se conectaron, saltando proxies y redireccionamientos balanceados
 *
 * @package system
 * @subpackage security
 * 
 */

class IpValidator extends Main {
	
	/**
	 * Valida que lo enviado sea un ip, y que no sea una ip reservada.
	 *
	 * @param string $ip
	 * @return boolean
	 */
	
	static function validip($ip = null) {
		
		if ($ip && ip2long($ip)!=-1) {
		    $reserved_ips = array (
		    array('0.0.0.0','2.255.255.255'),
		    array('10.0.0.0','10.255.255.255'),
		    array('127.0.0.0','127.255.255.255'),
		    array('169.254.0.0','169.254.255.255'),
		    array('172.16.0.0','172.31.255.255'),
		    array('192.0.2.0','192.0.2.255'),
		    array('192.168.0.0','192.168.255.255'),
		    array('255.255.255.0','255.255.255.255')
		    );
		
		    foreach ($reserved_ips as $r) {
		        $min = ip2long($r[0]);
		        $max = ip2long($r[1]);
		        if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		    }
		    return true;
		} else {
		    return false;
		}
 	}

 	/**
 	 * Devuelve la IP real
 	 *
 	 * @return string		n.n.n.n
 	 */
	
	static function getip() {
	    
		if (IpValidator::validip(isset($_SERVER["HTTP_CLIENT_IP"])?$_SERVER["HTTP_CLIENT_IP"]:'')) {
	        return $_SERVER["HTTP_CLIENT_IP"];
	    }
	    foreach (explode(",",isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]:"") as $ip) {
	        if (IpValidator::validip(trim($ip))) {
	            return $ip;
	        }
	    }
	    if (IpValidator::validip(isset($_SERVER["HTTP_X_FORWARDED"]) ? $_SERVER['HTTP_X_FORWARDED']  : "" )) {
	        return $_SERVER["HTTP_X_FORWARDED"];
	    } elseif (IpValidator::validip( isset($_SERVER["HTTP_FORWARDED_FOR"])? $_SERVER['HTTP_FORWARDED_FOR'] : ""  )) {
	        return $_SERVER["HTTP_FORWARDED_FOR"];
	    } elseif (IpValidator::validip( isset($_SERVER["HTTP_FORWARDED"]) ? $_SERVER['HTTP_FORWARDED'] : ""  )) {
	        return $_SERVER["HTTP_FORWARDED"];
	    } elseif (IpValidator::validip(isset($_SERVER["HTTP_X_FORWARDED"]) ? $_SERVER['HTTP_X_FORWARDED'] : ""  )) {
	        return $_SERVER["HTTP_X_FORWARDED"];
	    } else {
	        return $_SERVER["REMOTE_ADDR"];
	    }
	}

	
}
