<?
	/**
	 * Importar paquetes o unico archivo (JS).
	 * Se utiliza el punto "." para separar directorios. El "*" representa todas los archivos dentro del paquete.
	 *
	 * @param string Ruta del paquete o archivo a incluir.
	 * @return string Codigo JS
	 */
	function importJS($package)
	{
		
		$path1=(substr($package,0,1) == "@" ? APP_ROOT_JS : APP_COMMON_JS);
			
			if(substr($package,0,1) == "@"){
				$package=substr($package,1);	
			} 
		$path = $path1 . APP_PATH_SEPARATOR . str_replace(".", APP_PATH_SEPARATOR, $package);	
		$js = "";
		if (substr($path, -1) == "*")
		{
			$dirPath = substr($path, 0, -2);
			if (!is_dir($dirPath))
			{
				$js .= "throw \"Package '$package' does not exist!\";";
			}
			else {
				$d = opendir($dirPath);
				while (($file = readdir($d)) !== false)
				{
					if (strpos($file, '.') === 0) continue;
					$filePath = $dirPath.APP_PATH_SEPARATOR.$file;
					if (is_dir($filePath)) continue;
					$js .= "/*========== ".basename($filePath)." ==========*/\n".trim(file_get_contents($filePath))."\n";
				}
				closedir($d);
			}
		}
		else
		{
			$filePath = $path.".js";
			if (!file_exists($filePath)) $js .= "throw \"File '$package' not found!\";";
			else $js .= "/*========== ".basename($filePath)." ==========*/\n".trim(file_get_contents($filePath))."\n";
		}
		return $js;
	}

	
