<?
	/**
	*	Importa archivos css. Se utiliza la misma sintaxis que la funcion import.
	*	Si ($_GET['compress'] == 1) elimina saltos de linea y comentarios.
	*/
	
	
	
	ob_start("ob_gzhandler");
	
	header('Content-Type: text/css');
	
	
	$file=isset($_AppCaronte->Request->task) ? trim($_AppCaronte->Request->task) : die();
	
	
	$fileRequest=$_AppCaronte->pathApp."/".$_AppCaronte->Request->module."/".$_AppCaronte->Request->task;
	
	if(file_exists($fileRequest)){
		echo file_get_contents($fileRequest);
		die();
	}
	
	
	
	$file=substr($file, 0,strlen($file)-4);
	echo $css = importCSS($file);
	die();
	
	
	$fileString = isset($_GET['import']) ? $_GET['import'] : "";
	
	$packages = preg_split("#[,;]#", $fileString, -1, PREG_SPLIT_NO_EMPTY);
	
		
	foreach ($packages as $p)
	{
		$css = importCSS($p);

		if (isset($_GET['compress']) && $_GET['compress'] == 1)
		{
			$css = preg_replace(
				array("#/\*(.*?)\*/|[\t\r\n]+#s", "#\s+#", "#\s*([\{;\}])\s*#s"),
				array("", " ", "$1"),
				$css
			);
		}
		echo $css;
	}
