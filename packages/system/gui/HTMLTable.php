<?
	import("system.gui.HTMLElement");
	
	/**
	 * Representa una tabla HTML
	 * 
	 * @package system.gui
	 */
	final class HTMLTable extends HTMLElement
	{
		/**
		 * @var mixed[]
		 */
		private $data;
		/**
		 * @var string[]
		 */
		private $colNames;
		
		/**
		 * Constructor.
		 *
		 * @param string $id
		 * @param string $className
		 * @param mixed[][] $data Matriz de datos que representa la tabla
		 * @param string[] $colNames Nombre de las columnas
		 */
		function __construct($id, $className, array $data, array $colNames = array())
		{
			Parameter::check(array(
					"string" 	=> array($id, $className)
				), __METHOD__);
				
			parent::__construct($id, $className);
			
			$this->data = $data;
			$this->colNames = $colNames;
		}
		/**
		 * Obtener html que representa la tabla
		 *
		 * @return string
		 */
		public function getSource()
		{
			$html = '<table';
			if ($this->id) $html .= ' id="'.$this->id.'"';
			if ($this->className) $html .= ' class="'.$this->className.'"';
			$html .= $this->getStyle().">";
			if (!empty($this->colNames))
			{
				$html .= "\n<tr>";
				foreach ($this->colNames as $col) $html .= "\n\t<th>$col</th>";
				$html .= "\n</tr>";
			}
			foreach ($this->data as $tr)
			{
				$rowData = is_array($tr) ? $tr : array($tr);
				$html .= "\n<tr>";
				foreach ($rowData as $td)
				{
					$html .= "\n\t<td>$td</td>";
				}
				$html .= "\n</tr>";
			}
			$html .= "\n</table>";
			return $html;
		}
	}
?>