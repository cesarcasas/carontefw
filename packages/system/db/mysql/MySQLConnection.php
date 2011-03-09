<?
	import(	"system.db.DBConnection",
			"system.db.SQLException",
			"system.db.QueryException",
			"system.db.mysql.MySQLResultSet",
			"system.utils.Logger"
		);
	
	/**
	* Representa una coneccion con MySQL
	* 
	* @version 1.0
	* @package system.db
	*/
	class MySQLConnection extends Main implements DBConnection
	{


                var $fileSpoolerSQL;
		/**
		 * @var integer
		 */
		const DEFAULT_PORT = 3306;
		/**
		 * Array con instancias a conecciones
		 *
		 * @var MySQLConnection[]
		 */
		protected static $instances = array();
		/**
		 * @var mysqli
		 */
		protected $link = null;
		/**
		 * @var integer
		 */
		protected $port;
		
		/**
		 * Constructor. Se conecta automaticamente a la db
		 *
		 * @param string $host Host
		 * @param string $user Usuario
		 * @param string $pass Password
		 * @param string $db Base de Datos
		 * @param integer $port puerto
		 * @throws SQLException
		 */
		protected function __construct($host = '', $user = '', $pass = '', $db = '', $port = self::DEFAULT_PORT)
		{
			
			Parameter::check(array(
					"string" 	=> array($host, $user, $pass, $db),
					"integer" 	=> $port
				), __METHOD__);
			
				
			$this->port = $port;
			$this->connect($host, $user, $pass, $db);
		}


                public function setSpoolerSQLFile($file)
                {
                    $this->fileSpoolerSQL=$file;
                }
        
                public function AddSpoolerQuery($sql)
                {
                    file_put_contents($this->fileSpoolerSQL, $sql.";\r\n");
                }
                        
		/**
		 * Inicializar MySQLConnection. Devuelve siempre la misma instancia para cada host.
		 *
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $db
		 * @param integer $port
		 * @return MySQLConnection
		 */
		public static function init($host = '', $user = '', $pass = '', $db = '', $port = self::DEFAULT_PORT)
		{
			
			Parameter::check(array(
					"string" 	=> array($host, $user, $pass, $db),
					"integer" 	=> $port
				), __METHOD__);
			
				
			if (!isset(self::$instances[$host])) self::$instances[$host] = new self($host, $user, $pass, $db, $port);
			return self::$instances[$host];
		}

		/**
		 * Conectarse a una base de datos
		 *
		 * @param string $host Host
		 * @param string $user Usuario
		 * @param string $pass Password
		 * @param string $db Base de Datos
		 * @return void
		 * @throws SQLException Si hubo un error de conección.
		 */
		public function connect($host, $user, $pass, $db = '')
		{
			
			Parameter::check(array(
					"string" 	=> array($host, $user, $pass, $db)
				), __METHOD__);
			
			$link = @mysqli_connect($host, $user, $pass, $db, $this->port);
			
			if (!$link) throw new SQLException(mysqli_connect_error(), mysqli_connect_errno());
			$this->link = $link;
			
		}
		
		/**
		 * Seleccionar una base de datos
		 *
		 * @param string $dbName Nombre de la base de datos
		 * @return void
		 * @throws SQLException Si no se pudo seleccionar la DB.
		 */
		public function selectDB($dbName)
		{
			if (!@$this->link->select_db($dbName)) throw new SQLException($this->link->error, $this->link->errno);
		}
		
		/**
		 * Cierra la conexion a la db
		 *
		 * @return void
		 * @throws SQLException
		 */
		public function close()
		{
			if (!@$this->link->close()) throw new SQLException($this->link->error, $this->link->errno);
		}

		/**
		* Ejecutar query (SELECT, SHOW, DESCRIBE, etc)
		* 
		* @param string	$query Consulta SQL.
		* @param boolean $log Logear consultas sql
		* @param string $file Archivo 
		* @param integer $line Linea
		* @return MySQLResultSet Resultado.
		* @throws QueryException Si hubo un error en el query.
		*/
		public function executeQuery($sql, $log = false, $file = '', $line = -1)
		{
			Parameter::check(array(
					"string" 	=> array($sql, $file),
					"integer"	=> $line,
					"boolean"	=> $log
				), __METHOD__);
		
                       	
			$result = @$this->link->query($sql);
			if ($log) {
				Logger::init()->log(Logger::SQL_QUERY, $sql, $file, $line);
			}
			if (!is_object($result)) {
				if ($log) Logger::init()->log(Logger::SQL_ERROR, $sql, $file, $line);
				$error = !$this->link->errno ? "Provided query is not valid! Query must be a SELECT, SHOW, DESCRIBE or EXPLAIN" : $this->link->error;
				throw new QueryException($error, $this->link->errno, $sql);
			}
			return new MySQLResultSet($result);
		}
		
		/**
		 * Ejecutar query y paginar resultado (SELECT, SHOW, DESCRIBE, etc)
		 *
		 * @param string $sql Consulta SQL.
		 * @param integer $rowsPerPage Cantidad de registros por página a mostrar.
		 * @param mixed $actualPage Numero de página actual o nombre de la variable que contiene el numero de página actual. En ese caso, viene por $_GET
		 * @param boolean $log Logear consultas sql
		 * @param string $file Archivo 
		 * @param integer $line Linea
		 * @return MySQLResultSet Resultado
		 * @throws InvalidArgumentException Si la cantidad de registros por pagina es menor a cero. Si el nombre de la variable que contiene el numero de página es inválido.
		 * @throws QueryException Si hubo un error en el query.
		 */
		public function executePagedQuery($sql, $rowsPerPage = 10, $actualPage = 'p', $log = false, $file = '', $line = -1, $page='')
		{
			
			Parameter::check(array(
					"string" 	=> array($sql, $file),
					"integer"	=> array($rowsPerPage, $line),
					"boolean"	=> $log
				), __METHOD__);

			if ($rowsPerPage < 1) throw new InvalidArgumentException("Rows per page must be an integer greater than 0");

			if (stripos($sql, "SQL_CALC_FOUND_ROWS") === false)
				$sql = preg_replace("#^\s*SELECT(.*?)#i", "SELECT SQL_CALC_FOUND_ROWS $1", $sql);
			
			if (is_string($actualPage)) $actualPage = isset($_GET[$actualPage]) ? (int)$_GET[$actualPage] : 1;
			$actualPage=$page=='' ? $actualPage : $page;
			//print_r($actualPage);
			if ($actualPage < 1) $actualPage = 1;
			
			$recStart = $actualPage * $rowsPerPage - $rowsPerPage;
			$recEnd = $rowsPerPage;
			
			$sql .= " LIMIT $recStart, $recEnd";
			
			$result = @$this->link->query($sql);
			
			if ($log) {
				Logger::init()->log(Logger::SQL_QUERY, $sql, $file, $line);
			}
			if (!is_object($result))
			{
				if ($log) Logger::init()->log(Logger::SQL_ERROR, $sql, $file, $line);
				$error = !$this->link->errno ? "Provided query is not valid! Query must be a SELECT, SHOW, DESCRIBE or EXPLAIN" : $this->link->error;
				throw new QueryException($error, $this->link->errno, $sql);
			}
			
			$tempResult = $this->executeQuery("SELECT FOUND_ROWS()");
			$tempData = $tempResult->fetchRow();
			$rowCount = (int)$tempData[0];
			$pageCount = (int)ceil($rowCount / $rowsPerPage);
			
			// validamos pagina y reenviamos query si es invalida.
			/*if ($actualPage >= $numPages)
			{
				$recStart = $pageCount * $rowsPerPage - $rowsPerPage;
				$result = @$this->link->query(preg_replace("#LIMIT.*?$#", "LIMIT $recStart, $recEnd", $sql));
			}*/
			
			return new MySQLResultSet($result, $rowCount, $pageCount);
		}
		
		/**
		* Enviar query a la db (UPDATE, DELETE, INSERT, REPLACE, etc)
		* 
		* @param string	$query Consulta SQL
		* @param boolean $log Logear consultas sql
		* @param string $file Archivo 
		* @param integer $line Linea
		* @return integer Numero de filas afectadas
		* @throws QueryException Si hubo un error en el query.
		*/
		public function executeUpdate($sql, $log = false, $file = '', $line = -1)
		{
			Parameter::check(array(
					"string" 	=> array($sql, $file),
					"integer"	=> $line,
					"boolean"	=> $log
				), __METHOD__);
			
			$result = @$this->link->query($sql);
			if ($log) {
				Logger::init()->log(Logger::SQL_QUERY, $sql, $file, $line);
			}
			if (!$result) {
				if ($log) Logger::init()->log(Logger::SQL_ERROR, $sql,  $file, $line);
				throw new QueryException($this->link->error, $this->link->errno, $sql);
			}
			return $this->link->affected_rows;
		} 
        
		/**
		 * Escapar % y _ para usar en LIKE
		 *
		 * @param string $str Cadena a escapar
		 * @return string Cadena escapada
		 */
		public function escapeLike($str)
		{
			Parameter::check(array(
					"string" 	=> $str,
				), __METHOD__);
			
			return str_replace(array('%', '_'), array('\%', '\_'), $str);
		}
		
		/**
		 * Escapar una cadena para insertar en la db
		 *
		 * @param string $str cadena
		 * @return string
		 */
		public function escapeString($str)
		{
		   $str = (string)$str;
			Parameter::check(array(
					"string" 	=> $str,
				), __METHOD__);
			
			return $this->link->real_escape_string($str);
		}
		
		/**
		 * Limpiar cadena para insertar en la db (escapa: < > " ' &)
		 * 
		 * @param string $str cadena a limpiar
		 * @return string cadena bien limpia a prueba de chicos malos
		 */
		public function clean($str)
		{
			return $this->escapeString(htmlspecialchars(trim($str), ENT_QUOTES));
		}
		
		/**
		 * Obtener ultimo id autoincrementable insertado en el ultimo query
		 *
		 * @return integer
		 */
		public function getLastInsertId()
		{
			return (int)$this->link->insert_id;
		}
		/**
		 * Establecer charset para la conección
		 *
		 * @param string $charset
		 * @return boolean
		 */
		public function setCharset($charset)
		{
			return $this->link->set_charset($charset);
		}
		/**
		 * Establecer auto-commit.
		 *
		 * @param boolean $autoCommit
		 * @return void
		 * @throws SQLException
		 */
		public function setAutoCommit($autoCommit)
		{
			if (!$this->link->autocommit($autoCommit))
				throw new SQLException($this->link->error, $this->link->errno);
		}
		/**
		 * Hacer permanentes los ultimos cambios de la transacción.
		 * 
		 * @return void
		 * @throws SQLException
		 */
		public function commit()
		{
			if (!$this->link->commit())
				throw new SQLException($this->link->error, $this->link->errno);
		}
		/**
		 * Deshace los cambios de la ultima transacción.
		 * 
		 * @return void
		 * @throws SQLException
		 */
		public function rollback()
		{
			if (!$this->link->rollback())
				throw new SQLException($this->link->error, $this->link->errno);
		}
	}
?>
