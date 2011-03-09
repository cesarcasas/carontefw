<?
	/**
	 * Clase para manejar conecciones a bases de datos.
	 * 
	 * Soporta multiples bases de datos para operaciones de lectura, escritura y búsqueda.
	 * Utiliza SQLite para almacenar información sobre cada servidor.
	 * @package system.db
	 */
	final class DB extends Main
	{
		/**
		 * Operación de lectura
		 * @var integer
		 */
		const READ = 0;
		/**
		 * Operación de escritura
		 * @var integer
		 */
		const WRITE = 1;
		/**
		 * Operación de búsqueda
		 * @var integer
		 */
		const SEARCH = 2;
		/**
		 * @var string
		 */
		const MYSQL_ENGINE = "mysql";
		/**
		 * @var DB
		 */
		private static $instance = null;
		/**
		 * @var DBConnection[]
		 */
		private $connections = array(
			self::READ => null,
			self::WRITE	=> null,
			self::SEARCH => null
		);
		/**
		 * @var resource
		 */
		private $sqliteConn = null;
		
                private $fileSpoolerSQL;

                
		/**
		 * Constructor. Esta clase se debe instanciar mediante el método init()
		 * 
		 * @param string $configFile Path al archivo donde se almacena config sobre las db's.
		 * @throws SQLException Si no se pudo conectar a SQLite
		 */
		private function __construct($configFile = "")
		{
			$this->sqliteConn = @sqlite_open($configFile, 0777, $error);
			if (!$this->sqliteConn) throw new SQLException("Could not connect to SQLite database: $error");
		}
		/**
		 * Obtener instancia de esta clase.
		 *
		 * @param string $configFile Path al archivo donde se almacena config sobre las db's.
		 * @return DB
		 * @throws SQLException Si no se pudo conectar a SQLite.
		 */
		public static function init($configFile = "")
		{
			Parameter::check(array(
					"string" 	=> $configFile
				), __METHOD__);
			
			if (!self::$instance) self::$instance = new self($configFile);
			return self::$instance;
		}
		/**
		 * "Factory" para obtener conección a un servidor de base de datos dependiendo del engine especificado en la constante DB_ENGINE.
		 * 
		 * Devuelve la misma instancia de conección para un determinado host.
		 * Si se omiten los parametros se utilizan las constantes DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE y DB_PORT
		 *
		 * @param string $hostname
		 * @param string $username
		 * @param string $password
		 * @param string $dbname
		 * @param integer $port
		 * @return DBConnection
		 * @throws Exception Si el motor de DB especificado es inválido o no es soportado.
		 * @throws SQLException Si no se pudo obtener la conexión.
		 */
		public static function getConnection($hostname = DB_HOST, $username = DB_USERNAME, $password = DB_PASSWORD, $dbname = DB_DATABASE, $port =
                        DB_PORT, $serverList=array(), $serverLog=false)
		{
			
			
			Parameter::check(array(
					"string" 	=> array($hostname, $username, $password, $dbname),
					"integer" 	=> $port
				), __METHOD__);
			
			switch (DB_ENGINE)
			{
				case self::MYSQL_ENGINE:
                                        if(is_array($serverList) && count($serverList)>0){
                                        	

                                            $totalServers=count($serverList);
                                            $mem=new SMemcache();
                                            $actualServer=$mem->get("SERVER_DB_COLLECTION");
                                            if(is_array($actualServer) && isset($actualServer[0])) $actualServer=(int)$actualServer[0];
                                            else
                                            {
                                                $actualServer=0;  
                                            }

                                               if($actualServer==$totalServers-1) $actualServer=0;
                                               else
                                               {
                                                   $actualServer++;  
                                               }
                                               if($actualServer>count($serverList)) $actualServer=0;

                                                $serverConf=$serverList[$actualServer];
                                                /*$serverList[]=array('ServerHost'=>'localhost', 'ServerUser' => 'root', 'ServerPassword' => '', 'ServerDatabas'
                                                        =>'respuestas_new');*/

                                                $mem->push("SERVER_DB_COLLECTION", array($actualServer), 0);

                                                if($serverLog)
                                                    file_put_contents("/tmp/frame2SQL.log", serialize($serverConf)."\r\n");

                                                    
					        return MySQLConnection::init($serverConf['ServerHost'], $serverConf['ServerUser'], $serverConf['ServerPassword']
                                                    , $serverConf['ServerDatabase'], 3306);


                                        }
					import("system.db.mysql.MySQLConnection");
					
					return MySQLConnection::init($hostname, $username, $password, $dbname, $port);
                                        break;
				default:
					throw new Exception("Specified database engine does not exist!");
			}
		}
		/**
		 * Obtener conección para el tipo de operacion especificada.
		 * La operación debe ser una de las constantes READ, WRITE o SEARCH.
		 *
		 * @param integer $type
		 * @return DBConnection NULL si no existe una conección de este tipo.
		 */
		public function getConnectionForOp($type)
		{
			return $this->connections[$type];
		}
		/**
		 * Agregar servidor de base de datos (DBMS)
		 *
		 * @param integer $type Tipo de operaciones que va a recibir. Debe ser una de las siguientes constantes: READ, WRITE, SEARCH.
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $dbname
		 * @param integer $port
		 * @return void
		 * @throws SQLException
		 */
		public function addDBMS($type, $hostname, $username, $password, $dbname, $port = DB_PORT)
		{
			Parameter::check(array(
					"integer" 	=> array($type, $port),
					"string"	=> array($hostname, $username, $password, $dbname)
				), __METHOD__);
			
			/*$sql = "CREATE TABLE [DBMS] (
				[hostname] VARCHAR(150)  NULL,
				[username] VARCHAR(50)  NULL,
				[password] VARCHAR(50)  NULL,
				[dbname] VARCHAR(50)  NULL,
				[port] INTEGER DEFAULT '3306' NULL,
				[type] INTEGER DEFAULT '1' NULL
			)";
			$result = @sqlite_unbuffered_query($sql, $this->sqliteConn);
			if (!$result) throw new SQLException("Could not create DBMS Table!\n".sqlite_error_string(sqlite_last_error($this->sqliteConn)));
			*/
			$sql = "REPLACE INTO DBMS (
						hostname, username,
						password, dbname, port, type
					) VALUES (
						'".sqlite_escape_string($hostname)."', '".sqlite_escape_string($username)."',
						'".sqlite_escape_string($password)."', '".sqlite_escape_string($dbname)."',	$port, $type
					)";
			$result = @sqlite_unbuffered_query($sql, $this->sqliteConn);
			if (!$result) throw new SQLException("Could not add database '$hostname'!\n".sqlite_error_string(sqlite_last_error($this->sqliteConn)));
		}
	

                
                /**
		 * Conectarse a un servidor segun tipo de operación.
		 *
		 * @param integer $type
		 * @return void
		 * @throws Exception Si no hay servidores para este tipo de operación.
		 * @throws SQLException Si no se pudo obtener la conección a la DB.
		 */
		public function connect($type = self::READ)
		{
			if ($this->connections[$type] !== null) return;
			
			$sql = "SELECT *
					FROM DBMS
					WHERE type = $type";
			$result = @sqlite_query($sql, $this->sqliteConn);
			if (!$result) throw new SQLException("Could not select database for connection!\n".sqlite_error_string(sqlite_last_error($this->sqliteConn)));
			$dbs = sqlite_fetch_all($result, SQLITE_ASSOC);
			$dbCount = count($dbs);
			if ($dbCount == 0) throw new Exception("No servers found for this operation!");
			shuffle($dbs);
			// iterar sobre cada servidor e intentar la conexion
			for ($i=0; $i<$dbCount; $i++)
			{
				$db = $dbs[$i];
				try {
					$this->connections[$type] = self::getConnection($db['hostname'], $db['username'], $db['password'], $db['dbname'], (int)$db['port']);
					break;
				} catch (SQLException $e) {
					if ($i == ($dbCount - 1)) throw $e;
					continue;
				}
			}
		}
		/**
		 * Ejecutar query de lectura. Utiliza el metodo executeQuery del objeto DBConnection.
		 *
		 * @param string $sql
		 * @return ResultSet
		 * @throws SQLException Si hubo un error en la conexión.
		 * @throws QueryException Si hubo un error en la consulta enviada.
		 */
		public function executeQuery($sql)
		{
			if (!$this->connections[self::READ]) $this->connect(self::READ);
			return $this->connections[self::READ]->executeQuery($sql);
		}
		/**
		 * Ejecutar query de escritura. UPDATE, INSERT, DELETE, etc.
		 *
		 * @param string $sql
		 * @return integer Numero de filas afectadas.
		 * @throws SQLException Si hubo un error en la conexión.
		 * @throws QueryException Si hubo un error en la consulta enviada.
		 */
		public function executeUpdate($sql)
		{
			if (!$this->connections[self::WRITE]) $this->connect(self::WRITE);
			return $this->connections[self::WRITE]->executeUpdate($sql);
		}
		/**
		 * Ejecutar query de búsqueda. Utiliza el metodo executeQuery del objeto DBConnection.
		 *
		 * @param string $sql
		 * @return ResultSet
		 * @throws SQLException Si hubo un error en la conexión.
		 * @throws QueryException Si hubo un error en la consulta enviada.
		 */
		public function executeSearch($sql)
		{
			if (!$this->connections[self::SEARCH]) $this->connect(self::SEARCH);
			return $this->connections[self::SEARCH]->executeQuery($sql);
		}
		/**
		 * Escapar cadena de caracteres para insertar en la DB.
		 *
		 * @param string $str
		 * @return string
		 */
		public function escapeString($str)
		{
			return $this->connections[self::READ]->escapeString($str);
		}
		
		/**
		* Create SQL insert/replace statement. Uses default DB connection for escaping strings.
		* 
		* @param string $table table name
		* @param array  $data data, keys are column names and values are column values
		* @param boolean $replace Use REPLACE instead of INSERT
		* @return string Sql query
		*/         
		public static function createSQLInsert($table, $data, $replace = false)
		{
			$db = self::getConnection();
			$command = $replace ? 'REPLACE' : 'INSERT';
			$query = "$command INTO $table (";
			while (list($columns, ) = each($data)) {
				$query .= $columns . ', ';
			}
			$query = substr($query, 0, -2) . ') VALUES (';
			reset($data);
			foreach ($data as $col => $value) {
				if (is_numeric($value)) $query .= $value.", ";
				else if ($value === null) $query .= 'NULL, ';
				else if (strtoupper($value) == 'NOW()') $query .= 'NOW(), ';
				else if (is_string($value)) $query .= "'".$db->escapeString($value)."', ";
			}
			$query = substr($query, 0, -2) . ')';
			return $query;                             
		}
		/**
		* Create an SQL UPDATE statement. Uses default DB connection for escaping strings.
		* 
		* @param string $table table name
		* @param array  $data data, keys are column names and values are column values
		* @param string $where SQL WHERE
		* @return string Sql query
		*/         
		public static function createSQLUpdate($table, $data, $where = '')
		{
			$db = self::getConnection();
			$query = "UPDATE $table SET ";
			foreach ($data as $col => $value) {
				if (is_numeric($value)) $query .= "$col = $value, ";
				else if ($value === null) $query .= "$col = NULL, ";
				else if (strtoupper($value) == 'NOW()') $query .= "$col = NOW(), ";
				else if (is_string($value)) $query .= "$col = '".$db->escapeString($value)."', ";
			}
			if ($where=='') $query = substr($query, 0, -2);
			else $query = substr($query, 0, -2) . ' WHERE ' . $where;
			return $query;
		}
	}
?>
