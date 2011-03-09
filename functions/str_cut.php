<?
	if (!function_exists('str_cut'))
	{
		function str_cut($str, $length, $suffix = '...')
		{
			return substr($str,0,strrpos($str," ")?strrpos(substr($str,0,$length)," "):$length).($str!=""?$suffix:"");
		}
	}
?>