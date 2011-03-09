<?
	/**
	 * Representa un elemento HTML
	 * 
	 * @package system.gui
	 */
	abstract class HTMLElement extends Main
	{
		/**
		 * @var string
		 */
		protected $id;
		/**
		 * @var string
		 */
		protected $className;
		/**
		 * @var string[]
		 */
		protected $styles = array();
		/**
		 * @var string[]
		 */
		protected $attributes = array();
		
		/**
		 * Constructor.
		 *
		 * @param string $id ID del elemento
		 * @param string $className Clase CSS
		 */
		function __construct($id = '', $className = '')
		{
			Parameter::check(array(
					"string" 	=> array($id, $className)
				), __METHOD__, __FILE__, __LINE__);
				
			$this->id = $id;
			$this->className = $className;
		}
		/**
		 * Obtener html que representa al elemento. Se debe redefinir para cada elemento.
		 *
		 * @return string
		 */
		abstract public function getSource();
		/**
		 * Modificar ID
		 *
		 * @param string $id
		 * @return void
		 */
		public function setId($id)
		{
			Parameter::check(array(
					"string" 	=> $id
				), __METHOD__, __FILE__, __LINE__);
				
			$this->id = $id;
		}
		/**
		 * Modificar clase
		 *
		 * @param string $className
		 * @return void
		 */
		public function setClassName($className)
		{
			Parameter::check(array(
					"string" 	=> $className
				), __METHOD__, __FILE__, __LINE__);
				
			$this->className = $className;
		}
		/**
		 * Establecer atributo
		 *
		 * @param string $name
		 * @param string $value
		 * @return void
		 */
		public function setAttribute($name, $value)
		{
			$this->attributes[$name] = htmlspecialchars($value, ENT_COMPAT);
		}
		/**
		 * Obtener atributos de elemento como string.
		 *
		 * @return string
		 */
		public function getAttributes()
		{
			$attr = array();
			foreach ($this->attributes as $name => $value) $attr[] = $name.'="'.$value.'"';
			return empty($attr) ? '' : " ".implode(" ", $attr);
		}
		/**
		 * Obtener clase
		 *
		 * @return string
		 */
		public function getClassName()
		{
			return $this->className;
		}
		/**
		 * Modificar estilos del elemento.
		 *
		 * @param string[] $styles La clave es la propiedad y el valor es el valor de la propiedad.
		 * @return void
		 */
		public function setStyle(array $styles)
		{
			foreach ($styles as $property => $value) $this->styles[$property] = $value;
		}
		/**
		 * Obtener estilos del elemento como atributo 'style'
		 *
		 * @return string
		 */
		public function getStyle()
		{
			$styles = array();
			foreach ($this->styles as $property => $value) $styles[] = "$property:$value";
			return empty($styles) ? '' : ' style="'.implode(";", $styles).'"';
		}
		/**
		 * Imprimir el objeto
		 *
		 * @return string
		 */
		public function __toString()
		{
			return $this->getSource();
		}
	}
?>