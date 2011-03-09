<?php
class HTMLTemplates{
	
	private function __construct() {}

    public static function viewError($errors){
		GLOBAL $tpl;
		$msg=$tpl->getBlock("messages.errors");
		$erro=array();
		foreach ($errors as $e){
		$erro[]=array(
					"error"=>$e
					); 
		}
		$msg->setData("ERRORS", $erro);
		return $msg->show();
	
}

 public static function viewPager($values,$template,$pagers){
		GLOBAL $tpl;
		$tpl_pager=$tpl->getBlock("Pager.".$template);
		$tpl_pager->setVars($values);
		$tpl_pager->setData("PAGER", $pagers);
		return $tpl_pager->show();
	
}

public static function sinResult($class=""){
		GLOBAL $tpl;
		$msg=$tpl->getBlock("messages.sinResult");
		$msg->setVars(array("class"=>$class));
		return $msg->show();
	
}

public static function rangoInvalido($class=""){
		GLOBAL $tpl;
		$msg=$tpl->getBlock("messages.rangoInvalido");
		$msg->setVars(array("class"=>$class));
		return $msg->show();
	
}

public static function crearMenu(){
	global $tpl;
	global $_AppCaronte;
	$result=array();
	/*$categorias_data= CategoryPage::getAll(array());
	$tplResult=$tpl->getBlock("menues.menuCategoria");
	foreach ($categorias_data as $c){
	$result[]=array(
	
		"cat_name"=>$c['cat_name'],
		"cat_url"=>$c['cat_url'],
		"sel"=>$c['cat_url']==$_AppCaronte->Request->module?"class='sel'":""
		);
	}*/
	
	$categorias_data= Menus::getAll();
	
	$tplResult=$tpl->getBlock("menues.menuCategoria");
	foreach ($categorias_data as $c){
	$result[]=array(
	
		"cat_name"=>$c['menu_title'],
		"cat_url"=>$c['menu_url'],
		"sel"=>$c['menu_url']==$_AppCaronte->Request->module?"class='sel'":""
		);
	}
	$tplResult->setData("DATA", $result);
	echo $tplResult->show();
}

public static function crearMenuC($categoryID){
	global $tpl;
	global $_AppCaronte;
	$result=array();
	$category=CategoryPage::getInfoByCatId($categoryID);
	$pages_data = Page::getAllVisibleByCatMenu($categoryID);
	foreach ($pages_data as $p){
	$result[]=array(
	
		"cat_name"=>$category['cat_url'],
		"pag_name"=>$p['pag_name'],
		"pag_url"=>$p['pag_url'],
		"sel"=>$p['pag_url']==$_AppCaronte->Request->task?"class='sel'":""
		);
	}
	$tplResult=$tpl->getBlock("menues.menuEstaticas");
	$tplResult->setData("DATA", $result);
	echo $tplResult->show();

}
		
	}
?>