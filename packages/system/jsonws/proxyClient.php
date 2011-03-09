<?

class ProxyClient extends Main{
	
	
	var $url;
	function __construct($url){
		$this->url=$url;
	}
	
	public function exec($command, $args){
		
		if(!is_array($args)){
			new Exception("exec method use: String command, array args", 10);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url."/".$command);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, "my_cookies.txt"); 
    	curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt"); 
    	curl_setopt($ch, CURLOPT_REFERER, $this->url."/".$command);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11');
		curl_setopt($ch, CURLOPT_POSTFIELDS, "json=".json_encode($args)); 
		$result =curl_exec($ch);
		curl_close($ch);
		
		return $result ;
	}
}
?>