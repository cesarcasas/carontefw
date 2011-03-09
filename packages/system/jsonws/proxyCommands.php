<? 
class ProxyCommands extends Main{
	
	
	var $commands = array();
	function __construct(){
		
	}
	
	public function registerCommand($name){
		$this->commands[]=$name;
	}
	
	public function call($command, $args){
	
		if(in_array(trim($command), $this->commands)){
			//echo "llamaron!";
			return $command($args);
		}else {
			echo "Command no found".$command;
		}
	}
	
}

?>