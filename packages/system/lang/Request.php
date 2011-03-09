<?php
/**
 * Esta clase maneja las variables que se envian por POST, GET y COOKIE.
 * Soporta Apache Mod Rewrite si la constante MOD_REWRITE_ACTIVATED lo indica y captura las variables separadas mediante "/".
 * Solo se permite una instancia.
 * Posee metodos útiles para validar.
 * 
 * @package system.lang
 *
 */
class Request extends Main
{
	/**
	 * @var Request
	 */
	private static $instance = null;
	
	/**
	 * @var string
	 */
	const GET = "GET";
	/**
	 * @var string
	 */
	const POST = "POST";
	/**
	 * @var string
	 */
	const REQUEST = "REQUEST";
	/**
	 * @var string
	 */
	const COOKIE = "COOKIE";
	/**
	 * Almacena referencia a las variables superglobales $_POST, $_GET, $_REQUEST y $_COOKIE
	 *
	 * @var mixed[]
	 */
	public $data = array();
	/**
	 * Primer variable recibida por GET
	 *
	 * @var string
	 */
	public $module;
	/**
	 * Segunda variable recibida por GET
	 *
	 * @var string
	 */
	public $task;
	/**
	 * Tercera variable recibida por GET
	 *
	 * @var string
	 */
	public $subtask;

	/**
	 * Constructor.
	 *
	 */
	private function __construct() {
		if (MOD_REWRITE_ACTIVATED) {
			$this->data['GET'] = isset($_SERVER['REDIRECT_URL']) ? preg_split("#/#", $_SERVER['REDIRECT_URL'], -1, PREG_SPLIT_NO_EMPTY) : array();
			$this->module = isset($this->data['GET'][0]) ? $this->data['GET'][0] : (isset($_GET['module']) ? $_GET['module'] : "");
			$this->task = isset($this->data['GET'][1]) ? $this->data['GET'][1] : (isset($_GET['task']) ? $_GET['task'] : "");
			$this->subtask = isset($this->data['GET'][2]) ? $this->data['GET'][2] : (isset($_GET['subtask']) ? $_GET['subtask'] : "");
			if (!empty($_GET)) $this->data['GET'] = array_merge($this->data['GET'], $_GET);
		}
		else {
			$this->data['GET'] =& $_GET;
			$this->module = isset($this->data['GET']['module']) ? $this->data['GET']['module'] : "";
			$this->task = isset($this->data['GET']['task']) ? $this->data['GET']['task'] : "";
			$this->subtask = isset($this->data['GET']['subtask']) ? $this->data['GET']['subtask'] : "";
		}

		$this->data['POST'] =& $_POST;
		$this->data['REQUEST'] =& $_REQUEST;
		$this->data['COOKIE'] =& $_COOKIE;
	}
	
	/**
	 * Método "Singleton" para obtener instancia de esta clase.
	 *
	 * @return Request
	 */
	public static function init() {
		if (self::$instance == null) self::$instance = new Request();
		return self::$instance;
	}
	
	/**
	 * Limpia las variables que indiquemos para ser usadas en un gestor de datos.
	 *
	 * @param string[] $vars Nombres de las variables a escapar.
	 * @param string $environment Entorno de las variables. Puede ser una se las siguientes constantes: GET, POST, REQUEST, COOKIE.
	 * @return array 
	 * @example $datos = $request->escapeString($db,array('dirs','files'), Request::POST);
	 */
	public function escapeString(array $vars, $environment = self::POST) {
		Parameter::check(array(
					"string" 	=> $environment
				), __METHOD__);
		
		$db = DB::init();
		$values = array();
		foreach ($vars as $var)
			$values["$var"] = $db->escapeString($this->data[$environment]["$var"]);
		return $values;
	}
	/**
	 * Get friendly URL param by its index. If it does not exist an empty string is returned.
	 *
	 * @param int $index
	 * @return string
	 */
	public function fget($index) {
		return isset($this->data['GET'][$index]) ? $this->data['GET'][$index] : '';
	}
	
	/**
	 * Obtener un array con las variables solicitadas por el metodo solicitado (POST, GET, REQUEST o COOKIE)
	 *
	 * @param mixed[] $vars Array donde la clave es el tipo de dato que esperamos y el valor es el o los nombres de las variables.
	 * @param string $environment Entorno de las variables. Puede ser una se las siguientes constantes: GET, POST, REQUEST, COOKIE.
	 * @return mixed[]
	 * @example $datos = $request->get(array("array" => array("dirs", "files")), Request::GET);
	 */
	public function get(array $vars, $environment = self::POST) {
		Parameter::check(array(
					"string" 	=> $environment
				), __METHOD__);
		
		$result = array();
		foreach ($vars as $type => $value) {
			$value = (array)$value;
			switch ($type) {
				case "int":
				case "integer":
					foreach ($value as $v)
						$result[$v] = isset($this->data[$environment][$v]) ? (int)$this->data[$environment][$v] : 0;
					break;
				case "float":
					foreach ($value as $v)
						$result[$v] = isset($this->data[$environment][$v]) ? floatval($this->data[$environment][$v]) : 0;
					break;
				case "string":
					foreach ($value as $v)
						$result[$v] = isset($this->data[$environment][$v]) ? $this->data[$environment][$v] : "";
					break;
				case "array":
					foreach ($value as $v)
						$result[$v] = isset($this->data[$environment][$v]) && is_array($this->data[$environment][$v]) ? $this->data[$environment][$v] : array();
					break;
				default:
					throw new InvalidArgumentException("$type is not a valid data type!");
			}
		}
		return $result;
	}
	/**
	 * Detecta si la llamada a la pagina fue mediante Prototype (AJAX)
	 *
	 * @return boolean
	 */
	public function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}
	/**
	 * Validar variables segun tipo de dato.
	 * 
	 * Tipos de datos disponibles:<br />
	 * "string" = cualquier caracter<br />
	 * "string(1,10)" = string de 1 a 10 caracteres<br />
	 * "string(5)" = string de 5 caracteres<br />
	 * "string(5,)" = string de 5 caracteres minimo<br />
	 * "email"<br />
	 * "date" = formato de fecha yyyy-mm-dd<br />
	 * "datetime" = formato de fecha yyyy-mm-dd[ hh:mm[:ss]]<br />
	 * "time" = hora formato hh:mm[:ss]<br />
	 * "phone"<br />
	 * "integer"<br />
	 * "float"<br />
	 * "alphanumeric"<br />
	 * "filename" = nombre de archivo. Acepta caracteres validos en Windows<br />
	 * "filename(zip,rar,tgz)" = nombre de archivo con las extensiones zip, rar y tgz<br />
	 * "filepath" = ruta de archivo. Acepta caracteres validos en Windows, \ y /<br />
	 * "dirname" = directorio separados por \ o /<br />
	 * "ip" = IP<br />
	 * "url" = Comienza por http|https|ftp:// o www. seguido por el dominio y lo que sea<br />
	 * "creditcard" = Tarjeta de credito<br />
	 * "#^\d{2}-\w{3}$#" = expresion regular<br />
	 * Acepta minima y maxima longitud en los tipos de dato 'integer','float','alphanumeric' y 'string'
	 *
	 * @param mixed[] $params Array asociativo donde la clave es el tipo de dato y el valor es la variable a validar o un array de valores
	 * @param boolean $trim Indica si eliminar espacios al principio y final del valor a testear. Solo es valido para los tipos de dato string y alphanumeric. [default = true]
	 * @return boolean False si alguno de los valores es invalido.
	 * @example
	  
	  $req = Request::init();
	  $isValid = $req->validate(array(
	  		'date' => $_POST['date'],
	  		'integer' => $_POST['age'],
	  		'string(10,50)' => array($_POST['comment'], $_POST['comment2']),
	  		'string(15)' => $_POST['asdf'],
	  		'integer(5,)' => $_POST['number']
	  ));
	 */
	public function validate(array $params, $trim = true) {
		Parameter::check(array(
					"boolean" 	=> $trim
				), __METHOD__);

		foreach ($params as $key => &$var) {
			//if ($var == null) return false;
			$value = is_array($var) ? $var : array($var);

			if (preg_match("/^(\w+)(?:\((.+?)\))?$/i", $key, $matches)) {
				$type = $matches[1];
				$typeOptions = isset($matches[2]) ? $matches[2] : null;
				$isString = in_array($type, array('string','alphanumeric'));
				if ($isString && $trim || !$isString) $value = array_map("trim", $value);
				
				if ($typeOptions && in_array($type, array('integer','float','alphanumeric','string'))
					&& preg_match("#^(\d+)(,(\d+)?)?$#", $typeOptions, $matches)) {
					$minLength = $matches[1];
					$undefinedLength = isset($matches[2]) && !isset($matches[3]);
					$maxLength = isset($matches[3]) ? $matches[3] : null;
					foreach ($value as $v) {
						$valueLength = strlen($v);
						if (!$undefinedLength && !$maxLength && $valueLength != $minLength 
							|| $valueLength < $minLength 
							|| $maxLength && $valueLength > $maxLength) return false;
					}
				}
				
				switch ($type) {
					case 'string':
						if (!$typeOptions) foreach ($value as $v) if ($v == '') return false;
						break;
					case "email":
						foreach ($value as $v)
							if (!preg_match("#^[\w.-]{1,64}@[\w.-]{1,255}\.[a-z]{2,3}(?:\.[a-z]{2})?$#i", $v)) return false;
						break;
					case "date": /* yyyy-mm-dd */
						foreach ($value as $v) {
							if (!preg_match("#^(\d{4})-(\d{2})-(\d{2})$#", $v, $matches)) return false;
							if (!checkdate($matches[2], $matches[3], $matches[1])) return false;
						}
						break;
					case "datetime": /* yyyy-mm-dd[ hh:mm[:ss]] */
						foreach ($value as $v) {
							if (!preg_match("#^(\d{4})-(\d{2})-(\d{2})(?:\s(\d{2}):(\d{2})(?::(\d{2}))?)?$#", $v, $matches)) return false;
							if (!checkdate($matches[2], $matches[3], $matches[1]) 
								|| $matches[4] && $matches[4] > 24 || $matches[5] && $matches[5] > 59
								|| $matches[6] && $matches[6] > 59) return false;
						}
						break;
					case "time": /* hh:mm[:ss] */
						foreach ($value as $v) {
							if (!preg_match("#^(\d{2}):(\d{2})(?::(\d{2}))?$#", $v, $matches)) return false;
							if ($matches[1] > 24 || $matches[2] > 59 || ($matches[3] && $matches[3] > 59)) return false;
						}
						break;
					case "phone":
						foreach ($value as $v)
							if (!preg_match("#^[0-9_+\(\) \-]+$#", $v)) return false;
						break;
					case "integer":
						foreach ($value as $v)
							if (!preg_match("#^\d+$#", $v)) return false;
						break;
					case "float":
						foreach ($value as $v)
							if (!preg_match("#^\d+(\.?\d+)?$#", $v)) return false;
						break;
					case "alphanumeric":
						foreach ($value as $v)
							if (!preg_match("#^\w+$#i", $v)) return false;
						break;
					case "filename":
						foreach ($value as $v)
							if (!preg_match("/^[^?*:|<>\\\\/]+\.([a-z0-9]+)$/i", $v, $matches)) return false;
							if ($typeOptions) {
								$ext = explode(",", strtolower($typeOptions));
								if (count($ext) > 0 && !in_array(strtolower($matches[1]), $ext)) return false;
							}
					case "filepath":
						foreach ($value as $v)
							if (!preg_match("/^(?:[a-z]:[\\\\/])?[^?*:|<>]+\.([a-z0-9]+)$/i", $v, $matches)) return false;
							if ($typeOptions) {
								$ext = explode(",", strtolower($typeOptions));
								if (count($ext) > 0 && !in_array(strtolower($matches[1]), $ext)) return false;
							}
					case "dirname":
						foreach ($value as $v)
							if (!preg_match("/^[^?*:|<>]+$/i", $v)) return false;
						break;
					case 'ip':
					case "ipv4":
						foreach ($value as $v) {
							if (!preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/i", $v, $matches)) return false;
							if ($matches[1] > 255 || $matches[2] > 255 || $matches[3] > 255 || $matches[4] > 255) return false;
						}
						break;
					case "url": /* URL: Comienza por http:// o www. seguido por el dominio y lo que sea */
						foreach ($value as $v) {
							if (!preg_match("/(?:^(?:http|https|ftp):\/\/)|(?:^www\.).+?\.[a-z]{2,3}.*?$/i", $v)) return false;
						}
						break;
					case "creditcard":
						foreach ($value as $v)
							if (!preg_match("#^\d{16}$#", $v)) return false;
						break;
					default:
						foreach ($value as $v)
							if (!@preg_match($type, $v)) return false;
				}
			}
			else {
				// regexp
				foreach ($value as $v) if (!@preg_match($key, $v)) return false;
			}
		}
		return true;
	}
}
?>