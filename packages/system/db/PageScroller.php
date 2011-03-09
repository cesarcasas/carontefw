<?
	/**
	 * Clase para mostrar numeros de pagina de un ResultSet paginado.
	 *  
	 * @package system.db
	 */
	class PageScroller extends Main
	{
		protected $pageCount;
		protected $actualPage;
		protected $numPageVar;
		protected $url;
		
		/**
		 * Constructor
		 *
		 * @param ResultSet $result Resultado paginado
		 * @param string $numPageVar Nombre de la variable que contiene el numero de página.
		 * @param string $url URL a utilizar como links en los numeros de página.
		 * @throws Exception Si el ResultSet no es páginado.
		 */
		function __construct(ResultSet $result, $numPageVar = 'p', $url = '')
		{
			if ($result->pageCount == 0) throw new Exception("Page count must be an integer greater than 0");
			$this->pageCount = $result->pageCount;
			$actualPage = isset($_GET[$numPageVar]) ? (int)$_GET[$numPageVar] : 1;
			if ($actualPage < 1) $actualPage = 1;
			$this->actualPage = $actualPage;
			$this->numPageVar = $numPageVar;
			if (!$url) $url = APP_URL . (isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '');
			$this->setURL($url);
		}
		public function __toString()
		{
			return $this->getScroller();
		}
		/**
		 * Establecer la url para el scroller
		 *
		 * @param string $url
		 * @return void
		 */
		public function setURL($url)
		{
			$this->url = preg_replace("#/$#", "", $url);
		}
		/**
		 * Obtener url segun numero de página.
		 *
		 * @param integer $page Número de página.
		 * @return string
		 */
		protected function getURL($page)
		{
			Parameter::check(array(
					"integer" => $page
				), __METHOD__);
			
			$url = htmlspecialchars($this->url, ENT_QUOTES);
			$getParams = array();
			foreach ($_GET as $k => $v) if ($k != $this->numPageVar) $getParams[] = "$k=".urlencode($v);
			$getParams[] = $this->numPageVar."=".$page;
			$url .= '/?'.implode("&amp;", $getParams);
			
			return $url;
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
			$maxRange = ($this->actualPage + $scrollerRange > $this->pageCount) ? $this->pageCount : $this->actualPage + $scrollerRange;
			if ($this->pageCount > 1 && $this->actualPage <= $this->pageCount)
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
				if ($this->actualPage < $this->pageCount && $showNextPrev)
					$out .= '<a href="'.$this->getURL($this->actualPage+1).'">'._("Siguiente").' »</a>';
				if ($this->actualPage + $scrollerRange < $this->pageCount)
					$out .= '... <a href="'.$this->getURL((int)$this->pageCount).'">'._("Ultima").' »»</a>';
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
				), __METHOD__);
			
			$minRange = ($this->actualPage - $scrollerRange < 1) ? 1 : $this->actualPage - $scrollerRange;
			$maxRange = ($this->actualPage + $scrollerRange > $this->pageCount) ? $this->pageCount : $this->actualPage + $scrollerRange;
			if ($this->pageCount > 1)
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
				if ($this->actualPage < $this->pageCount)
					$out .= '<a href="javascript:;" onclick="'.$JSfunction.'('.($this->actualPage+1).', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[ >]</a>';
				if ($this->actualPage + $scrollerRange < $this->pageCount)
					$out .= '&nbsp;<a href="javascript:;" onclick="'.$JSfunction.'('.$this->pageCount.', ['.str_replace('"', '\\"', implode(',', $JSparams)).'])">[ >>]</a>';
				$out .= '</div>';
			}else{
				$out = '';
			}
			return $out;
		}
	}
?>