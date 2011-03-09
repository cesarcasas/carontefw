<?
	import("system.gui.HTMLElement");
	
	/**
	 * Representa un item de una lista html
	 * @package system.gui
	 */
	final class HTMLListItem extends HTMLElement
	{
		/**
		 * @var string
		 */
		private $text;
		/**
		 * @var array
		 */
		private $lists = array();
		
		/**
		 * Constructor.
		 *
		 * @param string $id ID de la lista
		 * @param string $text Texto del item
		 * @param array $lists Array de items
		 */
		function __construct($id = '', $text = '', array $lists = array())
		{
			Parameter::check(array(
					"string" 	=> array($id, $text)
				), __METHOD__, __FILE__, __LINE__);
				
			parent::__construct($id);
			
			$this->text = $text;
			foreach ($lists as $list) $this->addList($list);
		}
		
		/**
		 * Agregar una lista
		 *
		 * @param HTMLList $list
		 * @return void
		 */
		public function addList(HTMLList $list)
		{
			$this->lists[] = $list;
		}
		/**
		 * Modificar texto/html del item.
		 *
		 * @param string $text
		 * @return void
		 */
		public function setText($text)
		{
			$this->text = $text;
		}
		/**
		 * Obtener texto/html del item.
		 *
		 * @return string
		 */
		public function getText()
		{
			return $this->text;
		}
		/**
		 * Expandir sublistas
		 *
		 * @return void
		 */
		public function expand()
		{
			foreach ($this->lists as $list) $list->setStyle(array('display' => ''));
		}
		/**
		 * Ocultar sublistas
		 *
		 * @return void
		 */
		public function collapse()
		{
			foreach ($this->lists as $list) $list->setStyle(array('display' => 'none'));
		}
		
		/**
		 * Obtener html que representa la lista
		 *
		 * @return string
		 */
		public function getSource()
		{
			$html = "\n\t<li";
			if ($this->id) $html .= ' id="'.$this->id.'"';
			$html .= $this->getStyle().'>'.$this->text;
			foreach ($this->lists as $list) $html .= $list->getSource();
			$html .= '</li>';
			return $html;
		}
	}
?>