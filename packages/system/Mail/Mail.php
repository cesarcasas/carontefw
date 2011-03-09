<?php 

class Mail extends Main{
	
	function __construct(){
		
	}
	
	
	function __destruct(){
		
	}
	
	function create(){
		
		global $_AppCaronte;
		$_AppCaronte->getDBObject();
		$dbo = $_AppCaronte->getDBObject();
		$dbo->executeUpdate("
		
		CREATE TABLE if not exists `CountriesNN` (
		  `countries_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `countries_description` varchar(128) NOT NULL DEFAULT '',
		  `countries_code` char(2) NOT NULL DEFAULT '',
		  `countries_excode` char(3) NOT NULL DEFAULT '',
		  PRIMARY KEY (`countries_id`),
		  UNIQUE KEY `countries_description` (`countries_description`),
		  UNIQUE KEY `countries_code` (`countries_code`),
		  UNIQUE KEY `countries_excode` (`countries_excode`),
		  UNIQUE KEY `countries_id` (`countries_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=895 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;");
			}
	
	
	public static function addToSpooler(
								$sp_from,
								$sp_fromName,
								$sp_to,
								$sp_headers,
								$sp_body,
								$sp_asunto,
								$sp_html,
								$sp_newsletter=-1,
								$sp_attach=array(),
								$sp_hash
								){
		
						
		
		global $_AppCaronte;
		self::create();
		
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = false;
		$ar = $spool->ActiveRecord;
		
		/*$sql="INSERT INTO Spooler
		SET 
		sp_from='".mysql_real_escape_string($sp_from)."'
		,sp_fromname='".mysql_real_escape_string($sp_fromName)."'
		,sp_to='".mysql_real_escape_string($sp_to)."'
		,sp_header='".mysql_real_escape_string($sp_headers)."'
		,sp_asunto='".mysql_real_escape_string($sp_asunto)."'
		,sp_body='".mysql_real_escape_string($sp_body)."'
		,sp_html='".mysql_real_escape_string($sp_html)."'
		,sp_newsletter='".mysql_real_escape_string($sp_html)."'
		,sp_time=now()
		,sp_attach=".serialize($sp_attach)."
		,sp_hash='".mysql_real_escape_string($sp_hash)."'
		";
		
		mysql_query($sql);*/
		
		$ar->insert();
		
		$ar->SpFrom = $sp_from;
		$ar->SpFromname = $sp_fromName;
		$ar->SpTo = $sp_to;
		$ar->SpHeader = $sp_headers;
		$ar->SpAsunto = $sp_asunto;
		$ar->SpBody = $sp_body;
		$ar->SpHtml = $sp_html;
		$ar->SpNewsletter = $sp_newsletter;
		$ar->SpTime = array("NOW()");
		$ar->SpAttach = serialize($sp_attach);
		$ar->SpHash = $sp_hash;
		
		$ar->save();
		
		unset($ar);
	}
	
	
	
	
	
	public static function Send($spID=0){
		
		
		$spID=(int)$spID;
		
		$spData=self::getMail($spID);	
		
		
		$mail=new BuildMail();
		$mail->sendSpooler($spData['sp_body'],$spData['sp_asunto'],$spData['sp_from'],"",$spData['sp_to'],$spData['sp_header'], $spData['sp_attach'], $spData['sp_hash']);
		self::changeStatus($spData['sp_id']);
		
	}
	
	
	public static function SendMails($total=0){
		//print_r($total);
	 $mails=Mail::getAllMailsByStatus(0,$total);
			
	 //print_r($mails);
	 $enviados=array();
		 foreach ($mails as $mail){
		 		$enviados[]=$mail['sp_to']." ".$mail['sp_asunto'];
				self::Send1($mail['sp_id']);
		 }
		 
		 return $enviados;
		 
	}
	
	
	
	public static function changeStatus($id,$status){
		global $_AppCaronte;
	     self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		$ar = $spool->ActiveRecord;
		$ar->edit($id);

		$ar->SpStatus = $status;
		
		$ar->save();
		
	}
	
	
	
		
	public static function getAllMails($recPerPage=-1, $spool)	{
			
				
		global $_AppCaronte;
		self::create();
		
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = true;
		$search = $spool->ActiveSearch;	
		
		$result = $search->find('all');
		
		return $result;
	}
	
	
	public static function getAllMailsByStatus($status=1,$limit=10)
	{
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = true;
		$search = $spool->ActiveSearch;	
		
		$parametros = array(
 			'conditions' => array(
  			'=CONCAT(sp_status) =' => "$status"
 			),
 			'limit' => "$limit"
		);

		
		$result = $search->find('all',$parametros);
	
		return $result;
	}
	
	public static function getAllMailsSpooler()
	{
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = true;
		$search = $spool->ActiveSearch;	
		
		$result = $search->find('all');
	
		return $result;
	}
	
	public static function getMailsByStatus($params=array(),$mails=-1,$news=-1)
	{
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = true;
		$search = $spool->ActiveSearch;	
		
		if($mails!=-1)
		{
			if($news!=-1)
			$params ['conditions']=array('=sp_newsletter'=>$news);
			else 
			$params ['conditions']=array('=sp_newsletter <>'=>-1);
		}
		
		$params['conditions']['SpStatus'] = 0;
		
		$result = $search->find('pager',$params);
	
		return $result;
	}
	
	public static function getNewsletterById($id_news)
	{
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		//$spool->debug = true;
		$search = $spool->ActiveSearch;	
		
		$params['conditions']=array('=sp_newsletter'=>$id_news);
		
		$result = $search->find('pager',$params);
	
		return $result;
	}
		
	
	public static function delete($id){
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		$ar = $spool->ActiveRecord;

		$ar->edit($id);
		$ar->delete();
	}
	
	
	public static function deleteNewsletter($id_news){
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		$ar = $spool->ActiveRecord;
        $params['conditions']=array('=sp_newsletter'=>$id_news);
		$ar->deletePack($params);
	}
	
		
	public static function getMail($id)
	{
		global $_AppCaronte;
		self::create();
		$spool = new DataObject('Spooler', 'sp_id', $_AppCaronte->getDBObject());
		$search = $spool->ActiveSearch;

		$result = $search->get((int)$id);
		return $result[0];
		
	}
	
	
	public static function Send1($spID=0){
		
		
		$spID=(int)$spID;
		
		$spData=self::getMail($spID);	
		
		$mail=new BuildMail();
		$mail->sendSpooler($spData['sp_body'],$spData['sp_asunto'],$spData['sp_from'],"",$spData['sp_to'],$spData['sp_header'], $spData['sp_attach'], $spData['sp_hash']);
		self::changeStatus($spData['sp_id'],1);
		
	}
	
	public function getBody(){
		//self::setHeader();
		$body=file_get_contents($this->template);
		$keys=array_keys($this->vars);
		foreach ($keys as $key){
			$body=str_replace("{".$key."}", $this->vars[$key], $body);
		}
		$this->body=$body;

		
		return $body;
	}
	
	
	
		
}
?>
