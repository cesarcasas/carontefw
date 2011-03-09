<?
	import("system.utils.Session");
	
	/**
	 * Clase para manejar sesiones en la DB.
	 * Configura automaticamente la sesión con session_set_save_handler()
	 * Se debe llamar a session_start() despues de instanciar esta clase!
	 *
	 * @version 1.0
	 */
	class SessionManager extends Session {
		/**
		 * Constructor
		 *
		 * @param string $dbTable Nombre de la tabla en la DB donde se guardan las sesiones.
		 * @param integer $lifeTime Tiempo de duración de la sesión en segundos.
		 */
		public function __construct($dbTable, $lifeTime = 0) {
			parent::__construct($dbTable, $lifeTime);
			session_set_save_handler(
				array($this, "open"),
				array($this, "close"),
				array($this, "read"),
				array($this, "write"),
				array($this, "destroy"),
				array($this, "gc")
			);
		}
	}
?>