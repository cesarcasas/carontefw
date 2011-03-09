<?php 
class GUITables{
	var $name;
	var $id;
	var $class;
	var $titles;
	var $rows;
	var $style;
	
	public function __construct($name='', $id='', $class='', $style=''){
		$this->name=$name;
		$this->id=$id;
		$this->class=$class;
		$this->titles=array();
		$this->rows=array();
		$this->style=$style;
	}
	
	public function setTitles(){
		$titles=func_get_args();
		foreach ($titles as $title) $this->titles[]=$title;
	}
	
	public function addRow(){
		$this->rows[]=func_get_args();
	}
	
	public function show($efecto=""){
		$html="<table name='$this->name' id='$this->id' class='$this->class' style='$this->style'>";
		
		$html.="<tr>";
		foreach ($this->titles as $title){
			$html.= "<th >$title</th>";
		}
		$html.="</tr>";
		$x=0;
		foreach ($this->rows as $row){
			
			$html.=$x==1?"<tr $efecto >":"<tr  >";
			$pos=0;
				foreach ($row as $col){
					$style=$col->style;
					$class=$col->class;
					$data=$col->data;
					$id=$col->id;
					$html.="<td style='$style' class='$class' id='td_".$id."_$pos' >$data</td>";
					$pos++;
				}
			$html.="</tr>";
			$x=$x==0?1:0;
			
		}
		
		$html.="</table>";
		return $html;
	}
}


class TableCeil{
	var $class;
	var $style;
	var $data;
	var $id;
	function __construct($data,$id=0,$class='', $style=''){
		$this->class=$class;
		$this->style=$style;
		$this->data=$data;
		$this->id=$id;
	}
}
?>