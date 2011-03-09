<?php 

/**
 * @author Cesar Casas
 * @version 1.0
 * @package system.utils
 *
 */

class SingleLogs{
	function __construct(){
		
	}
	
	function __destruct(){
		
	}
	
	public static function logMail($subject, $body, $to, $user_id=0){
		global $db;
		
		$sql="INSERT INTO ".TABLE_LOGMAILS."
		SET 
		date=now()
		,subject='".$db->escapeString($subject)."'
		,body='".$db->escapeString($body)."'
		,recipient='".$db->escapeString($to)."'
		,user_id='$user_id'
		";
		$db->executeUpdate($sql);
		
	}
}

?>