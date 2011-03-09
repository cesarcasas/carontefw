<?
	/**
	 * Importar paquetes o unico archivo
	 *
	 * @param string Ruta del paquete o archivo a incluir. Se utiliza el punto "." para separar directorios. El "*" representa todas las clases dentro del paquete, excepto subpaquetes. Acepta infinitos parametros.
	 * @return void
	 */
	function import() {
		$packages = func_get_args();
		foreach ($packages as $package) {
			$path1=(substr($package,0,1) == "@" ? APP_ROOT_PACKAGES : APP_PACKAGES);
			
			if(substr($package,0,1) == "@"){
				$package=substr($package,1);	
			} 
				
						
			$path = $path1.APP_PATH_SEPARATOR.str_replace(".", APP_PATH_SEPARATOR, $package);
			if (substr($path, -1) == "*") {
				$dirPath = substr($path, 0, -2);
				if (!is_dir($dirPath)) {
					@ob_clean();
					ob_start();
					debug_print_backtrace();
					$stack = ob_get_contents();
					ob_end_clean();
					$error = "Package <em>$dirPath</em> does not exist!<pre>$stack</pre>";
					die($error);
				}
				getFiles($dirPath);
			}
			else {
				$file = $path.".php";
				if (!file_exists($file)) {
					@ob_clean();
					ob_start();
					debug_print_backtrace();
					$stack = ob_get_contents();
					ob_end_clean();
					$error = "File <em>$file</em> not found!<pre>$stack</pre>";
					die($error);
				}
				require_once($file);
			}
		}
	}

	/**
	 * Comprueba si se pueden importar paquetes o unico archivo
	 *
	 * @param string Ruta del paquete o archivo a incluir. Se utiliza el punto "." para separar directorios. El "*" representa todas las clases dentro del paquete, excepto subpaquetes. Acepta infinitos parametros.
	 * @return void
	 */
	function check_import() {
		$packages = func_get_args();
		foreach ($packages as $package) {
			$path1=(substr($package,0,1) == "@" ? APP_ROOT_PACKAGES : APP_PACKAGES);
			
			if(substr($package,0,1) == "@"){
				$package=substr($package,1);	
			} 
				
						
			$path = $path1.APP_PATH_SEPARATOR.str_replace(".", APP_PATH_SEPARATOR, $package);
			if (substr($path, -1) == "*") {
				$dirPath = substr($path, 0, -2);
				if (!is_dir($dirPath)) {
					return false;
				}
			}
			else {
				$file = $path.".php";
				if (!file_exists($file)) {
					return false;
				}
			}
		}
		
		return true;
	}

	/**
	 * Incluir todos los archivos de un determinado directorio, puede ser recursiva o no.
	 * Esta funcion es utilizada internamente por la función import.
	 *
	 * @param string $dirName
	 * @param boolean $recursive
	 * @return void
	 */
	function getFiles($dirName, $recursive = false) {
		$d = opendir($dirName);
		while (($file = readdir($d)) !== false) {
			if (strpos($file, '.') !== 0) {
				$filePath = $dirName.APP_PATH_SEPARATOR.$file;
				if (!is_dir($filePath)) {
					if (!file_exists($filePath)) {
						@ob_clean();
						ob_start();
						debug_print_backtrace();
						$stack = ob_get_contents();
						ob_end_clean();
						$error = "File <em>$filePath</em> not found!<pre>$stack</pre>";
						die($error);
					}
					if (substr($filePath, strrpos($filePath, '.')+1) == 'php') require_once($filePath);
				}
				else if ($recursive) {
					getFiles($filePath, $recursive);
				}
			}
		}
		closedir($d);
	}