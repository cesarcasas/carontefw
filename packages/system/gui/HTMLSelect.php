<?
	import("system.gui.HTMLElement");
	
	/**
	 * Representa una lista html
	 * 
	 * @package system.gui
	 */
	final class HTMLSelect extends HTMLElement
	{
		private $name;
		private $defaultValues = array();
		private $multiple;
		/**
		 * @var string[]
		 */
		private $options = array();
		
		/**
		 * Constructor.
		 *
		 * @param string $id
		 * @param string $name
		 * @param string[] $options Array de opciones, las claves son el value. Si no se proveen claves el valor es igual al texto.
		 * @param mixed $defaultValues Valor o array de valores (para select multiples) seleccionados por default
		 * @param boolean $multiple
		 * @param array $attributes
		 */
		function __construct($id = '', $name = '',  array $options = array(), $defaultValues = '', $multiple = false, array $attributes = array())
		{
			Parameter::check(array(
					"string" 	=> array($id, $name)
				), __METHOD__);
				
			parent::__construct($id);

			$this->name = $name;
			$this->defaultValues = (array)$defaultValues;
			$this->multiple = $multiple;
			foreach ($options as $o) $this->addOption($o);
			foreach ($attributes as $name => $value) $this->setAttribute($name, $value);
		}
		
		/**
		 * Agregar una opcion
		 *
		 * @param string[] $option
		 * @return void
		 */
		public function addOption($option)
		{
			$this->options[] = is_array($option) ? $option : array($option => $option);
		}
		/**
		 * Agregar opciones con las opciones dentro del rango
		 *
		 * @param integer $start
		 * @param integer $end
		 * @param integer $increment
		 * @return void
		 * @deprecated Usar range() nativa de PHP
		 */
		public function setOptionRange($start = 0, $end = 10, $increment = 1)
		{
			for ($i=$start; $i<$end; $i+=$increment) $this->addOption(array($i => $i));
		}
		
		/**
		 * Obtener html que representa el select
		 *
		 * @return string
		 */
		public function getSource()
		{
			$name = $this->name ? $this->name : $this->id ? $this->id : '';
			$html = '<select name="'.$name.'"';
			if ($this->id) $html .= ' id="'.$this->id.'"';
			if ($this->className) $html .= ' class="'.$this->className.'"';
			if ($this->multiple) $html .= ' multiple="multiple"';
			$html .= $this->getStyle().$this->getAttributes().">";
			foreach ($this->options as $opt)
			{
				$v = key($opt);
				$t = $opt[$v];
				$selected = in_array($v, $this->defaultValues) ? ' selected="selected"' : '';
				$html .=<<<HTML
				<option value="$v"$selected>$t</option>
HTML;
			}
			$html .= '</select>';
			return $html;
		}
	}
?>