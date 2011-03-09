<?php 
class Element {
	var $initHTML="";
	var $properties="";
	var $endHTML="";
	var $body="";
	var $lista;
	var $simpleHtml=true;
	var $textInfo="";
	
	public function __construct($type, $args=array()){
	$this->lista = array("img","br","link","input","meta");
	
	if (!$this->getBooleanValue($type)) {
			$this->simpleHtml = true;
			$this->getGenericTag($type);
			$this->processArgs($args);
	}else{
				$this->simpleHtml = false;
				$this->processArgs($args);
				$this->initHTML = $type;
	}
	
	}
	
	 public function getBooleanValue($type){
	 	return  in_array($type,$this->lista,true);
	 }
	 
	 public function getGenericTag($tag){
	  $this->initHTML = $tag;
	  $this->endHTML = "</".$tag.">";
	 }
	 
	 
	public function processArgs($args){
			
		$properties=array_keys($args);
		
		foreach ($properties as $property){
			if(is_array($args[$property])){
				
					$sp=array_keys($args[$property]);
					$parts="$property='";
					
					foreach ($sp as $s){
						$parts.="$s:{$args[$property][$s]}; ";
					}
					$parts.="'";
				
				$this->properties.=$parts;

			}
			else{
				$this->properties.= $property."='".$args[$property]."' ";	
			}
		}
		
	}
		
	public function adopt(Element $content){
		$this->body.=$content->getContent();		
	}
	
	public function getContent(){
		$htmlTag = "<".$this->initHTML." ".$this->properties;
		if ($this->simpleHtml) {
			$htmlTag.=" >".$this->body.$this->endHTML;
		}else{
			$htmlTag.= " />".$this->textInfo.$this->body;
		}
		return $htmlTag;
	}
	
	
	public function show(){
		echo $this->getContent();
	}
	
	public function __destruct(){}
	
	public function appendInfo($text){
		$this->body.=$text;
	}
	
	
}
