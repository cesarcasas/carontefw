<?
	import("system.db.ResultSet");
	
	/**
	 * Representa un resultado de MySQL para las consultas de lectura como SELECT, SHOW y DESCRIBE
	 *
	 * @package system.db
	 */
	class MySQLResultSet extends ResultSet
	{
		/**
		 * @var mysqli_result
		 */
		protected $result;
		
		/**
		 * Constructor
		 *
		 * @param mysqli_result $result
		 * @param integer $rowCount
		 * @param integer $pageCount
		 */
		public function __construct(mysqli_result $result, $rowCount = -1, $pageCount = 0)
		{
			Parameter::check(array(
					"integer"	=> array($rowCount, $pageCount)
				), __METHOD__);
			
			parent::__construct($rowCount, $pageCount);
			$this->result = $result;
		}
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array asociativo e indexado en forma numerica
		 */
		public function fetchArray()
		{
			return $this->result->fetch_array();
		}
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array indexado en forma numerica
		 */
		public function fetchRow()
		{
			return $this->result->fetch_row();
		}
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array asociativo
		 */
		public function fetchAssoc()
		{
			return $this->result->fetch_assoc();
		}
		/**
		 * Obtener matriz resultado
		 *
		 * @param integer $resultType Tipo de resultado. Debe ser una de las constantes MYSQLI_*
		 * @return mixed[]
		 */
		public function fetchAll($resultType = MYSQLI_BOTH)
		{
			$data = array();
			while ($row = $this->result->fetch_array($resultType)) $data[] = $row;
			return $data;
		}
		/**
		 * Mover puntero en la matriz resultado
		 *
		 * @param integer $index indice comenzando de 0
		 * @return boolean
		 */
		public function dataSeek($index)
		{
			return $this->result->data_seek($index);
		}
		/**
		 * Obtener numero de filas del resultado.
		 * Para resultados paginados, devuelve el numero de filas de la pagina actual. Para eso esta la propiedad totalNumRows
		 *
		 * @return integer
		 */
		public function numRows()
		{
			return $this->result->num_rows;
		}
		/**
		 * Obtener numero de columnas en el resultado
		 *
		 * @return integer
		 */
		public function numFields()
		{
			return $this->result->field_count;
		}
		/**
		 * Liberar memoria asociada al resultado
		 *
		 * @return void
		 */
		public function free()
		{
			$this->result->close();
		}
		
		
	}
?>