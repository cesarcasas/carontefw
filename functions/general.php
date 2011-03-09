<?php 
/**
 * Parser Objecto to Array
 *
 * @param object $objeto
 * @return array
 */
function ObjectToArray($object){

		$data[]="";
          if(!(is_array($object) || is_object($object))){ //si no es un objeto ni un array
              $data = $object; 
          } 
          else { 
              foreach($object as $key => $value){ 
                  $data[$key] = ObjectToArray($value); 
              }
          }
          return $data;
}






function totalClearUrl($url,$s=''){
	$url=strtolower($url);
	
	$url=str_replace("&aaqute;", "a", $url);
	$url=str_replace("&eaqute;", "e", $url);
	$url=str_replace("&iaqute;", "i", $url);
	$url=str_replace("&oaqute;", "o", $url);
	$url=str_replace("&uaqute;", "u", $url);
	
	
	$url=str_replace("á", "a",$url);
	$url=str_replace("é", "e",$url);
	$url=str_replace("í", "i",$url);
	$url=str_replace("ó", "o",$url);
	$url=str_replace("ú", "u",$url);
	$url=str_replace("ñ", "n", $url);
	$url=str_replace("Ñ", "n", $url);
	
	$url=str_replace("?","",$url);
	$url=str_replace("¿","",$url);
	
	
	
	$url=str_replace(" ","aaabbbccc",$url);
    

	$url=preg_replace("/[^$s\w]/", "",$url);
	$url=str_replace("iquest","",$url);
	$url=str_replace("aaabbbccc","-",$url);
	return $url;
}

function totalClearkey($url,$s=''){
	$url=strtolower($url);
	
	$url=str_replace("&aaqute;", "a", $url);
	$url=str_replace("&eaqute;", "e", $url);
	$url=str_replace("&iaqute;", "i", $url);
	$url=str_replace("&oaqute;", "o", $url);
	$url=str_replace("&uaqute;", "u", $url);
	
	
	$url=str_replace("á", "a",$url);
	$url=str_replace("é", "e",$url);
	$url=str_replace("í", "i",$url);
	$url=str_replace("ó", "o",$url);
	$url=str_replace("ú", "u",$url);
	
	$url=str_replace("?","",$url);
	$url=str_replace("¿","",$url);
	
	
	$url=str_replace(" ","aaabbbccc",$url);
        $url=str_replace("�", "n", $url);

	$url=preg_replace("/[^$s\w]/", "",$url);
	$url=str_replace("iquest","",$url);
	$url=str_replace("aaabbbccc","-",$url);
	return $url;
}


$replaces = array (
	  '/'	=>	'__b__',
	  '"'	=>	'__c__',
	  "'"	=>	'__sc__',
	  ','	=>	'__cm__',
	  'Ñ'	=>	'__nt__',
	  'ñ'	=>	'__ni__',
	  '+'	=>	'__m__',
	  '-'	=>	'__g__',
	);
	
function toUrlF ($word) {
		global $replaces;
		$word = utf8_decode($word);
		$word = trim($word);
		$word = preg_replace ('/\s{2,}/' ,' ',$word);
		$word = str_replace (' ','_',$word);
		$word = strtr($word,utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðòóôõöøùúûýýþÿŔŕ'),'aaaaaaaceeeeiiiidoooooouuuuybsaaaaaaaceeeeiiiidoooooouuuyybyRr');

		foreach ($replaces as $k => $w) {
			$word = str_replace (utf8_decode($k),$w,$word);
		}

		$word = urlencode($word);
		//$word = str_replace ('%B1','__nt__',$word);
		return utf8_encode($word);
	}
	
	
function fromUrlF ($word) {
	global $replaces;
	$word = utf8_decode($word);
	$word = trim($word);
	
	foreach ($replaces as $k => $w) {
		$word = str_replace ($w,utf8_decode($k),$word);
	}
	
	$word = str_replace ('__p__','.',$word);
	
	$word = str_replace ('_',' ',$word);
	$word = urldecode($word);
	return utf8_encode($word);
}


	
	/**
	 * Formatear fecha y devolver la diferencia, para la vista lounge
	 *
	 * @param integer $timestamp
	 */
	function formatDate($timestamp)
	{
		$diff = time() - $timestamp;
		if ($diff > 0 && $diff < 60) $date = sprintf(_n("%s segundo atrás", "%s segundos atrás", $diff), $diff);
		else if ($diff >= 60 && $diff < 3600) {
			$diff = floor($diff/60);
			$date = sprintf(_n("%s minuto atrás", "%s minutos atrás", $diff), $diff);
		}
		else if ($diff >= 3600 && $diff < 86400) {
			$diff = floor($diff/3600);
			$date = sprintf(_n("%s hora atrás", "%s horas atrás", $diff), $diff);
		}
		else if ($diff >= 86400) {
			$diff = floor($diff/86400);
			$date = sprintf(_n("%s día atrás", "%s días atrás", $diff), $diff);
		}
		return $date;
	}
	
	
	
	
	function getDateDistance($date){
		
		$diff=time()-$date;
		
		$second=1;
		$minute=$second*60;
		$hour=$minute*60;
		$day=$hour*24;
		$mount=$day*30;
		
		
		
		if($diff<$minute){
			$diff=round($diff/$second);
			return "$diff segundos";
		}
		
		elseif ($diff>$minute && $diff<$hour){
			$diff=round($diff/$minute);
			return " $diff minutos";
		}
		
		elseif ($diff>$hour && $diff<$day){
			$diff=round($diff/$hour);
			return " $diff horas";
		}
		
		elseif ($diff>$day && $diff<$mount){
			$diff=round($diff/$day);
			return " $diff dias";
		}
		
		elseif ($diff>$mount) {
			$diff=round($diff/$mount);
			return " $diff meses";
		}
		return $date;
	}



function html2rgb($color)
{
	if(strlen($color)>4){
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
	}
    else 
    return array();
}

function ByteSize($bytes) 
    {
    $size = $bytes / 1024;
    if($size < 1024)
        {
        $size = number_format($size, 2);
        $size .= ' KB';
        } 
    else 
        {
        if($size / 1024 < 1024) 
            {
            $size = number_format($size / 1024, 2);
            $size .= ' MB';
            } 
        else if ($size / 1024 / 1024 < 1024)  
            {
            $size = number_format($size / 1024 / 1024, 2);
            $size .= ' GB';
            } 
        }
    return $size;
    }
    
function mesSiguente($mess,$anio){
	
   $ultimo = date("t",mktime(0, 0, 0, $mess, 1, $anio));
    if($mess == '12' || $mess == '1'){
        if($mess == '12'){
            $next = 1;
            $prev = $mess -1;
            $anion = $anio + 1;
            $aniop = $anio;
        }
        if($mess == '1'){
            $next = $mess + 1;
            $prev = 12;
            $anion = $anio;
            $aniop = $anio -1;        
        }
    }else{
        $next = $mess + 1;
        $prev = $mess - 1;    
        $aniop = $anio;
        $anion = $anio;
    }
	 return array('mes'=>$next,'anio'=>$anion);
}

function mesAnterior($mess,$anio){
	
   $ultimo = date("t",mktime(0, 0, 0, $mess, 1, $anio));
    if($mess == '12' || $mess == '1'){
        if($mess == '12'){
            $next = 1;
            $prev = $mess -1;
            $anion = $anio + 1;
            $aniop = $anio;
        }
        if($mess == '1'){
            $next = $mess + 1;
            $prev = 12;
            $anion = $anio;
            $aniop = $anio -1;        
        }
    }else{
        $next = $mess + 1;
        $prev = $mess - 1;    
        $aniop = $anio;
        $anion = $anio;
    }
	 return array('mes'=>$prev,'anio'=>$aniop);
}