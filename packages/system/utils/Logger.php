<?
	import("system.utils.User");
	
	/**
	 * Clase para manejar logs en archivo/DB.
	 * Solo existe una sola instancia de esta clase y se obtiene mediante el metodo Logger::init()
	 * Estructura de la tabla Logs (El nombre se puede configurar):
	 * 
	 * CREATE TABLE `Logs` (                                  
          `log_id` int(11) NOT NULL auto_increment,            
          `log_date` datetime NOT NULL,                        
          `log_ip` varchar(20) NOT NULL default '0.0.0.0',     
          `log_agent` varchar(255) default NULL,               
          `log_lang` varchar(30) default NULL,                 
          `log_type` varchar(30) default NULL,                 
          `log_module` varchar(255) default NULL,              
          `log_task` varchar(255) default NULL,                
          `log_subtask` varchar(255) default NULL,             
          `log_file` varchar(255) default NULL,                
          `log_line` varchar(255) default NULL,                
          `log_url` varchar(255) default NULL,                 
          `log_text` text,                                     
          `log_request` text,                                  
          `log_backtrace` text,                                
          `log_session` text,                                  
          PRIMARY KEY  (`log_id`)                              
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
	 * 
	 * @package system.utils
	 * @version 1.0
	 */
	class Logger extends Main {
		/**
		 * @var Logger
		 */
		protected static $instance;
		/**
		 * @var integer
		 */
		protected $logType;
		/**
		 * @var integer
		 */
		protected $logLevel;
		/**
		 * @var DBConnection
		 */
		protected $dbConnection = null;
		/**
		 * @var string
		 */
		protected $logFilePath = LOG_FILENAME;
		/**
		 * @var string
		 */
		protected $logTable = LOG_TABLE;
		
		/**
		 * Tipo de log. Indica logear en la DB
		 * @var integer
		 */
		const LOG_SQL = 1;
		/**
		 * Tipo de log. Indica logear en archivo
		 * @var integer
		 */
		const LOG_FILE = 2;
		/**
		 * Tipo de log. Indica logear en DB y archivo
		 * @var integer
		 */
		const LOG_ALL = 3;
		
		/**
		 * Nivel de log. Indica información
		 * @var integer
		 */
		const INFO = 1;
		/**
		 * Nivel de log. Indica una advertencia
		 * @var integer
		 */
		const WARNING = 2;
		/**
		 * Nivel de log. Indica un error
		 * @var integer
		 */
		const ERROR = 4;
		/**
		 * Nivel de log. Indica una excepción
		 * @var integer
		 */
		const EXCEPTION = 8;
		/**
		 * Nivel de log. Indica una consulta a la DB
		 * @var integer
		 */
		const SQL_QUERY = 16;
		/**
		 * Nivel de log. Indica un error de SQL
		 * @var integer
		 */
		const SQL_ERROR = 32;
		/**
		 * Nivel de log. Indica habilitar todos los niveles de log
		 * @var integer
		 */
		const ALL = 63;
		
		protected function __construct($logType, $logLevel, $dbConnection) {
			$this->setLogLevel($logLevel);
			$this->setLogType($logType);
			$this->dbConnection = $dbConnection;
		}
		/**
		 * Obtener nombre del tipo de log.
		 *
		 * @param integer $logLevel
		 * @return string
		 */
		protected function getLogLevelName($logLevel) {
			switch ($logLevel) {
				case self::INFO:
					return "INFORMATION";
				case self::WARNING:
					return "WARNING";
				case self::ERROR:
					return "ERROR";
				case self::EXCEPTION:
					return "EXCEPTION";
				case self::SQL_QUERY:
					return "SQL_QUERY";
				case self::SQL_ERROR:
					return "SQL_ERROR";
				default:
					return "";
			}
		}
		/**
		 * [Singleton] Inicializar el objeto Logger.
		 *
		 * @param integer $logType
		 * @param integer $logLevel
		 * @param DBConnection $dbConnection
		 * @return Logger
		 */
		public static function init($logType = self::LOG_FILE, $logLevel = self::ALL, DBConnection $dbConnection = null) {
			if (!self::$instance) self::$instance = new self($logType, $logLevel, $dbConnection);
			return self::$instance;
		}
		/**
		 * Establecer el nivel de log. Habilita los niveles de log especificados en la mascara de bits.
		 * 0 deshabilita los logs
		 *
		 * @param integer $logLevel
		 * @return void
		 */
		public function setLogLevel($logLevel) {
			$this->logLevel = $logLevel;
		}
		/**
		 * Establecer el tipo de log (indica donde logear).
		 * Es una mascara de bits y se deben utilizar las constantes de clase LOG_*
		 * 0 deshabilita los logs
		 *
		 * @param integer $logType
		 * @return void
		 */
		public function setLogType($logType) {
			$this->logType = $logType;
		}
		/**
		 * Establecer ruta del archivo donde logear.
		 *
		 * @param string $logFilePath
		 * @return void
		 */
		public function setLogFile($logFilePath) {
			$this->logFilePath = $logFilePath;
		}
		/**
		 * Establecer nombre de la tabla donde se logea.
		 *
		 * @param string $logFilePath
		 * @return void
		 */
		public function setLogTable($logTable) {
			$this->logTable = $logTable;
		}
		/**
		 * Logear
		 *
		 * @param integer $logLevel Debe ser una de las constantes de clase que indican tipo de log.
		 * @param string $message Mensaje a logear.
		 * @param string $file Archivo fuente.
		 * @param integer $line Linea fuente.
		 * @param integer $logType Indica si logear en SQL, un archivo o ambos.
		 * @return boolean TRUE si se logeó con éxito. FALSE si hubo error o el logLevel esta deshabilitado.
		 */
		public function log($logLevel, $message, $file = "", $line = -1, $logType = null) {
			if (($logLevel & $this->logLevel) == 0) return false;
			if ($logType === null) $logType = $this->logType;
			$request = Request::init();
			$logged = false;
			if (($logType & self::LOG_FILE) && file_exists($this->logFilePath)) {
				// log file
				$f = @fopen($this->logFilePath, 'a+');
				if (!is_resource($f)) return false;
				$str = "[ ".date("d/m/Y H:i:s")." ] [ {$_SERVER['REMOTE_ADDR']} ] [ {$_SERVER['HTTP_USER_AGENT']} ] [ ".User::getLanguage()." ] [ ".$this->getLogLevelName($logLevel)." ] [ ".$request->module." ] [ ".$request->task." ] [ ".$request->subtask." ] [ ".$file." ] [ ".$line." ] [ ".APP_URL.$_SERVER['REQUEST_URI']." ]  ".trim(preg_replace("#[\r\n]+#", " ", $message))."\n";
				@fwrite($f, $str); 
				@fclose($f);
				$logged = true;
			}
			if (($logType & self::LOG_SQL) && $this->dbConnection != null) {
				$db = $this->dbConnection;
				// log SQL
				ob_start();
				ob_implicit_flush(0);
				print_r($_SESSION);
				$session = ob_get_contents();
				ob_end_clean();
				//--------------------------------------
				ob_start();
				ob_implicit_flush(0);
				debug_print_backtrace();
				$backtrace = ob_get_contents();
				ob_end_clean();
				//--------------------------------------
				ob_start();
				ob_implicit_flush(0);
				echo "POST:\n";
				if (isset($_POST)) print_r($_POST);
				echo "GET:\n";
				if(isset($_GET)) print_r($_GET);
				$_request = ob_get_contents();
				ob_end_clean();
				//--------------------------------------
				$sql = "INSERT INTO {$this->logTable} (
							log_date, log_ip, log_agent, log_lang,
							log_type, log_module, log_task, log_subtask,
							log_file, log_line, log_url, log_text,
							log_request, log_backtrace, log_session
						) VALUES (
				NOW(), '{$_SERVER['REMOTE_ADDR']}', '".$db->escapeString($_SERVER['HTTP_USER_AGENT'])."', '".User::getLanguage()."',
				'".$this->getLogLevelName($logLevel)."', '".$db->escapeString($request->module)."', '".$db->escapeString($request->task)."', '".$db->escapeString($request->subtask)."',
				'$file', '$line', '".$db->escapeString($_SERVER['REQUEST_URI'])."', '".$db->escapeString(trim($message))."',
				'".$db->escapeString($_request)."', '".$db->escapeString($backtrace)."', '".$db->escapeString($session)."'
						)";
				try {
					$db->executeUpdate($sql);
				} catch (SQLException $e) {
					return false;
				}
				$logged = true;
			}
			return $logged;
		}
	}
?>