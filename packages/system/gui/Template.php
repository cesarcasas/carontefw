<?php
	import("system.io.FileNotFoundException");
	
	/**
	 * Clase para manejar documentos XHTML.
	 * Los métodos showMessage() y showMessages() requieren el archivo ui.css donde se definen los estilos de los mensajes.
	 * 
	 * @package system.gui
	 * @example $tpl = new Template();
$tpl->header("default", "Mi documento loco");
require_once("mi_archivo.php");
$tpl->footer();
	 */
	class Template extends Main
	{
		/**
		 * @var string
		 * @static 
		 */
		const DTD_XHTML_TRANSITIONAL = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		/**
		 * @var string
		 * @static 
		 */
		const DTD_XHTML_STRICT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		/**
		 * @var string
		 * @static 
		 */
		const DTD_XHTML_FRAMESET = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
		/**
		 * Clase CSS para mensajes de texto plano
		 * @var string
		 */
		const PLAIN_MSG = 'tpl-plain-message';
		/**
		 * Clase CSS para mensajes de error
		 * @var string
		 */
		const ERROR_MSG = 'tpl-error-message';
		/**
		 * Clase CSS para mensajes de alerta
		 * @var string
		 */
		const WARNING_MSG = 'tpl-warning-message';
		/**
		 * Clase CSS para mensajes de información
		 * @var string
		 */
		const INFO_MSG = 'tpl-info-message';
		/**
		 * Clase CSS para lista de texto plano
		 * @var string
		 */
		const PLAIN_LIST = 'tpl-plain-list';
		/**
		 * Clase CSS para lista de errores
		 * @var string
		 */
		const ERROR_LIST = 'tpl-error-list';
		/**
		 * Clase CSS para lista de advertencias
		 * @var string
		 */
		const WARNING_LIST = 'tpl-warning-list';
		/**
		 * Clase CSS para lista de informes
		 * @var string
		 */
		const INFO_LIST = 'tpl-info-list';
		/**
		 * @var boolean
		 */
		protected $showXMLDef;
		/**
		 * @var string
		 */
		protected $xmlVersion;
		/**
		 * @var string
		 */
		protected $docType;
		/**
		 * @var string
		 */
		protected $lang;
		/**
		 * @var string
		 */
		protected $charset;
		/**
		 * @var string
		 */
		protected $title;
		/**
		 * @var string
		 */
		protected $titleSeparator = ' - ';
		/**
		 * @var string
		 */
		protected $keywords = array();
		/**
		 * @var string
		 */
		protected $description;
		/**
		 * @var string
		 */
		protected $favIcon;
		/**
		 * @var string
		 */
		protected $headExtraContent;
		/**
		 * @var string
		 */
		protected $bodyOnload;
		/**
		 * @var string[]
		 */
		protected $bodyAttributes = array();
		/**
		 * @var string
		 */
		protected $templatePath;
		/**
		 * @var string
		 */
		protected $tplHeaderSuffix = '_header';
		/**
		 * @var string
		 */
		protected $tplFooterSuffix = '_footer';
		/**
		 * @var string
		 */
		protected $tplExtension = '.php';
		/**
		 * @var array
		 */
		protected $links = array();
		/**
		 * Array de archivos CSS importados
		 * @var string[]
		 */
		protected $importedCSS = array();
		/**
		 * Array de archivos JS importadois
		 * @var string[]
		 */
		protected $importedJS = array();
		/**
		 * @var array
		 */
		protected $js = array();
		/**
		 * @var array
		 */
		protected $globalJSVars = array();
		/**
		 * @var array
		 */
		protected $rss = array();
		/**
		 * @var array
		 */
		protected $metaTags	= array();
		/**
		 * @var string
		 */
		protected $textDirection = 'ltr';
		/**
		 * @var string
		 */
		protected $extraNamespace = '';
		/**
		 * @var string
		 */
		protected $defaultTemplate = 'default';
		/**
		 * @var boolean
		 */
		protected $autoloadComponents = false;
		
		protected $_AppCaronte;
		
		public $optimizeJS=false;
		
		protected $varPHP = array();
		/**
		 * Constructor
		 *
		 * @param boolean $showXMLDef 	Mostrar definicion XML al principio del documento
		 * @param string $xmlVersion 	Version XML
		 * @param string $docType 		Doctype
		 * @param string $lang 			Lenguaje
		 * @param string $charset 		Codificacion
		 * @param string $title			Titulo del documento
		 * @param string $description	Meta description
		 * @param string $favIcon		Icono de favoritos
		 * @param string $bodyOnload		Codigo javascript que se ejecuta onload
		 */
		public function __construct(
							$showXMLDef = false,
							$xmlVersion = '1.0',
							$docType = self::DTD_XHTML_STRICT,
							$lang = APP_LANGUAGE,
							$charset = APP_CHARSET,
							$title = APP_TEMPLATE_TITLE,
							$description = '',
							$favIcon = APP_TEMPLATE_FAVICON,
							$bodyOnload = ''
						)
		{
			Parameter::check(array(
					"string" 	=> array($xmlVersion, $docType, $lang, $charset, $title, $description, $favIcon, $bodyOnload),
					"boolean" 	=> $showXMLDef
				), __METHOD__);
			
				global $_AppCaronte;
				
			$this->_AppCaronte=$_AppCaronte;
			$this->showXMLDef = $showXMLDef;
			$this->xmlVersion = $xmlVersion;
			$this->setDoctype($docType);
			$this->setLang ($lang);
			$this->setCharset($charset);
			$this->setTitle($title);
			$this->setDescription($description);
			$this->setFavIcon($favIcon);
			$this->addBodyOnload($bodyOnload);
			$this->setTemplatePath(APP_TEMPLATE_PATH);
		}

		
		public function addPHPVar($varName, $varValue){
			$this->varPHP[$varName]=$varValue;
		}
		/**
		 * Setear ruta a la carpeta de templates con los header y footer.
		 *
		 * @param 	string $path ruta
		 * @return 	void
		 */
		public function setTemplatePath($path)
		{
			Parameter::check(array(
					"string" 	=> $path
				), __METHOD__);
				
			$this->templatePath = preg_match("#/$#", $path) ? $path : "$path/";
		}
		
		/**
		 * Setear sufijo para los headers.
		 *
		 * @param 	string $str sufijo
		 * @return 	void
		 */
		public function setTplHeaderSuffix($str)
		{
			Parameter::check(array(
					"string" 	=> $str
				), __METHOD__);
				
			$this->tplHeaderSuffix = $str;
		}
		
		/**
		 * Setear sufijo para los footers.
		 *
		 * @param 	string $str sufijo
		 * @return 	void
		 */
		public function setTplFooterSuffix($str)
		{
			Parameter::check(array(
					"string" 	=> $str
				), __METHOD__);
				
			$this->tplFooterSuffix = $str;
		}
		
		/**
		 * Setear extension de los templates
		 *
		 * @param 	string	$ext extensión
		 * @return 	void
		 */
		public function setTplExtension($ext)
		{
			Parameter::check(array(
					"string" 	=> $ext
				), __METHOD__);
				
			$this->tplExtension = $ext;
		}
		/**
		 * Establecer template default que se utiliza en metodos como showMessage()
		 *
		 * @param string $tplName
		 * @return 	void
		 */
		public function setDefaultTemplate($tplName)
		{
			$this->defaultTemplate = $tplName;
		}
		/**
		 * Agregar atributo al <body>
		 *
		 * @param string $attribute
		 * @param string $value
		 * @return void
		 */
		public function setBodyAttribute($attribute, $value)
		{
			$this->bodyAttributes[$attribute] = htmlspecialchars($value, ENT_QUOTES);
		}
		/**
		 * Establecer separador para los titulos. Al titulo original se le agregan los del metodo header separados mediante este string.
		 *
		 * @param string $sep
		 * @return void
		 */
		public function setTitleSeparator($sep)
		{
			$this->titleSeparator = htmlspecialchars($sep, ENT_QUOTES);
		}
		
		/**
		 * Incluir archivo css/js. Permite infinitos parametros
		 * 
		 * @param string $file Archivo a incluir
		 * @return void
		 */
		public function addFile($file)
		{
			Parameter::check(array(
					"string" 	=> $file
				), __METHOD__);
				
			$files = func_get_args();
			foreach ($files as $src)
			{
				$ext = strtolower(substr($src, strrpos($src, '.')+1));
				if ($ext == 'js') $this->addJS($src);
				else if ($ext == 'css') $this->addCSS($src);
			}
		}
		
		/**
		 * Modificar titulo del documento
		 *
		 * @param string $title Titulo del documento
		 * @return void
		 */
		public function setTitle($title)
		{
			Parameter::check(array(
					"string" 	=> $title
				), __METHOD__);
				
			$this->title = htmlspecialchars($title, ENT_COMPAT);
		}
		
		/**
		 * Modificar dtd del documento
		 *
		 * @param string $docType DTD
		 * @return void
		 */
		public function setDoctype($docType)
		{
			Parameter::check(array(
					"string" 	=> $docType
				), __METHOD__);
				
			$this->docType = $docType;
		}
		
		/**
		 * Modificar lenguaje del documento
		 *
		 * @param string	$lang lenguaje
		 * @return void
		 */
		public function setLang($lang)
		{
			Parameter::check(array(
					"string" 	=> $lang
				), __METHOD__);
				
			$this->lang = $lang;
		}

		/**
		 * Obtiene el lenguaje del documento
		 */
		public function getLang() {
			return $this->lang;
		}
		
		/**
		 * Modificar juego de caracteres del documento
		 *
		 * @param string $charset Charset. Ej: ISO-8859-1 / UTF-8
		 * @return void
		 */
		public function setCharset($charset)
		{
			Parameter::check(array(
					"string" 	=> $charset
				), __METHOD__);
				
			$this->charset = $charset;
		}
		
		/**
		 * Modificar keywords del documento. Acepta un string o array de palabras.
		 *
		 * @param mixed $keywords keywords
		 * @return void
		 */
		public function setKeywords($keywords)
		{
			$this->keywords = (array)$keywords;
		}
		
		/**
		 * Agregar keywords al documento. Acepta un string o array de palabras.
		 *
		 * @param mixed $keywords keywords
		 * @param boolean $append True = Agregar al final. False = agregar al principio.
		 * @return void
		 */
		public function addKeywords($keywords, $append = true)
		{
			Parameter::check(array(
					"boolean"	=> $append
				), __METHOD__);
			
			$keywords = (array)$keywords;
			if ($append) $this->keywords = array_merge($this->keywords, $keywords);
			else array_splice($this->keywords, 0, 0, $keywords);
		}
		
		/**
		 * Modificar descripción del documento
		 *
		 * @param 	string	$description descripción
		 * @return 	void
		 */
		public function setDescription($description)
		{
			Parameter::check(array(
					"string" 	=> $description
				), __METHOD__);
				
			$this->description = htmlspecialchars($description, ENT_COMPAT);
		}
		
		/**
		 * Agregar descripción del documento.
		 *
		 * @param string $description descripción
		 * @return void
		 */
		public function addDescription($description)
		{
			Parameter::check(array(
					"string" 	=> $description
				), __METHOD__);
				
			$description = htmlspecialchars($description, ENT_COMPAT);
			$this->description .= $this->description ? " ".$description : $description;
		}
		
		/**
		 * Modificar icono de favoritos
		 *
		 * @param 	string	$favIcon ruta de la imagen
		 * @return 	void
		 */
		public function setFavIcon($favIcon)
		{
			Parameter::check(array(
					"string" 	=> $favIcon
				), __METHOD__);
				
			$this->favIcon = $favIcon;
		}
		
		/**
		 * Agregar meta
		 *
		 * @param 	string	$name valor del atributo 'name'
		 * @param 	string	$content valor del atributo 'content'
		 * @param 	string	$lang lenguaje. Ej: es/en
		 * @return 	void
		 */
		public function addMetaTag($name, $content, $lang = '')
		{
			Parameter::check(array(
					"string" 	=> array($name, $content, $lang)
				), __METHOD__);
				
			$this->metaTags[] = array(
					'name' 		=> $name,
					'content' 	=> htmlspecialchars($content, ENT_COMPAT),
					'lang'		=> $lang ? $lang : $this->lang
				);
		}
		
		/**
		 * Agregar link
		 *
		 * @param string $href
		 * @param string $type
		 * @param string $rel
		 * @param string $title
		 * @param string $id
		 * @param string $media
		 * @param boolean $bottomFixed Anclar link para que se incluya siempre al final de todos los demas.
		 * @return void
		 */
		public function addLink($href, $type = 'text/css', $rel = 'stylesheet', $title = '', $id = '', $media = '', $bottomFixed = false)
		{
			Parameter::check(array(
					"string" => array($href, $type, $rel, $title, $id, $media),
					"boolean" => $bottomFixed
				), __METHOD__);
			
			$this->links[] = array(
				'href' 	=> $href,
				'type' 	=> $type,
				'rel' 	=> $rel,
				'title'	=> $title,
				'id'	=> $id,
				'media'	=> $media,
				'_bottom_fixed' => $bottomFixed
			);
		}
		
		/**
		 * Agregar links a CSS stylesheet.
		 *
		 * @param mixed $href Rutas de los archivos css. Puede ser uno solo o un array de archivos.
		 * @param string $title
		 * @param string $id
		 * @param string $media
		 * @param boolean $bottomFixed Forzar link a incluirse último
		 * @return void
		 */
		public function addCSS($href, $title = '', $id = '', $media = '', $bottomFixed = false)
		{
			Parameter::check(array(
					"string" => array($title, $id, $media),
					"boolean" => $bottomFixed
				), __METHOD__);
			
			$href = (array)$href;
			foreach ($href as $css)	$this->addLink($css, 'text/css', 'stylesheet', $title, $id, $media, $bottomFixed);
		}
		
		
		
		public function clearCSS(){
			$this->links=array();
		}
		
		public function clearJS(){
			$this->js=array();
			$this->importedJS=array();
		}
		/**
		 * Eliminar <link> previamente agregado. Busca por el atributo href.
		 *
		 * @param string $href
		 * @return void
		 */
		public function removeLink($href)
		{
			$count = count($this->links);
			for ($i=0; $i<$count; $i++)
				if ($this->links[$i]['href'] == $href) unset($this->links[$i]);
		}
		
		/**
		 * Agregar archivo Javascript.
		 *
		 * @param 	mixed $src Rutas a archivos js. Puede ser uno solo o un array de archivos.
		 * @param 	string $type type
		 * @return 	void
		 */
		public function addJS($src, $type = 'text/javascript', $conditional=array())
		{
			Parameter::check(array(
					"string" 	=> array($type)
				), __METHOD__);
				
			$src = (array)$src;
			foreach ($src as $js) $this->js[] = array('src' => htmlspecialchars($js, ENT_COMPAT), 'type' => $type, 'conditional' => $conditional);
		}
		
		/**
		 * Agregar link a RSS
		 *
		 * @param 	string $href href
		 * @param 	string $title Titulo descriptivo
		 * @return 	void
		 */
		public function addRSS($href, $title = '')
		{
			Parameter::check(array(
					"string" 	=> array($href, $title)
				), __METHOD__);
				
			$this->rss[] = array(
				'href' => htmlspecialchars($href, ENT_COMPAT),
				'title' => htmlspecialchars($title, ENT_COMPAT)
			);
		}
		
		/**
		 * Importar codigo JS. Utiliza el archivo jscommon.php que debe estar en el root de la aplicación.
		 * Previene importar el mismo archivo 2 veces.
		 * 
		 * @param mixed $files Archivos o paquetes a incluir. Se utiliza la nomenclatura de la funcion import()
		 * @param boolean $compress Comprimir codigo JS. Remueve saltos de linea y espacios innecesarios. La única restricción es no usar comentarios de linea con // y terminar cada declaración con ;
		 * @return void
		 * @throws FileNotFoundException Si el archivo importador de JS no se encuentra.
		 */
		public function importJS($files, $compress = false)
		{
			
		die("jsimport deprecated");
			Parameter::check(array(
					"boolean"	=> $compress
				), __METHOD__);
			
			$jsImporter = 'jscommon.php';
			if (!file_exists(APP_ROOT.'/'.$jsImporter)) throw new FileNotFoundException("'$jsImporter' file not found!");
			$files = array_diff((array)$files, $this->importedJS);
			if (empty($files)) return;
			$this->importedJS = array_merge($this->importedJS, $files);
			$this->addJS('/'.$jsImporter.'?import='.implode(";", $files).($compress ? '&compress=1' : ''));
			
			
		}
		
		/**
		 * Importar codigo CSS. Utiliza el archivo csscommon.php que debe estar en el root de la aplicación.
		 * Previene importar el mismo archivo 2 veces.
		 * 
		 * @param mixed $files Archivos o paquetes a incluir. Se utiliza la nomenclatura de la funcion import()
		 * @param boolean $compress Comprimir codigo CSS. Remueve saltos de linea y espacios innecesarios.
		 * @return void
		 * @throws FileNotFoundException Si el archivo importador de CSS no se encuentra.
		 */
		public function importCSS($files, $compress = false)
		{
			Parameter::check(array(
					"boolean"	=> $compress
				), __METHOD__);
			
		/*	$cssImporter = 'csscommon.php';
			if (!file_exists(APP_ROOT.'/'.$cssImporter)) throw new FileNotFoundException("'$cssImporter' file not found!");
			$files = array_diff((array)$files, $this->importedCSS);
			if (empty($files)) return;
			$this->importedCSS = array_merge($this->importedCSS, $files);
			$this->addCSS('/'.$cssImporter.'?import='.implode(";", $files).($compress ? '&compress=1' : ''));*/
		
		die("Este metodo ya no se usa mas!!!! ".__FILE__.":".__LINE__);
		}
		
		/**
		 * Agregar variable global Javascript. Transforma los tipos de dato PHP a JS mediante json_encode().
		 *
		 * @param string $name
		 * @param mixed $value
		 * @return void
		 */
		public function addJSVar($name, $value = '')
		{
			Parameter::check(array(
					"string"	=> $name
				), __METHOD__);
							
			$this->globalJSVars[$name] = json_encode($value);
		}
		
		/**
		 * Agregar contenido extra en el head
		 *
		 * @param 	string	$str html
		 * @return 	void
		 */
		public function addHeadContent($str)
		{
			Parameter::check(array(
					"string" 	=> $str
				), __METHOD__);
				
			$this->headExtraContent .= $str;
		}
		
		
		public function clearHeadContent(){
			$this->headExtraContent="";
		}
		/**
		 * Agregar comentario condicional que lee el IE
		 *
		 * @param string $condition Condición. [Default = "lt IE 7"]
		 * @param mixed[] $files Archivos a incluir dentro del condicional
		 * @return void
		 */
		public function addConditionalComment($condition = 'lt IE 7', array $files = array())
		{
			Parameter::check(array(
					"string" 	=> $condition
				), __METHOD__);
			
			$html = "<!--[if $condition]>";
			foreach ($files as $src)
			{
				$ext = strtolower(substr($src, strrpos($src, '.')+1));
				if ($ext == 'js') $html .= '<script type="text/javascript" src="'.htmlspecialchars($src, ENT_COMPAT).'"></script>';
				else if ($ext == 'css') $html .= '<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($src, ENT_COMPAT).'" />';
			}
			$html .= "<![endif]-->\n\t";
			$this->addHeadContent($html);
		}
		
		/**
		 * Agregar codigo javascript para ejecutar onload
		 *
		 * @param 	string	$jsCode codigo javascript
		 * @return 	void
		 */
		public function addBodyOnload($jsCode)
		{
			Parameter::check(array(
					"string" 	=> $jsCode
				), __METHOD__);
				
			$jsCode = str_replace('"', "'", $jsCode);
			$this->bodyOnload .= ($this->bodyOnload != '' && !preg_match("#; *$#", $this->bodyOnload)) ? ';'.$jsCode : $jsCode;
		}
		
		/**
		 * Redireccionar a otra url en un tiempo determinado mediante meta refresh.
		 *
		 * @param 	string 	$redirUrl URL donde redirigir
		 * @param 	integer	$redirTime Tiempo en segundos
		 * @return 	void
		 */
		public function setRedirect($redirUrl = 'index.php', $redirTime = 2)
		{
			Parameter::check(array(
					"string" 	=> $redirUrl,
					"integer"	=> $redirTime
				), __METHOD__);
				
			$this->addHeadContent('<meta http-equiv="refresh" content="'.$redirTime.'; url='.$redirUrl.'" />');
		}
		/**
		 * Establecer direccion del texto.
		 *
		 * @param string $dir
		 * @return 	void
		 */
		public function setTextDirection($dir)
		{
			$this->textDirection = $dir;
		}
	
		/**
		 * Establecer un namespace extra (facebook con IE lo necesita).
		 *
		 * @param string $dir
		 * @return 	void
		 */
		public function addExtraNamespace($attr, $value)
		{
			$this->extraNamespace = "{$attr}=\"{$value}\"";
		}
	

		/**
		 * Muestra comienzo del documento HTML.
		 *
		 * @param 	string		$tpl Nombre del template (header) descartando el sufijo. Si es null no incluye nigun template. [Default = 'default']
		 * @param 	string		$title Titulo del documnento, se añade al titulo original separado por el separador asignado mediante setTitleSeparator()
		 * @param 	string[]	$files Archivos css/js a incluir
		 * @param 	mixed		$keywords Meta keywords adicionales. Puede ser un string o array de keywords.
		 * @param 	string		$description Meta description, reemplaza la descripcion global
		 * @param 	string		$bodyOnload Codigo javascript adicional para ejecutar onload
		 * @return 	void
		 * @throws	FileNotFoundException Si el template no existe.
		 */
		public function header($tpl = 'default', $title = '', array $files = null, $keywords = '', $description = '', $bodyOnload = '')
		{
			Parameter::check(array(
					"string" 	=> array($tpl, $title, $description, $bodyOnload)
				), __METHOD__);

			
						
			if ($tpl)
			{
				$templateFile = $this->templatePath.$tpl.$this->tplHeaderSuffix.$this->tplExtension;
				
				if (!file_exists($templateFile)) throw new FileNotFoundException("Template '$tpl' not found");
			}
				
			if ($title != '')
			{
				if ($this->title != '') $this->title .= $this->titleSeparator.htmlspecialchars($title, ENT_COMPAT);
				else $this->setTitle($title);
			}

			if (is_array($files)) foreach ($files as $v) $this->addFile($v);
			if ($keywords) $this->addKeywords($keywords);
			if ($description != '') $this->setDescription($description);
			if ($bodyOnload != '') $this->addBodyOnload($bodyOnload);
			if ($this->showXMLDef) echo <<<HTML
<?xml version="{$this->xmlVersion}" encoding="{$this->charset}" ?>

HTML;
			$keywords = htmlspecialchars(implode(", ", $this->keywords), ENT_COMPAT);
			echo <<<HTML
{$this->docType}
<html xmlns="http://www.w3.org/1999/xhtml" {$this->extraNamespace} xml:lang="{$this->lang}" lang="{$this->lang}" dir="{$this->textDirection}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$this->charset}" />
	<meta http-equiv="Content-Language" content="{$this->lang}" />
	<title>{$this->title}</title>
	<meta name="keywords" content="$keywords" />
	<meta name="description" content="{$this->description}" lang="{$this->lang}" />

HTML;
	foreach ($this->metaTags as $v)
		echo <<<HTML
	<meta name="{$v['name']}" content="{$v['content']}" lang="{$v['lang']}" />

HTML;
	array_multisort(array_collect($this->links, '_bottom_fixed'), SORT_ASC, SORT_NUMERIC, $this->links);
	foreach ($this->links as $v)
	{
		$attrs = 'rel="'.$v['rel'].'" type="'.$v['type'].'" href="'.htmlspecialchars($v['href'], ENT_COMPAT).'"';
		if ($v['id']) $attrs .= ' id="'.$v['id'].'"';
		if ($v['title']) $attrs .= ' title="'.htmlspecialchars($v['title'], ENT_COMPAT).'"';
		if ($v['media']) $attrs .= ' media="'.$v['media'].'"';
		echo <<<HTML
	<link $attrs />

HTML;
	}
	foreach ($this->rss as $v)
		echo <<<HTML
	<link rel="alternate" type="application/rss+xml" href="{$v['href']}" title="{$v['title']}" />

HTML;
		if ($this->favIcon) echo <<<HTML
	<link rel="shortcut icon" href="{$this->favIcon}" />

HTML;

if($this->optimizeJS){
	
	
		$filesJS=array();
		foreach ($this->js as $file){
			if($file!="/js/initCache.js" || $file!="/js/endCache.js" || $file!="/js/endCache" || $file!="/js/initCache")	$filesJS[]=$file["src"];
		}
		
			$files=implode(",",$filesJS);
			
			if($this->_AppCaronte->getBrowser()=="Internet Explorer 6"){
					
				$htpass_user=defined("HTPASS_USER") ?  HTPASS_USER.":" : "";
				$htpass_pass=defined("HTPASS_PASS") ?  HTPASS_PASS."@" : "";
				
				
				$contentJS=file_get_contents("http://".$htpass_user.$htpass_pass.$_SERVER['SERVER_NAME']."/jscommon.php?import=$files");
					echo 	"<script type='text/javascript'>";
					echo $contentJS;
					echo "</script>";
			}else{
				echo "<script type='text/javascript' src='/jscommon.php?import=$files'></script>";
			}
	
}
else{
	foreach ($this->js as $v){
	//lortmorris echo $v['src'];
            if(count($v['conditional'])>0) echo $v['conditional'][0];
echo <<<HTML
	<script type="{$v['type']}" src="{$v['src']}"></script>

HTML;
}
            if(count($v['conditional'])>0) echo $v['conditional'][1];
      }          


        
			if (!empty($this->globalJSVars))
			{
				echo <<<HTML
	<script type="text/javascript">/*<![CDATA[*/

HTML;
				foreach ($this->globalJSVars as $name => $value)
				{
					echo <<<HTML
		$name = $value;\n
HTML;
				}
				echo <<<HTML
	/*]]>*/</script>
HTML;
			}
			if ($this->headExtraContent) echo <<<HTML
	
	{$this->headExtraContent}

HTML;
			$bodyAttributes = '';
			foreach ($this->bodyAttributes as $attr => $value) $bodyAttributes .= " $attr=\"$value\"";
			
			echo "<script type=\"text/javascript\">\r\n";

				$constantes=get_defined_constants();
				
				$namesConstants=array_keys($constantes);
				
				foreach ($namesConstants as $name){
				
					if(substr($name, 0, 3)=="EVT" || substr($name, 0, 7)=="DEFINED") {
						
						echo "var $name=".$constantes[$name]."; \r\n";
					}
				}

			echo "</script>";
			echo <<<HTML

</head>
<body onload="{$this->bodyOnload}"$bodyAttributes>

HTML;
			if ($tpl) {
						
				$keys=array_keys($this->varPHP);
				foreach ($keys as $key){
					eval("$".$key ."=". $this->varPHP[$key].";");
				}
				
			//	require_once($this->_AppCaronte->pathApp."/".APP_TEMPLATE_PATH."/default_footer.php");	
				require_once($templateFile);
                                
				}
		}
		
		/**
		 * Muestra fin del documento HTML
		 * 
		 * @param string $tpl Nombre del template (footer) descartando el sufijo. Si es null no se incluye nada. [Default = 'default']
		 * @return void
		 * @throws FileNotFoundException Si el template no existe
		 */
		public function footer($tpl = 'default')
		{
			Parameter::check(array(
					"string" 	=> $tpl
				), __METHOD__);
			
			if ($tpl)
			{
				$templateFile = $this->templatePath.$tpl.$this->tplFooterSuffix.$this->tplExtension;
				if (!file_exists($templateFile)) throw new FileNotFoundException("Template '$tpl' not found");
			}
			
			if ($tpl) require_once($templateFile);
                   //    require_once($this->_AppCaronte->pathApp."/".APP_TEMPLATE_PATH."/default_footer.php");	*/
			
			
			echo <<<HTML

</body>
</html>
HTML;
		}
	


public static function loadBox($name="")
{
    require_once(APP_COMMON."/templates/boxes/$name.php");
}
		/**
		 * Mostrar mensaje de error incluyendo header y footer default.
		 * Los estilos estan definidos por default en el archivo ui.css
		 *
		 * @param string $message Mensaje de error.
		 * @param string $type Tipo de mensaje. Puede ser una se las constantes *_MSG.
		 * @return void
		 */
		public function showMessage($message, $type = self::ERROR_MSG)
		{
			Parameter::check(array(
					"string" 	=> array($message, $type)
				), __METHOD__);
			
			$this->header($this->defaultTemplate);
			echo '<div class="'.$type.'">'.$message.'</div>';
			$this->footer($this->defaultTemplate);
		}
		
		/**
		 * @deprecated Utilizar showList()
		 * @return void
		 */
		public function showMessages(array $messages, $type = self::ERROR_LIST)
		{
			$this->showList($messages, $type);
		}
		
		
		
		public function getBlock($path, $internal = false){
			import("system.gui.Blocks");
			return new Blocks($path, $internal);
		}
		
		
		
}

