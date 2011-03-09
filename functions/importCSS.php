<?
	/**
	 * Importar paquetes o unico archivo (CSS).
	 * Se utiliza el punto "." para separar directorios. El "*" representa todas los archivos dentro del paquete.
	 *
	 * @param string Ruta del paquete o archivo a incluir.
	 * @return string Codigo CSS
	 */
	function importCSS($package)
	{
		$path1=(substr($package,0,1) == "@" ? APP_ROOT_CSS : APP_COMMON_CSS);
			
			if(substr($package,0,1) == "@"){
				$package=substr($package,1);	
			}
		$path = $path1.APP_PATH_SEPARATOR.str_replace(".", APP_PATH_SEPARATOR, $package);

		$css = "";
		if (substr($path, -1) == "*")
		{
			$dirPath = substr($path, 0, -2);
			if (!is_dir($dirPath))
			{
				$css .= "/* ERROR: Package '$package' does not exist! */";
			}
			else {
				$d = opendir($dirPath);
				while (($file = readdir($d)) !== false)
				{
					if (strpos($file, '.') === 0) continue;
					$filePath = $dirPath.APP_PATH_SEPARATOR.$file;
					if (is_dir($filePath)) continue;
					$css .= "/*========== ".basename($filePath)." ==========*/\n".trim(file_get_contents($filePath))."\n";
				}
				closedir($d);
			}
		}
		else
		{
			$filePath = $path.".css";
			
			if (!file_exists($filePath)) $css .= "/* ERROR: File '$package' does not exist! */";
			else $css .= "/*========== ".basename($filePath)." ==========*/\n".trim(file_get_contents($filePath))."\n";
		}
		return $css;
	}
?>