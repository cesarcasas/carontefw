<?php 

class Application{
	var $appName;
	var $appVersion;
	var $localDBConfiguration;
	var $dbObject;
	var $Request;
	
	
	
	public function __construct($AppName){
		$this->appName=$AppName;
		$this->localDBConfiguration=array();
		$this->dbObject=null;
		$this->Request=null;
		$this->pathFramework=defined(PATH_FRAMEWORK) ? PATH_FRAMEWORK : dirname(__FILE__);
		$this->pathApp=$_SERVER['DOCUMENT_ROOT'];
	}
	
	public function readConfig($key) {
	   if (!empty($this->ModeAPP)) {
	      $mode = $this->ModeAPP;
         $config = @$this->$mode;
	      
	      if (!empty($config[$key])) {
	         return $config[$key];
	      }
	   }
	
	  return array();
	}
	
	
	public function __destruct(){
		
	}
	
	public function getApplicationVars(){
		
		$vars=array();
		
		foreach($this as $key => $value) {
			$vars[$key]=$value;
       }
       
       return $vars;
	}
	
	
	public function checkInitApplicationBasic(){
		
		
		if(!isset($this->production)) die("No config available for Production site!");
		if(!isset($this->developer)) die("No config available for Developer site!");
		if(!isset($this->preproduction)) die("No config available for Pre production site!");
		
		
	}
	
	public function run(){
		$this->checkInitApplicationBasic();
		
		if(isset($_SERVER['SERVER_NAME'])){
			$localDomain=$_SERVER['SERVER_NAME'];
			if($this->production["DOMAINS"]){
				if(in_array($localDomain, $this->production["DOMAINS"])){
					$this->ModeAPP="production";
					$this->localDBConfiguration=$this->production["DATABASE"];
				}
				
				else if (in_array($localDomain, $this->preproduction["DOMAINS"])) {
					$this->ModeAPP="preproduction";
					$this->localDBConfiguration=$this->preproduction["DATABASE"];
				}
				else if (in_array($localDomain, $this->developer["DOMAINS"])) {
					$this->ModeAPP="developer";
					$this->localDBConfiguration=$this->developer["DATABASE"];
				}
				else{
					die("No config available for domain <strong>$localDomain</strong>");
					$this->ModeAPP="";
				}
			}
			
		}
		
		import("system.lang.Main",
        "system.lang.*");
        
		$this->Request= Request::init();
		
	}
	
	
	public function down(){
		require_once(PATH_APPLICATION.'/app_disabled.html');
	}
	
	public function getBrowser($user_agent="") {
	
			if($user_agent=="") $user_agent=@$_SERVER['HTTP_USER_AGENT'];
			
		     $navegadores = array(
		          'Opera' => 'Opera',
		          'Mozilla Firefox'=> '(Firebird)|(Firefox)',
		          'Galeon' => 'Galeon',
		          'Mozilla'=>'Gecko',
		          'MyIE'=>'MyIE',
		          'Lynx' => 'Lynx',
		          'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
		          'Konqueror'=>'Konqueror',
		          'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
		          'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
		          'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
		          'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
		);
		foreach($navegadores as $navegador=>$pattern){
		   if (preg_match("#" . $pattern . "#", $user_agent))
		       return $navegador;
		   }
		return 'Desconocido';
		}
	
	
		
		public function getTimeZones()	{
		$tz = array(
			array(-12 => '(GMT -12:00) Eniwetok, Kwajalein'),
			array(-11 => '(GMT -11:00) Midway Island, Samoa'),
			array(-10 => '(GMT -10:00) Hawaii'),
			array(-9 => '(GMT -9:00) Alaska'),
			array(-8 => '(GMT -8:00) Pacific Time (US &amp; Canada)'),
			array(-7 => '(GMT -7:00) Mountain Time (US &amp; Canada)'),
			array(-6 => '(GMT -6:00) Central Time (US &amp; Canada), Mexico City'),
			array(-5 => '(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima'),
			array(-4 => '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz'),
			array(-3.5 => '(GMT -3:30) Newfoundland'),
			array(-3 => '(GMT -3:00) Brazil, Buenos Aires, Georgetown'),
			array(-2 => '(GMT -2:00) Mid-Atlantic'),
			array(-1 => '(GMT -1:00) Azores, Cape Verde Islands'),
			array(0 => '(GMT) Western Europe Time, London, Lisbon, Casablanca'),
			array(1 => '(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris'),
			array(2 => '(GMT +2:00) Kaliningrad, South Africa'),
			array(3 => '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg'),
			array(3.5 => '(GMT +3:30) Tehran'),
			array(4 => '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi'),
			array(4.5 => '(GMT +4:30) Kabul'),
			array(5 => '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent'),
			array(5.5 => '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi'),
			array(6 => '(GMT +6:00) Almaty, Dhaka, Colombo'),
			array(7 => '(GMT +7:00) Bangkok, Hanoi, Jakarta'),
			array(8 => '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong'),
			array(9 => '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
			array(9.5 => '(GMT +9:30) Adelaide, Darwin'),
			array(10 => '(GMT +10:00) Eastern Australia, Guam, Vladivostok'),
			array(11 => '(GMT +11:00) Magadan, Solomon Islands, New Caledonia'),
			array(12 => '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka')
		);
		return $tz;
	}
	
	public function setupSessionCookie() {

	   $config = $this->readConfig('SESSION');
	   
	   $path = !empty($config['PATH']) ? $config['PATH'] : '/';
	   $domain = !empty($config['DOMAIN']) ? $config['DOMAIN'] : (!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
	   	   
      if (!empty($domain)) {	   
	     session_set_cookie_params(0, $path, $domain);
      }
	}
	
	public function startSessionDB($tblName=TABLE_SESSIONS, $life=28800, $dbInstance){
		import(	"system.lang.Main",
        	"system.utils.Session"
			);

	
		
		
		// iniciar sesion
		$obj_session = new Session($tblName, $life, $dbInstance);
		session_set_save_handler(
			array($obj_session,"open"),
			array($obj_session,"close"),
			array($obj_session,"read"),
			array($obj_session,"write"),
			array($obj_session,"destroy"),
			array($obj_session,"gc")
		);
		$this->setupSessionCookie();
		session_start();

	}
	
	public function startSessionFile(){
	   $this->setupSessionCookie();
		session_start();
	}
	public function connectDB(){
		import(	
		        "system.db.*",
				"system.db.mysql.*"
		);
		
		
		$db = DB::getConnection($this->localDBConfiguration["host"],$this->localDBConfiguration["user"],$this->localDBConfiguration["password"],$this->localDBConfiguration["database"], $this->localDBConfiguration["port"],null, false);
		$db->setCharset('utf8');
		$this->dbObject=$db;
		return $db;

	}
	
	public function getDBObject(){
		return $this->dbObject;
	}
}

