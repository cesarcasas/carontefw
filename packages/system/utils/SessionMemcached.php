<?


import("system.db.memcache.smemcache");
	/**
	 * 
	 * @package system.utils
	 */
	class Session extends Main
	{
		protected $MemcacheObj;
                protected $Expire;
		
		/**
		 * Constructor
		 *
		 * @param string $dbTable Nombre de la tabla MySQL donde se guardan las sesiones.
		 * @param integer $lifeTime Tiempo de duración de la sesión en segundos.
		 */
		public function __construct($lifeTime)
                {

				
			$this->MemcacheObj = new SMemcache();
			$this->Expire=(int)$lifeTime;
		}
		
		function __destruct()
		{
			session_write_close();
		}
		/**
		 * Open session
		 *
		 * @param string $savePath
		 * @param string $sessName
		 * @return boolean
		 */
		public function open($savePath, $sessName)
		{
			if($this->Expire==0){
				$this->Expire = get_cfg_var("session.gc_maxlifetime");
			}
			return true;
		}
		/**
		 * Close session
		 *
		 * @return boolean
		 */
		public function close()
		{
		   try {
		   		if($this->Expire==0){
		   			$this->gc(ini_get('session.gc_maxlifetime'));
		   		}
		   		return true;
		   } catch (Exception $e)  { die("e:".$e);}
		}
		/**
		 * Read session
		 *
		 * @param string $key
		 * @return string
		 */
		public function read($key)
		{
			 $data=$this->MemcacheObj->get($key);   
                         return $data;
			
		}
		/**
		 * Write session
		 *
		 * @param string $key
		 * @param string $val
		 * @return boolean
		 */
		public function write($key, $val)
		{
			try {
		     $sessData=array();
                      
		     $sessData['user_id']= isset($_SESSION['user'])?$_SESSION['user']['id']:0;
		      
		      
		      $sessData['ip']=$_SERVER['REMOTE_ADDR'];
		      $sessData['agent']=$_SERVER['HTTP_USER_AGENT'];
		      $sessData['uri']=$_SERVER['REQUEST_URI'];
		     $this->MemcacheObj->push($key, $sessData,$this->Expire); 

                     $save=$this->MemcacheObj->get($key);
		
			} catch (Exception $e) { 
					
				die("e:".$e); 
			}
		}
		/**
		 * Destroy session
		 *
		 * @param string $key
		 * @return boolean
		 */
		public function destroy($key)
		{

                    $this->MemcacheObj->push($key, null, 1);
			
		}
		/**
		 * Delete session
		 *
		 * @param integer $maxlifetime
		 * @return boolean
		 */
		public function gc($maxlifetime)
		{
			
			return true;
		}
		
		public function getUserByHash($hash=''){
		//	$sql="SELECT user_id FROM {$this->dbTable} WHERE sess_key='$hash'";
		//	$result=$this->db->executeQuery($sql);
		//	if($result->numRows()==0) return 0;
			
		//	$id=$result->fetchArray();
		//	return $id['user_id'];
		}
	}
?>
