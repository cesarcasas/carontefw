<?php
/*
Esta clase se utiliza para la lectura y escritura de las actividades realizadas por el usuario

*/
class LogsSystem 
{
	
	
	private function __construct() {}
	
	public static function create(){
		global $_AppCaronte;
		$_AppCaronte->getDBObject();
		$dbo = $_AppCaronte->getDBObject();
		$dbo->executeUpdate("
		 create table if not exists `Logs_system` (
		 `log_id` int NOT NULL AUTO_INCREMENT ,
		 `log_ip` varchar (15),
		 `log_date` datetime,
		 `log_description` text ,
		 `activity_id` int ,
		 `user_id`  int,
			PRIMARY KEY (`log_id`)
				) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
		");
		
		
	}
		
	public static function add($log_ip="",$log_description,$activity_id,$user_id=0){
		global $_AppCaronte;
		self::create();
	  	$act = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
	  	//$act->debug=true;
		$ar = $act->ActiveRecord;
		$ar->insert();
		$ar->LogIp = $log_ip;
		$ar->LogDescription = $log_description;
		$ar->LogDate = array('NOW()');
		$ar->UserId = $user_id;
		$ar->ActivityId = $activity_id;
		return $ar->save();	
	 
	  
	}
	
	
	public static function update($log_id,$log_ip="",$log_description,$activity_id,$user_id=0){
		
		global $_AppCaronte;
		self::create();
	  
	  	$act = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
		$ar = $act->ActiveRecord;
		
		$ar->edit($log_id);
		
		$ar->LogIp = $log_ip;
		$ar->LogDescription = $log_description;
		$ar->UserId = $user_id;
		$ar->ActivityId = $activity_id;
		$ar->save();	
		
	}
	
	public static function delete($log_id){
		global $_AppCaronte;
			 
	  	$act = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
		$ar = $act->ActiveRecord;
		
		$ar->edit($log_id);
		$ar->delete();
	}
	
	public static function deleteByAct($activity_id){
		global $_AppCaronte;
		$act = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
		
		$ar = $act->ActiveRecord;
		
		$params = array(
					'conditions' => array(
					'ActivityId =' => $activity_id
					)
			);

		$ar->deletePack($params);
	}
	
	
	public static function getInfo($log_id)
	{
		global $_AppCaronte;
		
		$act = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
		$search = $act->ActiveSearch;

		$result = $search->get($log_id);
		return $result[0];
	}
	

	
	public static function getAll($params=array(),$table="Users",$user_id='user_id',$user_name="user_username")
	{
		global $_AppCaronte;
		
		$froms = new DataObject('Logs_system', 'log_id', $_AppCaronte->getDBObject());
		//$froms->debug=true;
		$search = $froms->ActiveSearch;
		$search->setJoin('left', $table, 'user_id', 'user_id');
		$search->setJoin('Inner', 'Activity', 'activity_id', 'activity_id');
		$params['fields']= array('=Logs_system.*',"=".$table.".".$user_name, '=activity_name');
		if(empty($params) ){
			
			$result = $search->find('all');
			
		}else{
			if(!isset($params['page_size'])){
			
				$result = $search->find('all',$params);
			}else{
				$result = $search->find('pager', $params);
			}
		}
		
		return $result;
	}
	
	public static function registerEvent($log_ip="",$log_description="",$activity_url,$user_id=0){
	   $activity=ActivitySystem::getInfoByUrl($activity_url);
	   self::add($log_ip,$log_description,$activity['activity_url'],$user_id);
	 
	}
	
	public static function getAllUsers($table,$clave){
		global $_AppCaronte;
		$froms = new DataObject($table, $clave, $_AppCaronte->getDBObject());
		$search = $froms->ActiveSearch;
		$result = $search->find('all');
		return $result;
	}
	
	public static function registerLog($data,$user_id,$description,$ip){
		$name="";
		$names=explode("/",$data);
		$url="";
		foreach ($names as $name1){
			if($name1!=""){
				$id=(int)$name1;
				
			if($id==0){
					$url.="/".$name1;
					if($name=="")
						{
						$name.=$name1;
						}
					else{
						$name.=".".$name1;
						}
				}
			}
		}
		$url_find1="";
		$activity=0;
		$valor=$data==""?false:true;
		if($valor){
			$valores=explode("?",$data);				
			if(count($valores)>0){
				$data=$valores[0];
			}
			
			$activity=ActivitySystem::getInfoByUrl($url);
			
			if($activity==0){
				$activity=ActivitySystem::add($name,$url,"Sin descripcion");
			}
			
			self::add($ip,$description,$activity,$user_id);
			}
		  
	}
}