<?
	/**
	 * Clase para generar documentos RSS.
	 * 
	 * @package system.xml
	 */
	class RSSWriter extends Main
	{
		/**
		 * @var string
		 */
		protected $version;
		/**
		 * @var string
		 */
		protected $charset;
		/**
		 * @var XMLWriter
		 */
		protected $xmlWriter;
		/**
		 * @var array
		 */
		protected $channel = array();
		/**
		 * @var array
		 */
		protected $items = array();
		
		/**
		 * Constructor
		 *
		 * @param string $version
		 * @param string $charset
		 * @throws Exception Si la clase XMLWriter no existe
		 */
		function __construct($version = '2.0', $charset = APP_CHARSET)
		{
			Parameter::check(array(
					"string" 	=> array($version, $charset)
				), __METHOD__);
			
			if (!class_exists('XMLWriter')) throw new Exception("Class XMLWriter not found!");
			
			$this->version = $version;
			$this->charset = $charset;
			$this->xmlWriter = new XMLWriter();
		}
		/**
		 * Establecer channel y sus subelementos
		 *
		 * @param string $title
		 * @param string $link
		 * @param string $description
		 * @param string $pubDate Si no se provee una fecha se utiliza la de ahora
		 * @param string $language
		 * @param array $extraElements Elementos adicionales para el channel
		 * @return void
		 */
		public function setChannel($title, $link, $description = '', $pubDate = '', $language = APP_LANGUAGE, array $extraElements = array())
		{
			Parameter::check(array(
					"string" 	=> array($title, $link, $description, $pubDate, $language)
				), __METHOD__);
			
			$this->channel['title'] = $title;
			$this->channel['link'] = $link;
			$this->channel['description'] = $description;
			$this->channel['pubDate'] = $pubDate ? $pubDate : date(DATE_RSS);
			$this->channel['language'] = $language;
			if (!empty($extraElements)) $this->channel = array_merge($this->channel, $extraElements);
		}
		/**
		 * Establecer imagen para el channel
		 *
		 * @param string $url
		 * @param string $link
		 * @param string $title
		 * @param string $description
		 * @param integer $width
		 * @param integer $height
		 * @return void
		 */
		public function setImage($url, $link = '', $title = '', $description = '', $width = 0, $height = 0)
		{
			Parameter::check(array(
					"string" 	=> array($url, $link, $title, $description),
					"integer"	=> array($width, $height)
				), __METHOD__);
			
			$this->channel['image'] = array(
				'url'			=> $url,
				'link'			=> $link,
				'title'			=> $title,
				'description'	=> $description,
				'width'			=> $width,
				'height'		=> $height
			);
		}
		/**
		 * Agregar nuevo item
		 *
		 * @param string $title
		 * @param string $link
		 * @param string $description
		 * @param string $pubDate
		 * @param array $extraElements Elementos adicionales para el item
		 * @return void
		 */
		public function addItem($title, $link, $description = '', $pubDate = '', array $extraElements = array())
		{
			Parameter::check(array(
					"string" 	=> array($title, $link, $description, $pubDate)
				), __METHOD__);
			
			$attrs = array(
				'title'			=> $title,
				'link'			=> $link,
				'description'	=> $description,
				'pubDate'		=> $pubDate
			);
			if (!empty($extraElements)) $attrs = array_merge($attrs, $extraElements);
			$this->items[] = $attrs;
		}
		/**
		 * Obtener XML
		 *
		 * @return string
		 */
		public function getSource()
		{
			$xml = $this->xmlWriter;
			$xml->openMemory();
			$xml->setIndentString("\t");
			$xml->setIndent(true);
			$xml->startDocument('1.0', $this->charset);
			$xml->startElement('rss');
			$xml->writeAttribute('version', $this->version);
				$xml->startElement('channel');
					$xml->writeElement('title', $this->channel['title']);
					$xml->writeElement('link', $this->channel['link']);
					if ($this->channel['description']) $xml->writeElement('description', $this->channel['description']);
					$xml->writeElement('language', $this->channel['language']);
					$xml->writeElement('pubDate', $this->channel['pubDate']);
					if (!empty($this->channel['image']))
					{
						$xml->startElement('image');
						$xml->writeElement('url', $this->channel['image']['url']);
						$xml->writeElement('link', $this->channel['image']['link']);
						if ($this->channel['image']['title']) $xml->writeElement('title', $this->channel['image']['title']);
						if ($this->channel['image']['description']) $xml->writeElement('title', $this->channel['image']['description']);
						if ($this->channel['image']['width']) $xml->writeElement('title', $this->channel['image']['width']);
						if ($this->channel['image']['height']) $xml->writeElement('title', $this->channel['image']['height']);
						$xml->endElement(); // </image>
					}
					foreach ($this->items as $item)
					{
						$xml->startElement('item');
							$xml->writeElement('title', $item['title']);
							$xml->writeElement('link', $item['link']);
							if ($item['description']) $xml->writeElement('description', $item['description']);
							if ($item['pubDate']) $xml->writeElement('pubDate', $item['pubDate']);
						$xml->endElement(); // </item>
					}
				$xml->endElement(); // </channel>
			$xml->endElement(); // </rss>
			return $xml->outputMemory(false);
		}
		/**
		 * Guardar contenido xml en un archivo
		 *
		 * @param string $xmlFile Ruta del archivo incluyendo su extensión. Si no existe se crea.
		 * @return void
		 */
		public function save($xmlFile)
		{
			Parameter::check(array(
					"string" 	=> array($xmlFile)
				), __METHOD__);
			
			$file = fopen($xmlFile, 'w+');
			fwrite($file, $this->getSource());
			fclose($file);
			@chmod($xmlFile, 0774);
		}
	}
?>