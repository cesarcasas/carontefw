<?
	/**
	 * Representa un resultado (SELECT, SHOW, DESCRIBE) de una base de datos.
	 * 
	 *
	 * @package system.db
	 */
	abstract class ResultSet extends Main
	{
		/**
		 * Numero de paginas. 
		 * @var integer
		 */
		public $pageCount = 0;
		/**
		 * Número total de registros sin tener en cuenta la paginación.
		 * @var integer
		 */
		public $totalNumRows = -1;
		
		/**
		 * Constructor
		 *
		 * @param integer $rowCount Numero de filas del resultado sin tener en cuenta la paginación. -1 indica resultado no paginado.
		 * @param integer $pageCount Cantidad de paginas totales.
		 * @throws InvalidArgumentException
		 */
		public function __construct($rowCount = -1, $pageCount = 0)
		{
			Parameter::check(array(
					"integer"	=> array($rowCount, $pageCount)
				), __METHOD__);
			
			$this->totalNumRows = $rowCount;
			$this->pageCount = $pageCount;
			
			if ($rowCount < -1) throw new InvalidArgumentException("Row count must be an integer greater or equal than -1");
			if ($pageCount < 0) throw new InvalidArgumentException("Page count must be an integer greater than -1");
		}
		
		function __destruct()
		{
			$this->free();
		}
		/**
		 * Obtener paginador. Utilizar la clase PageScroller en su lugar!!!
		 *
		 * @param integer $range
		 * @param string $style
		 * @param boolean $showNextPrev
		 * @return string
		 * @deprecated Utilizar PageScroller.
		 */
		public function getScroller($range = 6, $style = 'scroller', $showNextPrev = true)
		{
			import("system.db.PageScroller");
			$sc = new PageScroller($this, 'p');
			return $sc->getScroller($range, $style, $showNextPrev);
		}
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array asociativo e indexado en forma numerica
		 */
		abstract public function fetchArray();
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array indexado en forma numerica
		 */
		abstract public function fetchRow();
		/**
		 * Obtener fila de la matriz resultado
		 *
		 * @return mixed[] Array asociativo
		 */
		abstract public function fetchAssoc();
		/**
		 * Obtener matriz resultado
		 *
		 * @param integer $resultType Tipo de resultado. Debe ser una de las constantes MYSQLI_*
		 * @return mixed[]
		 */
		abstract public function fetchAll($resultType = MYSQLI_BOTH);
		/**
		 * Mover puntero en la matriz resultado
		 *
		 * @param integer $index indice comenzando de 0
		 * @return boolean
		 */
		abstract public function dataSeek($index);
		/**
		 * Obtener numero de filas del resultado.
		 * Para resultados paginados, devuelve el numero de filas de la pagina actual. Para eso esta la propiedad totalNumRows
		 *
		 * @return integer
		 */
		abstract public function numRows();
		/**
		 * Obtener numero de columnas en el resultado
		 *
		 * @return integer
		 */
		abstract public function numFields();
		/**
		 * Liberar memoria asociada al resultado
		 *
		 * @return void
		 */
		abstract public function free();
	}
?>