<?
	/**
	 * Alias de printf, reemplaza variables en una cadena para usar con gettext. Acepta millones de parametros...
	 *
	 * @param string $str
	 * @return string
	 * @example parse("tengo %d patos en %d piletas", 5, 9);
	 */
	function _parse($str)
	{
		$vars = func_get_args();
		array_shift($vars);
		$str = _($str);
		ob_start();
		vprintf($str, $vars);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
?>