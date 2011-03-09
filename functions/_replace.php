<?
	function _replace($str)
	{
		$num = func_num_args();
		$args = func_get_args();
		for ($i=1; $i<$num; $i++) $str = str_replace('{'.$i.'}', $args[$i], $str);
		return $str;
	}
?>