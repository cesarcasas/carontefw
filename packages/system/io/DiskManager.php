<?php
	/**
	 * Clase para manejar archivos y directorios.
	 * 
	 * @package system.io
	 * @deprecated Utilizar FileManager en su lugar.
	 */
	class DiskManager extends Main
	{
		public function __construct() {}
		
		/**
		 * Formatear tamaño de archivo
		 *
		 * @param 	integer	$size tamaño de archivo en bytes
		 * @return 	string	tamaño formateado
		 */
		public static function formatSize($size)
		{
			$measures = array (
				0 => array("Bytes", 0),
				1 => array("KB", 0),
				2 => array("MB", 1),
				3 => array("GB", 2),
				4 => array("TB", 2)
			);
			$file_size = $size;
			for ($i=0;$file_size>=1024;$i++)
			{
				$file_size = $file_size / 1024;
			}
			$file_size = number_format($file_size, $measures[$i][1]);
			return $file_size." ".$measures[$i][0];
		}
		/**
		 * Obtener mime type del archivo. Utiliza el comando file de UNIX.
		 *
		 * @param string $path
		 * @return string
		 */
		public static function getFileMime($path)
		{
			return trim(exec('file -bi '.escapeshellarg($path)));
		}
		/**
		 * Devolver ruta de archivo unico en el directorio donde se encuentra.
		 *
		 * @param string $path Ruta completa del archivo
		 * @param boolean $cleanName Limpiar nombre de archivo y dejar solo caracteres alfanumericos
		 * @return string Ruta de archivo
		 */
		public static function getUniqueFileName($path, $cleanName = true)
		{
			$counter = 2;
			$add = '';
			$fileInfo = pathinfo($path);
			$fileName = $cleanName ? preg_replace("#[^\w]#i", "_", $fileInfo['filename']) : $fileInfo['filename'];
			$extension = isset($fileInfo['extension']) ? '.'.$fileInfo['extension'] : '';
			while (file_exists($fileInfo['dirname'].'/'.$fileName.$add.$extension))	$add = '_'.$counter++;
			return $fileInfo['dirname'].'/'.$fileName.$add;
		}
		/**
		 * Determinar si un archivo existe en un directorio segun una expresion regular
		 *
		 * @param string $dir
		 * @param string $regex
		 * @return boolean
		 */
		public static function fileExists($dir, $regex)
		{
			$i = new DirectoryIterator($dir);
			foreach ($i as $file) if (@preg_match($regex, $file->getFileName())) return true;
			return false;
		}
		
		/**
		 * Obtener informacion de archivos y directorios.
		 *
		 * @param 	string	$dir directorio padre
		 * @param 	boolean	$recursive recorrer directorio recursivamente
		 * @param 	boolean	$hiddenFiles incluir archivos ocultos
		 * @param 	string	$omit Omitir los directorios
		 * @return 	array	Cada directorio es un array que contiene la clave 'dirname' y 'files'
		  					dirname => nombre del directorio
		  					dirpath	=> ruta del directorio
		  					files	=> array que contiene arrays asociativos con info de los archivos
		  					
		  					Cada archivo es un array con los siguientes valores:
		  					name 	=> nombre completo del archivo
		  					path 	=> ruta absoluta del archivo incluyendo el nombre
		  					size	=> tamaño del archivo en bytes
		  					fsize	=> tamaño del archivo formateado (KB, MB, GB, etc)
		  					ext		=> extension del archivo
		  					mtime	=> fecha de modificacion del archivo
		  					mime	=> mime type del archivo
		  					isimage	=> indica si el archivo es una imagen
		 */
		public function getFiles($dir, $recursive = true, $hiddenFiles = false, $omit = "")
		{
			Parameter::check(array(
					"string" => array($dir, $omit),
					"boolean" => array($recursive, $hiddenFiles)
				), __METHOD__);
			
			
			$dir = preg_replace("#/$#", "", $dir);
			$i = new DirectoryIterator($dir);
			$files = array('dirname' => basename($dir), 'dirpath' => $dir, 'files' => array());
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				if (!$hiddenFiles && strpos($file->getFileName(), ".") === 0) continue;
				if ($recursive && $file->isDir())
				{
					if ($omit && !$this->omitString($omit, $dir."/".$file->getFileName())) continue;
					$files['files'][] = $this->getFiles($dir."/".$file->getFileName(), $recursive, $hiddenFiles, $omit);
				}
				else if (!$file->isDir())
				{
					$pathName = $file->getPathName();
					$extension = strtolower(substr($file->getFileName(), strrpos($file->getFileName(), ".")+1));
					if ($omit && !$this->omitString($omit, $pathName."/".$file->getFileName())) continue;
					$files['files'][] = array(
						'name' 		=> $file->getFileName(),
						'path' 		=> $pathName,
						'size' 		=> $file->getSize(),
						'fsize' 	=> self::formatSize($file->getSize()),
						'ext'		=> $extension,
						'mtime'		=> $file->getMTime(),
						'mime'		=> self::getFileMime($pathName),
						'isimage'	=> in_array($extension, array("jpg","jpeg","gif","png","bmp","tif","tiff"))
					);
				}
			}
			
			return $files;
		}
		/**
		 * Determinar si una palabra existe en un nombre de archivo.
		 *
		 * @param string $string Cadena a buscar
		 * @param string $line Nombre de archivo
		 * @return boolean
		 */
		protected function omitString($string = "", $line = "")
		{
			if($string == "") return true;
			$pars = split(",", $string);
			foreach ($pars as $par) if(strstr($line, $par)) return false;
			return true;
		}
		
		/**
		 * Obtener archivos descendientes del directorio en un array unidimensional.
		 *
		 * @param 	string	$dir directorio padre
		 * @param 	boolean $includeDirs incluir directorios
		 * @param 	boolean	$recursive recorrer directorio recursivamente
		 * @param 	boolean	$hiddenFiles incluir archivos ocultos
		 * @param 	string 	$regex Devolver archivos que coincidan con la expresion regular
		 * @return 	mixed[][]	Cada archivo es un array con las siguientes claves:
			  					name 	=> nombre completo del archivo
			  					path 	=> ruta absoluta del archivo incluyendo el nombre
			  					size	=> tamaño del archivo en bytes
			  					fsize	=> tamaño del archivo formateado (KB, MB, GB, etc)
			  					ext		=> extension del archivo
			  					mtime	=> fecha de modificacion del archivo
			  					mime	=> mime type del archivo
			  					isimage	=> indica si el archivo es una imagen
		 */
		public function getFilesArray($dir, $includeDirs = false, $recursive = true, $hiddenFiles = false, $regex = '')
		{
			Parameter::check(array(
					"string" => array($dir, $regex),
					"boolean" => array($includeDirs, $recursive, $hiddenFiles)
				), __METHOD__);
			
			$dir = preg_replace("#/$#", "", $dir);
			$i = new DirectoryIterator($dir);
			$files = array();
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				if (!$hiddenFiles && strpos($file->getFileName(), ".") === 0) continue;
				if ($recursive && $file->isDir())
				{
					$files = array_merge($files, $this->getFilesArray($dir."/".$file->getFileName(), $includeDirs, $recursive, $hiddenFiles, $regex));
				}
				if (!$file->isDir() || $file->isDir() && $includeDirs)
				{
					$pathName = $file->getPathName();
					$name = $file->getFileName();
					if ($regex && !@preg_match($regex, $name)) continue;
					$extension = $file->isDir() ? '' : strtolower(substr($file->getFileName(), strrpos($file->getFileName(), ".")+1));
					$files[] = array(
						'name' 		=> $name,
						'dirpath'	=> $dir,
						'path' 		=> $pathName,
						'size' 		=> $file->getSize(),
						'fsize' 	=> self::formatSize($file->getSize()),
						'ext'		=> $extension,
						'mtime'		=> $file->getMTime(),
						'mime'		=> self::getFileMime($pathName),
						'isimage'	=> in_array($extension, array("jpg","jpeg","gif","png","bmp","tif","tiff"))
					);
				}
			}

			return $files;
		}
	}
?>