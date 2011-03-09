<?
	if (!function_exists('array_collect'))
	{
		/**
		 * Recolectar valores para una columna de una matrix
		 *
		 * @param array[] $array
		 * @param string $key
		 * @return array
		 */
		function array_collect(array $array, $key)
		{
			$elements = array();
			foreach ($array as $a) if (array_key_exists($key, (array)$a)) $elements[] = $a[$key];
			return $elements;
		}
	}
?>