<?php
/*
Esta clase se utiliza para 

*/
class ActivitySystem extends Main
{
	
	
	private function __construct() {}
	
	public static function create(){
		global $_AppCaronte;
		$_AppCaronte->getDBObject();
		$dbo = $_AppCaronte->getDBObject();
		$dbo->executeUpdate("
		 create table if not exists `Activity` (
			`activity_id` int NOT NULL AUTO_INCREMENT,
			`activity_name` varchar (15),
			`activity_url` varchar(100) ,
			`activity_description` text ,
		 	PRIMARY KEY (`activity_id`)
				) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
		");
	
		
	}
		
	public static function add($activity_name,$activity_url,$activity_description){
		global $_AppCaronte;
		self::create();
	  
	  	$act = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		$ar = $act->ActiveRecord;
		
		$ar->insert();
		
		$ar->ActivityName = $activity_name;
		$ar->ActivityUrl = $activity_url;
		$ar->ActivityDescription = $activity_description;
		return $ar->save();	
		
	 
	}
	
	
	public static function update($activity_id,$activity_name,$activity_url,$activity_description){
		global $_AppCaronte;
		self::create();
	  
	  	$act = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		$ar = $act->ActiveRecord;
		
		$ar->edit($activity_id);
		
		$ar->ActivityName = $activity_name;
		$ar->ActivityUrl = $activity_url;
		$ar->ActivityDescription = $activity_description;
		$ar->save();	
	 
	}
	
	public static function delete($activity_id){
		
		global $_AppCaronte;
		
		LogsSystem::deleteByAct($activity_id);
		$from = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		
		$ar = $from->ActiveRecord;

		$ar->edit($activity_id);
		$ar->delete();
	}
	
	
	public static function getInfo($activity_id){
		global $_AppCaronte;
		
		$from = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		$search = $from->ActiveSearch;

		$result = $search->get($activity_id);
		return $result[0];
		
	}
	public static function getInfoByUrl($activity_url){
		global $_AppCaronte;
		$from = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		//$from->debug=true;
		$search = $from->ActiveSearch;
		$params=array("conditions"=>array(
		'=activity_url'=>"'".$activity_url."'"));
		$result = $search->find('all', $params);
		$id=0;
		if(count($result)>0){
			$id=$result[0]['activity_id'];
		}
		
		return $id;
		
	}
	
	public static function getlala($activity_url){
		global $_AppCaronte;
		return 1;
		
	}
	public static function getIDByUrl($activity_url){
	  	$result=self::getIDByUrl($activity_url);
	 	return $result['activity_id'];
	}
	public static function getAllpage1($recsPerPage = -1,$page="",$activity_name="")
	{
		
		$filters="";
		$filters.=$activity_name!=""?" and concat(activity_name,activity_url,activity_description) like '%".$activity_name."%'":"";
		
		
		global $db;
		$sql = "SELECT *
				FROM Activity L 
				
				where 1
				$filters
		";
		
	 	if ($recsPerPage == -1)	return $db->executeQuery($sql);
		else return $db->executePagedQuery($sql, $recsPerPage,'p', false, '', -1, $page);
		
	}
	
	public static function getAllpage($params=array())

	{
		global $_AppCaronte;
		
		$froms = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		//$froms->debug=true;
		
		$search = $froms->ActiveSearch;
	
		
		if(empty($params)){
			$result = $search->find('all');
			
		}else{
			
			$result = $search->find('pager', $params);
		}
		
		return $result;
	}
	
	public static function exists($activity)
	{    
		global $_AppCaronte;
		$act = new DataObject('Activity', 'activity_id', $_AppCaronte->getDBObject());
		$search = $act->ActiveSearch;
		self::create();	
		$parametros = array(
 			'conditions' => array(
  			'=activity_name' => "'".$activity."'"
 			)
		);
		$query=$search->find('all',$parametros);
		
		return count($query)>0;
	}
}