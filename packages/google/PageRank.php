<?php
define('GOOGLE_MAGIC', 0xE6359A60);

class PageRank {
	private static function zeroFill($a, $b) {
		$z = hexdec(80000000);
		if ($z & $a) {
			$a = ($a>>1);
			$a &= (~$z);
			$a |= 0x40000000;
			$a = ($a>>($b-1));
		} else {
			$a = ($a>>$b);
		}
		return $a;
	}
	
	private static function toInt32(& $x){
		$z = hexdec(80000000);
		$y = (int)$x;
		if($y==-$z&&$x<-$z){
			$y = (int)((-1)*$x);
			$y = (-1)*$y;
		}
		$x = $y;
	}

	private static function mix($a,$b,$c){
		$a -= $b; $a -= $c; self::toInt32($a); $a = (int)($a ^ (self::zeroFill($c,13)));
		$b -= $c; $b -= $a; self::toInt32($b); $b = (int)($b ^ ($a<<8));
		$c -= $a; $c -= $b; self::toInt32($c); $c = (int)($c ^ (self::zeroFill($b,13)));
		$a -= $b; $a -= $c; self::toInt32($a); $a = (int)($a ^ (self::zeroFill($c,12)));
		$b -= $c; $b -= $a; self::toInt32($b); $b = (int)($b ^ ($a<<16));
		$c -= $a; $c -= $b; self::toInt32($c); $c = (int)($c ^ (self::zeroFill($b,5)));
		$a -= $b; $a -= $c; self::toInt32($a); $a = (int)($a ^ (self::zeroFill($c,3)));
		$b -= $c; $b -= $a; self::toInt32($b); $b = (int)($b ^ ($a<<10));
		$c -= $a; $c -= $b; self::toInt32($c); $c = (int)($c ^ (self::zeroFill($b,15)));
		return array($a,$b,$c);
	}

	private static function GoogleCH($url, $length=null, $init=GOOGLE_MAGIC){
		if(is_null($length)) {
			$length = sizeof($url);
		}
		$a = $b = 0x9E3779B9;
		$c = $init;
		$k = 0;
		$len = $length;
		while($len>= 12) {
			$a += ($url[$k+0] +($url[$k+1]<<8) +($url[$k+2]<<16) +($url[$k+3]<<24));
			$b += ($url[$k+4] +($url[$k+5]<<8) +($url[$k+6]<<16) +($url[$k+7]<<24));
			$c += ($url[$k+8] +($url[$k+9]<<8) +($url[$k+10]<<16)+($url[$k+11]<<24));
			$mix = self::mix($a,$b,$c);
			$a = $mix[0]; $b = $mix[1]; $c = $mix[2];
			$k += 12;
			$len -= 12;
		}
		$c += $length;
		switch($len){ /* all the case statements fall through */
			case 11: $c+=($url[$k+10]<<24);
			case 10: $c+=($url[$k+9]<<16);
			case 9 : $c+=($url[$k+8]<<8);
			/* the first byte of c is reserved for the length */
			case 8 : $b+=($url[$k+7]<<24);
			case 7 : $b+=($url[$k+6]<<16);
			case 6 : $b+=($url[$k+5]<<8);
			case 5 : $b+=($url[$k+4]);
			case 4 : $a+=($url[$k+3]<<24);
			case 3 : $a+=($url[$k+2]<<16);
			case 2 : $a+=($url[$k+1]<<8);
			case 1 : $a+=($url[$k+0]);
			/* case 0: nothing left to add */
		}
		$mix = self::mix($a,$b,$c);
		/*-------------------------------------------- report the result */
		return $mix[2];
	}

	//converts a string into an array of integers containing the numeric value of the char
	private static function strord($string){
		for($i=0;$i<strlen($string);$i++) {
			$result[$i] = ord($string{$i});
		}
		return $result;
	}

	public static function get($url){
		$url = preg_replace('/\?.*$/','?',$url);
		$reqgr = "info:".$url;
		$reqgre = "info:".urlencode($url);
		$gch = "6".self::GoogleCH(self::strord($reqgr));
		$fsock = fsockopen('toolbarqueries.google.com', 80, $errno, $errstr);
		if ( !$fsock ){
			echo 'Can not connect to server';
			return -1;
		}
		$base_get = "/search?client=navclient-auto&ch=".$gch."&ie=UTF-8&oe=UTF-8&features=Rank:FVN&q=".$reqgre;
		fputs($fsock, "GET $base_get HTTP/1.1\r\n");
		fputs($fsock, "HOST: toolbarqueries.google.com\r\n");
		fputs($fsock, "User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)\r\n");
		fputs($fsock, "Connection: close\r\n\r\n");
		$res['content']='';
		while(!feof($fsock)){
			$res['content'] .= fread($fsock, 1024);
		}
		fclose($fsock);
		if(preg_match('/Rank_.*?:.*?:(\d+)/i', $res['content'], $m)){
			return $m[1];
		} else {
			return -1;
		}
	}
}

?>