<?
	/**
	 * Clase para paginar arrays
	 * 
	 *
	 *
	 * @package system.db
	 * @example
	 
	$result = $db->executeQuery("SELECT user_name FROM Users");
	$pag = new Pagination($result, 15);
	foreach ($pag->data as $row) echo $row['user_name']."<br />";
	echo $pag->getScroller();
	 */
	class Pagination extends Main
	{
		protected $URL;
		/**
		 * Indice del primer registro
		 *
		 * @var integer
		 */
		protected $recStart;
		/**
		 * Indice del ultimo registro
		 *
		 * @var integer
		 */
		protected $recEnd;
		/**
		 * Numero de paginas
		 *
		 * @var integer
		 */
		protected $numPages;
		/**
		 * Número de pagina actual
		 *
		 * @var integer
		 */
		protected $actualPage;
		/**
		 * Cantidad de registros a mostrar por pagina
		 *
		 * @var integer
		 */
		protected $recsPerPage;
		/**
		 * Nombre de la variable que contiene la pagina actual. Viene por GET.
		 *
		 * @var string
		 */
		protected $numPageVar;
		/**
		 * Registros del resultado
		 *
		 * @var array
		 */
		protected $data;
		
		/**
		 * Constructor
		 *
		 * @param array $data Array de datos.
		 * @param integer $recsPerPage Cantidad de registros por pagina
		 * @param string $numPageVar Nombre de variable que contiene numero de pagina (siempre por GET)
		 */
		function __construct(array $data, $recsPerPage = 10, $numPageVar = 'p')
		{
			Parameter::check(array(
					"integer" => $recsPerPage,
					"string" => $numPageVar
				), __METHOD__);
			
			$url = APP_URL.(isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '');
			$this->setURL($url);
			$this->recsPerPage = $recsPerPage;
			$this->numPageVar = $numPageVar;
			$this->data = array();
			$this->actualPage = isset($_GET[$numPageVar]) ? (int)$_GET[$numPageVar] : 1;
			$this->recStart = $this->actualPage * $this->recsPerPage - $this->recsPerPage;
			$this->recEnd = $this->recStart + $this->recsPerPage;
			$numItems = count($data);
			$index = 0;
			$this->numPages = ceil($numItems / $this->recsPerPage);
			for ($i=$this->recStart; $i<min($this->recEnd, $numItems); $i++) $this->data[$index++] = $data[$i];
		}
		
		/**
		 * Obtener URL segun numero de página.
		 *
		 * @param integer $page Número de página.
		 * @return string
		 */
		protected function getURL($page)
		{
			Parameter::check(array(
					"integer" => $page
				), __METHOD__);
			
			$url = $this->URL;
			$getParams = array($this->numPageVar."=".$page);
			foreach ($_GET as $k => $v) if ($k != $this->numPageVar) $getParams[] = "$k=$v";
			$url .= '/?'.implode("&amp;", $getParams);
			return $url;
		}
		/**
		 * Establecer la URL para el scroller
		 *
		 * @param string $URL
		 * @return void
		 */
		public function setURL($URL)
		{
			$this->URL = $URL;
		}
		/**
		 * Obtener matriz de datos
		 *
		 * @return array
		 */
		public function getData()
		{
			return $this->data;
		}
		
		/**
		* Obtener numeros de pagina
		* 
		* @param integer $scrollerRange cantidad visible de numeros de pagina
		* @param string	$scrollerStyle clase CSS para el paginador
		* @param boolean $showNextPrev Mostrar "Anterior" y "Siguiente"
		* @return string Numeros de pagina
		*/
		public function getScroller($scrollerRange = 6, $scrollerStyle = 'scroller', $showNextPrev = true)
		{
			Parameter::check(array(
					"integer" 	=> $scrollerRange,
					"string" 	=> $scrollerStyle,
					"boolean"	=> $showNextPrev
				), __METHOD__);
			
			$minRange = ($this->actualPage - $scrollerRange < 1) ? 1 : $this->actualPage - $scrollerRange;
			$maxRange = ($this->actualPage + $scrollerRange > $this->numPages) ? $this->numPages : $this->actualPage + $scrollerRange;
			if ($this->numPages > 1 && $this->actualPage <= $this->numPages)
			{
				
				$out = '<div class="'.$scrollerStyle.'">';
				if ($this->actualPage - $scrollerRange > 1)
					$out .= '<a href="'.$this->getURL(1).'">«« '._("Primera").'</a> ';
				if ($this->actualPage > 1 && $showNextPrev)
					$out .= '<a href="'.$this->getURL($this->actualPage-1).'">« '._("Anterior").'</a>';
				for ($i=$minRange; $i<=$maxRange; $i++)
				{
					if ($this->actualPage == $i) $out .=' <strong><big><a href="'.$this->getURL($i).'">'.$i.'</a></big></strong> ';
					else $out .=' <a href="'.$this->getURL($i).'">'.$i.'</a> ';
				}
				if ($this->actualPage < $this->numPages && $showNextPrev)
					$out .= '<a href="'.$this->getURL($this->actualPage+1).'">'._("Siguiente").' »</a>';
				if ($this->actualPage + $scrollerRange < $this->numPages)
					$out .= '... <a href="'.$this->getURL((int)$this->numPages).'">'._("Ultima").' »»</a>';
				$out .= '</div>';
			}else{
				$out = '';
			}
			return $out;
		}

		/**
		* Mostrar numeros de pagina para paginar con AJAX
		* 
		* @param string	$JSfunction Nombre de la funcion en javascript que se llama al hacer click en el numero. Recibe como primer parametro el numero de pagina y como 2do parametro un array con los parametros adicionales.
		* @param array $JSparams Parametros adicionales de la funcion.
		* @param integer $scrollerRange Cantidad visible de numeros de pagina
		* @param string	$scrollerStyle Clase CSS para el paginador. Se le aplica al <div> contenedor.
		* @return string Numeros de pagina
		*/
		public function getAJAXScroller($JSfunction, array $JSparams = array(), $scrollerRange = 6, $scrollerStyle = 'scroller')
		{
			Parameter::check(array(
					"integer" => $scrollerRange,
					"string" => array($JSfunction, $scrollerStyle)
				), __METHOD__, __FILE__, __LINE__);
			
			$minRange = ($this->actualPage - $scrollerRange < 1) ? 1 : $this->actualPage - $scrollerRange;
			$maxRange = ($this->actualPage + $scrollerRange > $this->numPages) ? $this->numPages : $this->actualPage + $scrollerRange;
			if ($this->numPages > 1)
			{
				$out = '<div class="'.$scrollerStyle.'">';
				if ($this->actualPage - $scrollerRange > 1)
					$out .= '<a href="javascript:;" onclick="'.$JSfunction.'(1, ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[<< ]</a>&nbsp;';
				if ($this->actualPage > 1)
					$out .= '<a href="javascript:;" onclick="'.$JSfunction.'('.($this->actualPage-1).', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[< ]</a>';
				for ($i=$minRange;$i<=$maxRange;$i++)
				{
					if ($this->actualPage == $i) $out .='&nbsp;<strong><big><a href="javascript:;" onclick="'.$JSfunction.'('.$i.', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">'.$i.'</a></big></strong>&nbsp;';
					else $out .='&nbsp;<a href="javascript:;" onclick="'.$JSfunction.'('.$i.', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">'.$i.'</a>&nbsp;';
				}
				if ($this->actualPage < $this->numPages)
					$out .= '<a href="javascript:;" onclick="'.$JSfunction.'('.($this->actualPage+1).', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[ >]</a>';
				if ($this->actualPage + $scrollerRange < $this->numPages)
					$out .= '&nbsp;<a href="javascript:;" onclick="'.$JSfunction.'('.$this->numPages.', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[ >>]</a>';
				$out .= '</div>';
			}else{
				$out = '';
			}
			return $out;
		}
	}
?>