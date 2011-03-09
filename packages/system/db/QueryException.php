<?
	import("system.db.SQLException");
	
	/**
	 * Exception lanzada cuando se produce un error en una consulta.
	 *
	 * @exception 
	 * @package system.db
	 */
	class QueryException extends SQLException
	{
		/**
		 * @var string
		 */
		protected $query;
		
		/**
		 * Constructor
		 *
		 * @param string $msg Mensaje
		 * @param string $errorCode Codigo de error que reportó la base de datos.
		 * @param string $query Consulta enviada.
		 */
		function __construct($msg = '', $errorCode = 0, $query = '')
		{
			parent::__construct($msg, $errorCode);
			$this->query = $query;
		}
		/**
		 * Obtener consulta .
		 *
		 * @return string
		 */
		public function getQuery()
		{
			return $this->query;
		}
		/**
		 * Obtener objeto como un string.
		 * 
		 * @return string
		 */
		public function __toString()
		{
			$query = preg_replace("#^\s+#m", "", $this->query);
			return $this->getMessage()."\nError code: ".$this->getErrorCode()."\nQuery:\n".$query;
		}
	}
?>