<?
	import("system.gui.HTMLElement");
	
	/**
	 * Representa una lista html
	 * 
	 * @package system.gui
	 */
	final class HTMLList extends HTMLElement
	{
		/**
		 * @var array
		 */
		private $items = array();
		
		/**
		 * Constructor.
		 *
		 * @param string $id ID de la lista
		 * @param string $className Clase CSS de la lista
		 * @param array $items Array de items
		 */
		function __construct($id = '', $className, array $items = array())
		{
			Parameter::check(array(
					"string" 	=> array($id, $className)
				), __METHOD__);
				
			parent::__construct($id, $className);

			foreach ($items as $i) $this->addItem($i);
		}
		
		/**
		 * Agregar un item
		 *
		 * @param HTMLListItem $item
		 * @return void
		 */
		public function addItem(HTMLListItem $item)
		{
			$this->items[] = $item;
		}
		
		/**
		 * Obtener item segun el indice
		 *
		 * @param integer $index
		 * @return HTMLListItem
		 */
		public function getItem($index)
		{
			Parameter::check(array(
					"integer" 	=> $index
				), __METHOD__);
			
			if ($index < 0 || $index > count($this->items)) throw new IndexOutOfBoundsException();
			return $this->items[$index];
		}
		
		/**
		 * Expandir sublistas
		 *
		 * @return void
		 */
		public function expand()
		{
			foreach ($this->items as $item) $item->expand();
		}
		
		/**
		 * Ocultar sublistas
		 *
		 * @return void
		 */
		public function collapse()
		{
			foreach ($this->items as $item) $item->collapse();
		}
		
		/**
		 * Obtener html que representa la lista
		 *
		 * @return string
		 */
		public function getSource()
		{
			$html = '<ul';
			if ($this->id) $html .= ' id="'.$this->id.'"';
			if ($this->className) $html .= ' class="'.$this->className.'"';
			$html .= $this->getStyle().">";
			foreach ($this->items as $i) $html .= $i->getSource();
			$html .= '</ul>';
			return $html;
		}
	}
?>