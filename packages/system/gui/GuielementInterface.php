<?
interface GUIElement{
	function InitElement($name);
	function EndElement();
	function Show();
	function GetContent();
	function Add($value, $text, $style ="", $css = "");
	function Adopt(GUIElement $element);
}
?>