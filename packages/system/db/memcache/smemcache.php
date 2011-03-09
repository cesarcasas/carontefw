<?php 


/**
 * Maneja memcache (bue, "maneja").
 * @author Cesar Casas (lortjava@gmail.com)
 * @version 1.0
 *
 * 
 * Necesita las constantes 
 * 
 * define('MEMCACHE_HOST', 'localhost');
 * define('MEMCACHE_PORT','11211');
 */

class SMemcache extends Main{
	
	private $memcacheObj;
	public function __construct(){

			$this->memcacheObj = new Memcache;
			$this->memcacheObj->connect(MEMCACHE_HOST, MEMCACHE_PORT);
			

	}
	
	
	public function push($key, $data, $expire){
		
		$data=(is_array($data)) ?  serialize($data): $data;
		
		$this->memcacheObj->set($key, $data, MEMCACHE_COMPRESS, $expire);
		
	}
	
	
	public function get($key){
		
                    return @unserialize($this->memcacheObj->get($key));

		
	}
	
	
	public function status(){
		return $this->memcacheObj->getStats();
	}
	
	
	
}
?>
