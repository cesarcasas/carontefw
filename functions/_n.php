<?
	if (!function_exists('_n'))
	{
		/**
		 * Alias de ngettext(). Retorna el 1er parametro si $count == 1 o el 2do si es > 1
		 *
		 * @param string $str1
		 * @param string $str2
		 * @param integer $count
		 * @return string
		 */
		function _n($str1, $str2, $count)
		{
			return ngettext($str1, $str2, $count);
		}
	}
?>