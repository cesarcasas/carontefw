<?php 
import("system.gui.GuielementInterface");
	
class DropDown implements GUIElement {
	
	var $elementHTML;
	var $elementItems;
	var $elements;
	
		public function __construct($name){
			$elementHTML = "";
			$elementItems = "";
			$elements = array();
			$this->InitElement($name);
		}
	
		public function __destruct(){	
		}
		
		public function InitElement($name){
			if ($name == "") {
				$this->elementHTML = "<select>";	
			}else {
				$this->elementHTML = "<select  name='$name'>";	
			}
			
		}
	
		public function EndElement(){
			$this->elementHTML .= "</select>";
		}
		
		public function Show(){
			 $this->GetContent();
		}

		public function GetContent(){
			$this->makeOptions();
	
			$this->EndElement();

			return $this->elementHTML;	
		}
				
		public function Add($value, $txt, $style ="", $css = ""){
			$this ->elements[] = array("value" => $value, "txt"=>$txt);
			
		}
		
		public function Adopt(GUIElement $element){
			$this->elementHTML.=$element->GetContent();
		}
	
		public function makeOptions(){
			foreach ($this -> elements as $element){
				$this -> elementItems.="<option value ='{$element["value"]}'>{$element["txt"]}</option>";	
			}	
			$this ->elementHTML.=$this->elementItems;	
		}
		
}
?>
