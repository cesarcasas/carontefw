<?
	/**
	*	Importa archivos javascript. Se utiliza la misma sintaxis que la funcion import.
	*	Si ($_GET['compress'] == 1) elimina saltos de linea y comentarios.
	* 	IMPORTANTE!! En caso de comprimir el archivo hay que eliminar comentarios de una linea y usar ";"!!!
	*
	*/
	

	
	//ob_start("ob_gzhandler");
	
	header('Content-Type: text/javascript');
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	
	$file=isset($_AppCaronte->Request->task) ? trim($_AppCaronte->Request->task) : die();
	
	
	$fileRequest=$_AppCaronte->pathApp."/".$_AppCaronte->Request->module."/".$_AppCaronte->Request->task;
	
	if(file_exists($fileRequest)){
		echo file_get_contents($fileRequest);
		die();
	}
	
	
	
	if($file=="initCache.js") {
		$_SESSION["cacheJS"]="save";
		
	}
	if($file=="endCache.js") {
		$_SESSION["cacheJS"]="get";
	}
	
	
	
	$file=substr($file, 0,strlen($file)-3);
	
	
	$contentJS="";
	if($file!="initCache" && $file!="endCache") $contentJS=importJS($file);
	
	if(isset($_SESSION["cacheJS"])){
		switch ($_SESSION["cacheJS"]){
			case 'save':
				$_SESSION["contentJS"][]=$contentJS;
				break;
			case 'get':
				foreach ($_SESSION["contentJS"] as $js){
						echo $js;
				}
				$_SESSION["contentJS"]=array();
				unset($_SESSION["cacheJS"]);
				break;
		}
	}
	else echo $contentJS;
	
	
	die();
	
	
	

	$fileString = isset($_GET['import']) ? $_GET['import'] : "";
	
	$packages = preg_split("#[,;]#", $fileString, -1, PREG_SPLIT_NO_EMPTY);
	
	foreach ($packages as $p)
	{
		$js = importJS($p);

		if (isset($_GET['compress']) && $_GET['compress'] == 1)
		{
			$js = preg_replace("#(?:\s+)|(?:/\*.*?\*/)#s", " ", $js); /* Elimina saltos de linea y comentarios */
			$js = preg_replace("#\s*([;,\{\}\[\]\(\)=<>!:?&|+*/-])\s*#", "$1", $js); /* Elimina espacios innecesarios */
		}
		echo $js;
	}
