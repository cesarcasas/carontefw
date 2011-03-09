<?
import("system.gui.GuielementInterface");
 
class TextField implements GUIElement {
	
	var $elementHTML;
	var $elementItems;
	var $elements;
	
	public function __construct($name,$size){
			$elementHTML = "";
			$this->InitElement($name);
	}
	
		public function __destruct(){	
		}
		
		public function InitElement($name){
			if ($name == "") {
				$this->elementHTML = "<input type='text'";	
			}else {
				$this->elementHTML = "<input  type='text' name=".$name." size= 10";	
				
			}
			
		}
	
	
		public function EndElement(){
			$this->elementHTML .= ">";
		}
		
		public function Show(){
			 $this->elementHTML;
		}

		public function GetContent(){
			$this->EndElement();
			return $this->elementHTML;	
		}
				
		public function Add($value, $txt, $style ="", $css = ""){ }
		
		public function Adopt(GUIElement $element){ }
	
		public function makeOptions(){ }
		
}

?>