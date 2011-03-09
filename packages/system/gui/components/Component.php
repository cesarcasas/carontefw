<?php
	import("system.gui.components.ComponentException");
	
	/**
	 * Clase base que representa un componente.
	 * 
	 * @package system.gui.components
	 */
	abstract class Component extends Main
	{
		/**
		 * Array con archivos requeridos
		 *
		 * @var string[]
		 */
		protected $requiredFiles = array(
			'css'	=> array(),
			'js'	=> array()
		);
		/**
		 * Codigo a ejecutarse cuando se carga la página
		 *
		 * @var string
		 */
		protected $jsLoader;
		/**
		 * Array con variables javascript globales
		 *
		 * @var mixed[]
		 */
		protected $jsVars = array();
		/**
		 * Nombre del componente
		 *
		 * @var string
		 */
		protected $name;
		/**
		 * ID del componente (unico)
		 *
		 * @var string
		 */
		protected $id;
		/**
		 * Ancho del componente
		 *
		 * @var integer
		 */
		protected $width;
		/**
		 * Alto del componente
		 *
		 * @var integer
		 */
		protected $height;
		
		/**
		 * Constructor. Carga automaticamente los css/js default para el componente (si existen).
		 *
		 * @param string $id Debe ser un ID alfanumérico valido como variable PHP.
		 * @throws InvalidArgumentException Si el ID no es válido.
		 */
		public function __construct($id)
		{
			if (!preg_match("#^[a-z_]\w*$#i", $id))
				throw new InvalidArgumentException("Component ID must be a valid variable name!");
			
			if (!isset($_SESSION)) @session_start();
			if (!isset($_SESSION['__COMPONENTS__'])) $_SESSION['__COMPONENTS__'] = array();
			$_SESSION['__COMPONENTS__'][$id] = $this;
			$this->name = get_class($this);
			$this->id = $id;
			
			// cargar automaticamente si existen los css/js
			if (file_exists(APP_COMMON_CSS.'/components/'.$this->name.'.css'))
				$this->requiredFiles['css'][] = 'components.'.$this->name;
			if (file_exists(APP_COMMON_JS.'/components/'.$this->name.'.js'))
			{
				$this->requiredFiles['js'][] = 'components.'.$this->name;
				$this->addJSVar($this->id, null);
				$this->jsLoader = "_Components.create('{$this->name}', '$id')";
			}
		}
		/**
		 * Establecer dimensiones del componente
		 *
		 * @param integer $width
		 * @param integer $height
		 * @return void
		 */
		public function setSize($width, $height = 0)
		{
			$this->width = $width;
			if ($height) $this->height = $height;
		}
		public function addJSVar($name, $value)
		{
			$this->jsVars[$name] = $value;
		}
		public function getName()
		{
			return $this->name;
		}
		public function getID()
		{
			return $this->id;
		}
		public function getCSS()
		{
			return $this->requiredFiles['css'];
		}
		public function getJS()
		{
			return $this->requiredFiles['js'];
		}
		public function getJSLoader()
		{
			return $this->jsLoader;
		}
		public function getJSVars()
		{
			return $this->jsVars;
		}
		public function __toString()
		{
			return $this->getSource();
		}
		/**
		 * Obtener JS que carga dinamicamente los archivos JS/CSS requeridos e inicializa el componente.
		 *
		 * @return string
		 */
		public function getAJAXLoader()
		{
			$html = '
			<script type="text/javascript">
				if (typeof '.$this->id.' == \'undefined\') '.$this->id.' = null;
				_Components.loadJS('.json_encode($this->getJS()).', function() { '.$this->getJSLoader().' });
				_Components.loadCSS('.json_encode($this->getCSS()).');
			</script>';
			return $html;
		}
		/**
		 * Establecer funcion que se ejecuta cuando el componente es cargado mediante AJAX.
		 *
		 * @param string $function
		 * @return void
		 */
		public function setAJAXOnload($function)
		{
			$this->jsLoader .= ";($function).apply({$this->id});";
		}
		/**
		 * Obtener código fuente del componente
		 * 
		 * @return string
		 */
		abstract public function getSource();
	}
?>