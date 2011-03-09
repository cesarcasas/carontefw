<?
	/**
	 * Interface base para las conexiones a origenes de datos
	 * 
	 * @package system.db
	 */
	interface DBConnection
	{
		/**
		 * Conectarse a una base de datos
		 *
		 * @param string $host Host
		 * @param string $user Usuario
		 * @param string $pass Password
		 * @param string $db Base de Datos
		 * @return void
		 * @throws SQLException
		 */
		public function connect($host, $user, $pass, $db = '');
		/**
		 * Seleccionar una base de datos
		 *
		 * @param string $dbName Nombre de la base de datos
		 * @return void
		 * @throws SQLException
		 */
		public function selectDB($dbName);
		/**
		 * Cierra la conexion a la db
		 *
		 * @return void
		 * @throws SQLException
		 */
		public function close();
		/**
		* Ejecutar query (Operaciones de lectura: SELECT, SHOW, DESCRIBE, etc)
		* 
		* @param string	$query Consulta SQL.
		* @return ResultSet Resultado.
		* @throws QueryException
		*/
		public function executeQuery($query);
		/**
		* Enviar query (Operaciones de escritura: UPDATE, DELETE, INSERT, REPLACE, etc)
		* 
		* @param string	$query consulta sql
		* @return integer resultado: numero de filas afectadas
		* @throws QueryException
		*/
		public function executeUpdate($query);
		/**
		 * Escapar una cadena para insertar en la db
		 *
		 * @param string $string
		 * @return string
		 */
		public function escapeString($string);
		/**
		 * Obtener ultimo id autoincrementable insertado en el ultimo query
		 *
		 * @return integer
		 */
		public function getLastInsertId();
		/**
		 * Establecer encoding para la conección con la DB.
		 *
		 * @param string $charset
		 * @return boolean
		 */
		public function setCharset($charset);
		/**
		 * Establecer auto-commit.
		 *
		 * @param boolean $autoCommit
		 * @return void
		 * @throws SQLException
		 */
		public function setAutoCommit($autoCommit);
		/**
		 * Hacer permanentes los ultimos cambios de la transacción
		 * 
		 * @return void
		 * @throws SQLException
		 */
		public function commit();
		/**
		 * Deshace los cambios de la ultima transacción.
		 * 
		 * @return void
		 * @throws SQLException
		 */
		public function rollback();
	}
?>