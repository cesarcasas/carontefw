<?php 

class Blocks extends Main{
	
	
	var $template;
	protected $vars;
	
	public function __construct($package, $internal = false){
			
			if ($internal) {
				$path1=FW_TEMPLATEBLOCKS_PATH;
			}
			else {
				$path1=APP_TEMPLATEBLOCKS_PATH;
			}
			$path = $path1.APP_PATH_SEPARATOR.str_replace(".", APP_PATH_SEPARATOR, $package);
			
			$filePath = $path.".tpl";
				$tpl="";
				
				$extra = $internal ? ' in framework folder' : '';
				
				if (!file_exists($filePath)) $tpl .= "/* ERROR: File '$package'{$extra} does not exist! */";
				else $tpl.= trim(file_get_contents($filePath))."\n";
			
			$this->template=$tpl;
			$this->vars=array();
	}

	
	
	public function setVars($vars=array()){
		$this->vars=$vars;
	}
	
	public function setConditional($keyword, $value) {
	   
	   $value = (empty($value) ? false : true);
	   
	   $end_offset = strlen($keyword) + 6;
	   $init = (int)strpos($this->template, "[IF:{$keyword}]");
	   $end = (int)strpos($this->template, "[/IF:{$keyword}]") + $end_offset;
	   
		if ($end-$end_offset <= $init) {
		   return false;
		}

		$preTemplate=substr($this->template, 0, $init);
		$postTemplate=substr($this->template, $end);
		$partTemplate=substr($this->template, $init, ($end-$init));	   
      $partTemplate=str_replace(array("[IF:{$keyword}]","[/IF:{$keyword}]"),array("",""),$partTemplate);
      
		$this->template = $preTemplate;

		if ($value) {
		   $this->template .= $partTemplate;
		}
		
		$this->template .= $postTemplate;
		
	}
	
	public function setData($keyword, $data){
		
	   $end_offset = strlen($keyword) + 3;
		
		$init=(int)strpos($this->template, "[$keyword]");
		
		$end=(int)strpos($this->template, "[/".$keyword."]")+$end_offset;
		
		if ($end-$end_offset <= $init) {
		   return false;
		}
		
		$preTemplate=substr($this->template, 0, $init);
		
		
		$postTemplate=substr($this->template, $end);
		$partTemplate=substr($this->template, $init, ($end-$init));
		
		
		$partTemplate=str_replace(array("[$keyword]","[/$keyword]"),array("",""),$partTemplate);
		
		$tmp="";
		
		$odd = 0;
		foreach ($data as $reg){
			
			$reg_output = $this->doForEach($partTemplate, $reg);
			
			$reg_output = $this->replaceVars($reg_output, $reg, $odd);
			
			$tmp.=$reg_output; 

			$odd++;
		}
		
		
		$this->template=$preTemplate;
		$this->template.=$tmp;
		$this->template.=$postTemplate;
		
	}
	

	private function doForEach($txt, $data) {
		$init = strpos($txt, '[FOR:EACH]');
		$end = strpos($txt, '[/FOR:EACH]');
			
		if (($init == false) || ($end === false)) return $txt;	
		
		if ($init > $end) return $txt;
		
		$pre = substr($txt, 0, $init);
		$post = substr($txt, $end + 11);
		
		$part = substr($txt, $init+10, $end-$init-10);
		
		$return = '';
		
		foreach ($data as $key => $value) {
			$return .= str_replace(array('{fe:key}', '{fe:value}'), array($key, $value), $part);
		}
		
		$return = $pre.$return.$post;
		
		return $return;		
	}

	
	private function replaceVars($string="", $data=false, $odd = 0){
			
	$keys= is_array($data) ? array_keys($data) : array_keys($this->vars);
	$data=is_array($data)  ? $data : $this->vars;
	
		
		
	
	$txt=$string!="" ? $string : $this->template;	
		
		foreach ($keys as $key){
			
			@$txt=str_replace("{".$key."}", $data[$key], $txt);

		}

		$txt=str_replace("{@autozebra}", ($odd % 2 ? 'class="alt"' : ''), $txt);
		
		if($string=="") $this->template=$txt;
		else return $txt;
	}	
	
	
	
	public function show(){
		$this->replaceVars();
		return $this->template;
	}
}