<?
	/**
	 * Exception lanzada cuando hubo un error de conexion con la db
	 *
	 * @exception 
	 * @package system.db
	 */
	class SQLException extends Exception
	{
		/**
		 * @var integer
		 */
		protected $errorCode;
		
		/**
		 * Constructor
		 *
		 * @param string $msg Mensaje
		 * @param integer $errorCode Codigo de error que reportó la base de datos.
		 */
		function __construct($msg = '', $errorCode = 0)
		{
			parent::__construct($msg);
			$this->errorCode = $errorCode;
		}
		/**
		 * Obtener mensaje de error de la DB.
		 *
		 * @return string
		 */
		public function getErrorCode()
		{
			return $this->errorCode;
		}
		/**
		 * Obtener objeto como un string.
		 * 
		 * @return string
		 */
		public function __toString()
		{
			return $this->getMessage()."\nError code: ".$this->getErrorCode();
		}
	}
?>