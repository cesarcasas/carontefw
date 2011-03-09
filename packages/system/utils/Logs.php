<?

/**
 * Esta classe logea todo a texto o sql.
 * La idea de la clase es poder logear lo que querramos en todo momento.
 * 
 * @package utils
 * @deprecated Usar clase Logger
 * @see Logger
 */

/*

CREATE TABLE `Loging` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_date` datetime default NULL,
  `log_ip` varchar(20) NOT NULL default '0.0.0.0',
  `log_typetxt` varchar(255) default NULL,
  `log_type` int(11) default NULL,
  `user_id` int(11) default NULL,
  `log_domain` varchar(254) default NULL,
  `log_module` varchar(254) default NULL,
  `log_task` varchar(254) default NULL,
  `log_subtask` varchar(254) default NULL,
  `log_scrip` varchar(254) default NULL,
  `log_line` varchar(254) default NULL,
  `log_url` varchar(254) default NULL,
  `log_text` text,
  `log_os` varchar(15) default NULL,
  `log_nav` varchar(20) default NULL,
  `log_lang` varchar(30) default NULL,
  `log_agent` varchar(255) default NULL,
  `log_sessionvar` text,
  `log_request` text,
  `log_backtrace` text,
  PRIMARY KEY  (`log_id`),
  UNIQUE KEY `log_id` (`log_id`)
)
*/

import("system.utils.User");
import("system.utils.Browscap");

class Logs extends Main
{
	/**
	 * @var integer Logear cualquier query SQL, erronea o no.
	 */
	const SQL_QUERY = 1;
	/**
	 * @var integer Logear querys que devuelven error.
	 */
	const SQL_ERROR = 2;
	/**
	 * @var integer Cualquier tipo de error ( logico, de usuario, etc)
	 */
	const ERROR = 3;
	/**
	 * @var integer Para usar internamente debugeando scripts
	 */
	const DEBUG = 4;
	/**
	 * @var integer Para informar sobre cualquier cosa. (ej: usuario registrado con exito )
	 */
	const INFO = 5;
	/**
	 * @var integer Guardar todos los datos de una exepcion
	 */
	const EXCEPTION = 6;
	
	/**
	 * @var Logs
	 */
	private static $instance = null;
	/**
	 * @var DBConnection
	 */
	private $dbConnection = null;
	
	private $log_file;
	private $log_rot_ena;
	private $log_table;
	private $enabled;
	private $enableFile;
	private $enableSQL;
	private $loglevel;
	private $rem_st;
	private $rem_sq;
	private $rem_fi;
	private $buffkey;
	public $buff=array();
	private $yalogie;
	private $browserInfo;

	/**
	 * Constructor.
	 * 
	 */
	private function __construct($fileToLog, $tableToLog)
	{
		$this->log_file=$fileToLog;
		$this->log_table=$tableToLog;
		$this->log_rot_ena=1;
		$this->enabled=1;
		$this->enableFile=1;
		$this->enableSQL=1;
		$this->loglevel="111111";
		$this->buffkey=0;
		$this->yalogie=0;
		
		//$bc = new Browscap(APP_TEMP_DIR);
		//$this->browserInfo = @$bc->getBrowser(null, true);
	}
	/**
	 * Establecer coneccion a la DB que se usará para logear.
	 *
	 * @param DBConnection $conn
	 * @return void
	 */
	public function setDBConnection(DBConnection $conn)
	{
		$this->dbConnection = $conn;
	}
	/**
	 * Singleton para inicializar el objeto.
	 *
	 * @param string $fileToLog Ruta del archivo donde logear
	 * @param string $tableToLog Nombre de la tabla mysql donde logear
	 * @return Logs
	 */
	public static function init($fileToLog = LOG_FILENAME, $tableToLog = LOG_TABLE)
	{
		if (!isset(self::$instance)) self::$instance = new self($fileToLog, $tableToLog);
		return self::$instance;
	}

	/**
    * Metodo encargado de logear los eventos a un archivo de texto.
    *
    * @param integer $tipo - tipo de logeo. Debe ser una de las constantes de clase.
    * @param string $scriptname - nombre del script en el que fue llamado el metodo, usando __FILE__
    * @param string $scriptline - numero de linea en el que fue llamado el metodo, usando __LINE__
    * @param string $texto - Cosa a logear ( la sentencia sql o el texto que sea )
    * @param string $module - modulo en el que fue llamado $request->module
    * @param string $task - Tarea ejecutandose en el momento de llamar al log
    * @param string $subtask - Sub tarea ejecutandose en el momento de llamar al log
    * @return boolean
    */
	private function logToFile($scriptname,$scriptline,$module,$task,$subtask,$tipo,$texto)
	{
		if ($this->enabled==0 || $this->enableFile==0)
		return true;

		$log_file_p=($this->log_rot_ena?$this->log_file.date("-d-m-Y"):$this->log_file).".log";

		switch($tipo)
		{
			case 1:
			if($this->loglevel[0]==0)
			return 1;
			$tipotxt=str_pad("SQL QUERY", 12);
			break;

			case 2:
			if($this->loglevel[1]==0)
			return 1;
			$tipotxt=str_pad("SQL ERROR", 12);
			break;

			case 3:
			if($this->loglevel[2]==0)
			return 1;
			$tipotxt=str_pad("ERROR", 12);
			break;

			case 4:
			if($this->loglevel[3]==0)
			return 1;
			$tipotxt=str_pad("DEBUG", 12);
			break;

			default:
			case 5:
			if($this->loglevel[4]==0)
			return 1;
			$tipotxt=str_pad("INFORMACION", 12);
			break;
			
			case 6:
			if($this->loglevel[5]==0)
			return 1;
			$tipotxt=str_pad("EXCEPTION", 12);
			break;
		}
		
		$tuipes=$_SERVER['REMOTE_ADDR'];
		//$tuos=$this->browserInfo['platform'];
		$tuos="";
		//$tunav=$this->browserInfo['browser'];
		$tunav="";
		$tuagent=$_SERVER['HTTP_USER_AGENT'];
		$tulang=User::getLanguage();
		
		$f = @fopen($log_file_p, 'a+');
		if (is_resource($f)){
			$UID=isset($_SESSION['user'])?$_SESSION['user']['id']:0;
			
			$linea="[ ".date("d/m/Y H:i:s")." ] [".$tuipes."] [".$tuos."] [".$tunav."] [".$tulang."] [ ".$tipotxt." ] [ ".str_pad($UID, 6)." ] [ ".str_pad($module, 4)." ] [ ".str_pad($task, 4)." ] [ ".str_pad($subtask, 4)." ] [ ".$scriptname." ] [ ".str_pad($scriptline, 4)." ] [ ".$_SERVER['REQUEST_URI']." ]  [".$tuagent."] ".$texto."\n";
			@fwrite($f,$linea); 
			@fclose($f);
			
			$this->buff[]=$linea;
			$this->yalogie=1;
			
		}
		$byten=@filesize($log_file_p);
		return $byten;

	}


	/**
    * Metodo encargado de logear los eventos a una tabla sql.
    *
    * @param script name
    * @param line
    * @return boolean
    */

	private function logToSql($scriptname,$scriptline,$module='',$task='',$subtask='',$tipo=5,$texto){
		if($this->enabled==0 || $this->enableSQL==0 || !$this->dbConnection) return false;
		
		$guardosession="";
		$guardobacktrace="";
		$guardorequest="";
		
		switch($tipo){

			case self::SQL_QUERY:
			if($this->loglevel[0]==0)
			return 1;
			$tipotxt=str_pad("SQL QUERY", 12);
			break;

			case self::SQL_ERROR:
			if($this->loglevel[1]==0)
			return 1;
			$tipotxt=str_pad("SQL ERROR", 12);
			//-------------------------------------
			ob_start();
			ob_implicit_flush(0);
			print_r($_SESSION);
			$guardosession=ob_get_contents();
			ob_end_clean();
			//--------------------------------------
			ob_start();
			ob_implicit_flush(0);
			debug_print_backtrace();
			$guardobacktrace=ob_get_contents();
			ob_end_clean();
			//--------------------------------------
			ob_start();
			ob_implicit_flush(0);
			echo "<strong>POST:</strong><br/>";
			if(isset($_POST))
			print_r($_POST);
			echo "<strong>GET:</strong><br/>";
			if(isset($_GET))
			print_r($_GET);
			$guardorequest=ob_get_contents();
			ob_end_clean();
			//--------------------------------------
			break;

			case 3:
			if($this->loglevel[2]==0)
			return 1;
			$tipotxt=str_pad("ERROR", 12);
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			print_r($_SESSION);
			$guardosession=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			debug_print_backtrace();
			$guardobacktrace=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			echo "<strong>POST:</strong><br/>";
			if(isset($_POST))
			print_r($_POST);
			echo "<strong>GET:</strong><br/>";
			if(isset($_GET))
			print_r($_GET);
			$guardorequest=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			break;

			case 4:
			if($this->loglevel[3]==0)
			return 1;
			$tipotxt=str_pad("DEBUG", 12);
			break;

			default:
			case 5:
			if($this->loglevel[4]==0)
			return 1;
			$tipotxt=str_pad("INFORMACION", 12);
			break;
			
			case 6:
			if($this->loglevel[5]==0)
			return 1;
			$tipotxt=str_pad("EXCEPTION", 12);
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			print_r($_SESSION);
			$guardosession=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			debug_print_backtrace();
			$guardobacktrace=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			ob_start();
			ob_implicit_flush(0);
			echo "<strong>POST:</strong><br/>";
			if(isset($_POST))
			print_r($_POST);
			echo "<strong>GET:</strong><br/>";
			if(isset($_GET))
			print_r($_GET);
			$guardorequest=ob_get_contents();
			ob_end_clean();
			//------------------------------------------------------
			break;
		}

		$tuipes=$_SERVER['REMOTE_ADDR'];
		//$tuos=$this->browserInfo['platform'];
		//$tunav=$this->browserInfo['browser'];
		$tuos="";
		$tunav="";
		$tuagent=$this->dbConnection->escapeString($_SERVER['HTTP_USER_AGENT']);
		$tulang=User::getLanguage();
		
		$guardorequest=$this->dbConnection->escapeString($guardorequest);
		$guardobacktrace=$this->dbConnection->escapeString($guardobacktrace);
		$guardosession=$this->dbConnection->escapeString($guardosession);
		
		$req_uri=$this->dbConnection->escapeString($_SERVER['REQUEST_URI']);
		$task=$this->dbConnection->escapeString($task);

		
		$subtask=$this->dbConnection->escapeString($subtask);

		//echo "PEPIN: ".$texto;
		$texto=$this->dbConnection->escapeString($texto);

		$UID=isset($_SESSION['user'])?$_SESSION['user']['id']:0;
		$domain=$_SERVER['SERVER_NAME'];
		 $SQLq="INSERT INTO ".$this->log_table." (log_date,log_ip,log_typetxt,log_type,user_id,log_domain, 
										log_module,log_task,log_subtask,log_scrip,log_line,
										log_url,log_text,log_os,log_nav,log_lang,log_agent,log_sessionvar,
										log_backtrace,log_request) VALUES 
		     (NOW(),'$tuipes','$tipotxt','$tipo','".$UID."','$domain','$module','$task',
		     '$subtask','$scriptname','$scriptline','".$req_uri."','$texto','$tuos','$tunav',
		     '$tulang','$tuagent',".($guardosession==""?"NULL":"'$guardosession'").",
		     ".($guardobacktrace==""?"NULL":"'$guardobacktrace'").",".($guardorequest==""?"NULL":"'$guardorequest'").")";
		$hay=$this->dbConnection->executeUpdate($SQLq, false);

		if($this->yalogie==0){
			$this->buff[]="[ ".date("d/m/Y H:i:s")." ] [".$tuipes."] [".$tuos."] [".$tunav."] [".$tulang."] [ ".$tipotxt." ] [ ".str_pad($UID, 6)." ] [ ".str_pad($module, 6)." ] [ ".str_pad($task, 4)." ] [ ".str_pad($subtask, 4)." ] [ ".$scriptname." ] [ ".str_pad($scriptline, 4)." ] [ ".$_SERVER['REQUEST_URI']." ] [".$tuagent."] ".$texto;
		}
		return 0;
	}
	
	/**
	 * Metodo general que llama a los metdos de logeo a texto y sql.
   	 * mismos parametros que las anteriores pero pasandole los campos de tabla y file.
	 *
	 * @param integer $type
	 * @param string $scriptname
	 * @param string $scriptline
	 * @param string $texto
	 * @param string $module
	 * @param string $task
	 * @param string $subtask
	 * @return boolean
	 */
	public function log($type = self::INFO, $scriptname = '', $scriptline = -1, $texto='', $module = '', $task='', $subtask='')
	{
		Parameter::check(array(
					"integer"	=> array($type, $scriptline),
					"string" 	=> array($scriptname, $texto, $module, $task, $subtask)
				), __METHOD__);
		
		$request = Request::init();
		if (!$module) $module = $request->module;
		if (!$task) $task = $request->task;
		if (!$subtask) $subtask = $request->subtask;

		$fileStatus = $sqlStatus = true;
		if ($this->enableFile) $fileStatus = $this->logToFile($scriptname,$scriptline,$module,$task,$subtask,$type,$texto);
		if ($this->enableSQL) $sqlStatus = $this->logToSql($scriptname,$scriptline,$module,$task,$subtask,$type,$texto);
		$this->yalogie=0;
		return $fileStatus && $sqlStatus;
	}


		/**
		 * Inicia TODO ( a sql y texto ) el sistema de logs
		 * puede usarse en cualquier momento para iniciarlo.
		 */
	public function start(){
		$this->enabled=1;
	}

		/**
		 * Para TODO ( a sql y texto ) el sistema de logs
		 * puede usarse en cualquier momento para frenarlo.
		 */
	public function stop(){
		$this->enabled=0;
	}

		/**
		 * Inicia logeo a archivo
		 * puede usarse en cualquier momento para iniciarlo.
		 */
	public function startFile(){
		$this->enableFile=1;
	}
		/**
		 * Frena logeo a archivo
		 * puede usarse en cualquier momento para frenarlo.
		 */
	public function stopFile(){
		$this->enableFile=0;
	}

		/**
		 * Inicia logeo a sql
		 * puede usarse en cualquier momento para iniciarlo.
		 */
	public function startSQL(){
		$this->enabled_1=0;
	}

		/**
		 * Frena logeo a sql
		 * puede usarse en cualquier momento para frenarlo.
		 */
	public function stopSQL(){
		$this->enableSQL=0;
	}

		/**
         * Setea el nivel de logeo del sistema.
         *
         * @param string $loglevelstr su formato es "1-0|1-0|1-0|1-0|1-0|1-0"
         * ejemplo: "100111"
         * El primer char activa o desactiva ( 1 o 0 ) las entradas de tipo "SQL QUERY" (1).
         * El segundo char activa o desactiva ( 1 o 0 ) las entradas de tipo "SQL ERROR" (2).
         * El tercer char activa o desactiva ( 1 o 0 ) las entradas de tipo "ERROR" (3).
         * El cuarto char activa o desactiva ( 1 o 0 ) las entradas de tipo "DEBUG" (4).
         * El quinto char activa o desactiva ( 1 o 0 ) las entradas de tipo "INFORMACION" (5).
         * El sexto char activa o desactiva ( 1 o 0 ) las entradas de tipo "EXCEPTION" (6).
         * 
         */
	public function setLogLevel($loglevelstr){
		if(strlen($loglevelstr)!=6)
		$this->loglevel="111111";
		$this->loglevel =$loglevelstr;
	}

		/**
         * Retorna el estado del sistema de logs.
         *
         * @return boolean
         */
	public function getStatus(){
		return $this->enabled;
	}

		/**
         * Retorna el loglevel del sistema
         *
         * @return string formato "101001" ( ver metodo loglevel )
         */
	public function getLogLevel(){
		return $this->loglevel;
	}

		/**
         * Recuerda el estado de cada sistema de logeo
         * ( general, texto, sql)
         * para hacer cambios en los estados y
         * despues poder recuperarlos con el metodo restoreStatus()
         *
         */
	public function saveStatus(){
		$this->rem_fi=$this->enableFile;
		$this->rem_sq=$this->enableSQL;
		$this->rem_st=$this->enabled;
	}

		/**
         * Setea los estados de logeo ( general, sql,texto ) a como
         * los habia recordado el metodo save_status()
         *
         */
	public function restoreStatus(){
		$this->enableFile=$this->rem_fi;
		$this->enableSQL=$this->rem_sq;
		$this->enabled=$this->rem_st;
	}

		/**
         * Imprime en pantalla los ultmimos $cuantos logs
         * guardados en el buffer.
         *
         * @param int $cuantos la cantidad de logs que queres imprimir
         */
	public function lastLogs($cuantos=0){
		if($cuantos==0){
			$desde=0;
		}else{
			$desde=count($this->buff)-$cuantos;
			if($desde<0)
			$desde=0;
		}
		for($i=$desde;$i<count($this->buff);$i++)
		echo $this->buff[$i]."<br/>\n";
	}


}

?>