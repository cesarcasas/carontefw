<?
class Pager{
	/*
	Keys permitidos para la ejecucion del template
	{total} total de resultados encontrado
	{url_first} Url que indica la primera pagina
	{url_previous} Url que pasa a las next_page
	<b>Se encontraron {total} resultados</b>
	<a href="{url_first}"><< Primera</a>
	<a href="{url_previous}">Anterior</a>
	[PAGER]
	<a href="{url_page}" {current_page}>{page}</a>
	[/PAGER]
	<a href="{url_next}">Siguiente</a>
	<a href="{url_last}">Ultima >></a>
	
*/
	var $totalpage;
	var $url;
	var $current_page;
	var $start;
	var $parameters;
	var $totalRows;
	var $visible_result;
	var $next_pages;
	var $template;
	var $class_current_page;
	
	public function __construct($totalpage,$url,$current_page,$start=1,$parameters=array(),$totalRows=0,
	$visible_result=true,$next_pages=5,$template='default',$class_current_page="")
	{
		$this->totalpage=$totalpage;
		$this->url=$url;
		$this->current_page=$current_page;
		$this->start=$start;
		$this->parameters=$parameters;
		$this->totalRows=$totalRows;
		$this->visible_result=$visible_result;
		$this->next_pages=$next_pages;
		$this->template=$template;
		$this->class_current_page=$class_current_page;
	}
	
	public function show(){
		echo $this->getContent();
	}
	
	public function getContent(){
		$pagers=array();
		$keys_values=array();	
		$keys_values['url_style_previous']="display:none";
		$keys_values['url_style_next']="display:none";
		$keys_values['url_style_result']="display:none";
		$keys_values['url_style_last']="display:none";
		$keys_values['url_style_first']="display:none";
		$html="";
	if($this->totalRows>0 && $this->totalpage>1){
		if($this->totalpage<=$this->next_pages){
		$keys_values['url_style_last']="display:none";
		$keys_values['url_style_first']="display:none";
			
		}else{
		$keys_values['url_style_last']="display:inline";
		$keys_values['url_style_first']="display:inline";
			
		}
		if($this->visible_result && $this->totalRows>0){
			$keys_values["total"]=$this->totalRows;
			$keys_values['url_style_result']="display:inline";
		}
		if($this->next_pages>0){
			$end=$this->totalpage<($this->start+$this->next_pages-1)?$this->totalpage:$this->start+$this->next_pages-1;
		}else{
			$end=$this->totalpage;
		}
		$previous=$this->start-$this->next_pages;
		
		$parametersarray="?";
		
		for ($i=0;$i<count($this->parameters);$i++){
			$parameter=@$this->parameters[$i];
			$parametersarray.=($i==0)?$parameter['nombre']."=".$parameter['valor']:"&".$parameter['nombre']."=".$parameter['valor'];
			
		}
		
		if($this->totalRows>0 && $this->current_page>$this->next_pages-1){
			$keys_values["url_first"]=$this->url."/1/1/".$parametersarray;
		}else{
			$keys_values["url_first"]="";
		}

		if($this->current_page>$this->next_pages){
			$keys_values["url_previous"]= $this->url."/".($previous)."/".$previous."/".$parametersarray;
			$keys_values['url_style_previous']="display:inline";
		}else{
			$keys_values["url_previous"]="";
		}
		$pagers=array();
		
		for($i=$this->start;$i<=$end;$i++){
			//$valor=$i==$this->current_page?"class='".$this->class_current_page."'":"";
			$valor=$i==$this->current_page?"style='font-weight: bold'":"";
			$url=$valor?"":$this->url.'/'.$i.'/'.$this->start.'/'.$parametersarray;
			$pagers[]=array("url_page"=>$url,"current_page"=>$valor,"page"=>$i);
			if($this->next_pages>0){
			$value=$i%$this->next_pages;
			if($this->totalpage!=$end){
				if($value==0)
				{
					$next=$end+1;
					$keys_values["next"]=$next;
					$keys_values["url_next"]=$this->url."/".($next)."/".$next."/".$parametersarray;
					$keys_values['url_style_next']="display:inline";
								
				}
			}
			}
			
		}
	    if($this->next_pages>0){
		if($this->totalpage%$this->next_pages==0){
			$lastpage=$this->totalpage-($this->next_pages-1);
		}
		else {
			$value=$this->totalpage%$this->next_pages-1;
			$lastpage=$this->totalpage-$value;
		}
		}else{
			$lastpage=$i;
		}
			
			if($this->totalRows>0){
			$keys_values["url_last"]=$this->url."/".$this->totalpage."/".$lastpage."/".$parametersarray;
			}
	
			}
		
		$html=HTMLTemplates::viewPager($keys_values,$this->template,$pagers);
		return $html;
	}
	
public function getContent2(){
		$keys_values=array();	
		$html="";
	if($this->totalRows>0){
		$keys_values["total"]=$this->totalRows;
		if($this->next_pages>0){
			$end=$this->totalpage<=($this->start+$this->next_pages-1)?$this->totalpage:$this->start+$this->next_pages-1;
		}else{
			$end=$this->totalpage;
		}
		$keys_values["end"]=$end;
		$previous=$this->start-$this->next_pages;
		$parametersarray="?";
		for ($i=0;$i<count($this->parameters);$i++){
			$parameter=$this->parameters[$i];
			$parametersarray.=($i==0)?$parameter['nombre']."=".$parameter['valor']:"&".$parameter['nombre']."=".$parameter['valor'];
			
		}
		
		if($this->totalRows>0 && $this->current_page>$this->next_pages-1){
			$keys_values["url_first"]=$this->url."/1/1/".$parametersarray;
		}

		if($this->current_page>$this->next_pages-1){
			$keys_values["url_previous"]= $this->url."/".($previous)."/".$previous."/".$parametersarray;
		}else{
			$keys_values["url_previous"]="";
		}
		$pagers=array();
		
		for($i=$this->start;$i<=$end;$i++){
			$valor=$i==$this->current_page?"class='".$this->class_current_page."'":"";
			$url=$valor?"":$this->url.'/'.$i.'/'.$this->start.'/'.$parametersarray;
			$pagers[]=array("url_page"=>$url,"curren_page"=>$valor,"page"=>$i);
			if($this->next_pages>0){
			$value=$i%$this->next_pages;
			if($this->totalpage!=$end){
				if($value==0)
				{
					$next=$end+1;
					$keys_values["next"]=$next;
					$keys_values["url_next"]=$this->url."/".($next)."/".$next."/".$parametersarray;
								
				}
			}
			}
			
		}
	    if($this->next_pages>0){
		if($this->totalpage%$this->next_pages==0){
			$lastpage=$this->totalpage-($this->next_pages-1);
		}
		else {
			$value=$this->totalpage%$this->next_pages-1;
			$lastpage=$this->totalpage-$value;
		}
		}else{
			$lastpage=$i;
		}
			
			if($this->totalRows>0){
			$keys_values["url_last"]=$this->url."/".$this->totalpage."/".$lastpage."/".$parametersarray;
			}
	
			}
		
		return $keys_values;
	}
	
	/*
	public function getContent_old(){
		
	$html="";
	if($this->totalRows>0){
		$html.="<div class='".$this->class."'>";
		$html.=$this->totalRows>0?"<b> Fueron encontrados  ".$this->totalRows ." resultados </b>  ":"";
		$end=$this->totalpage<=($this->start+4)?$this->totalpage:$this->start+4;
		$previous=$this->start-5;
		$parametersarray="?";
		for ($i=0;$i<count($this->parameters);$i++){
			$parameter=$this->parameters[$i];
			$parametersarray.=($i==0)?$parameter['nombre']."=".$parameter['valor']:"&".$parameter['nombre']."=".$parameter['valor'];
			
		}
		
		$html.=$this->totalRows>0?" <a href='".$this->url."/1/1/".$parametersarray."'> << Primera</a>&nbsp; &nbsp;":""	;
		$html.=$this->current_page<=5?"<b>Anterior</b>&nbsp; | &nbsp;":"<a href='".$this->url."/".($previous)."/".$previous."/".$parametersarray."'> Anterior</a>&nbsp; | &nbsp;"		;
		for($i=$this->start;$i<=$end;$i++){
			$html.=$i==$this->current_page?"<b>".$i."</b> &nbsp;":'<a href="'.$this->url.'/'.$i.'/'.$this->start.'/'.$k.$parametersarray.'">'.$i.'</a>&nbsp;';	
			$value=$i%5;
			if($this->totalpage!=$end){
				if($value==0)
				{
					$next=$end+1;
					$html.=" | <a href='".$this->url."/".$next."/".$next."/".$parametersarray."'>Siguiente</a>";	
				}
			}
			
		}
		$html.=$this->totalpage==$end?"&nbsp; | &nbsp; <b>Siguiente</b>":"";	
		if($this->totalpage%5==0){
			$lastpage=$this->totalpage-4;
		}
		else {
			$value=$this->totalpage%5-1;
			$lastpage=$this->totalpage-$value;
		}
		
		$html.=$this->totalRows>0?" <a href='".$this->url."/".$this->totalpage."/".$lastpage."/".$parametersarray."'> &nbsp; Ultima >></a>":""		;
		}
		$html="</div>";
		return $html;
	}
*/
}
?>