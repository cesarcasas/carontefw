<?php
	import("system.io.IOException");
	
	/**
	 * Clase para manejar archivos y directorios. Reemplaza a DiskManager.
	 * 
	 * @package system.io
	 */
	class FileManager extends Main
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
			Parameter::check(array(
					"string" => array($path),
					"boolean" => array($cleanName)
				), __METHOD__);
			
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
			Parameter::check(array(
					"string" => array($dir, $regex)
				), __METHOD__);
			
			$i = new DirectoryIterator($dir);
			foreach ($i as $file) if (@preg_match($regex, $file->getFileName())) return true;
			return false;
		}
		
		/**
		 * Renombrar archivos segun expresion regular.
		 *
		 * @param string $dir Directorio donde buscar
		 * @param string $find Expresión regular. Matchea con el nombre completo del archivo incluyendo la extension.
		 * @param string $replace 2do parametro para preg_replace
		 * @param boolean $recursive Renombrar recursivamente
		 * @return integer Cantidad de archivos renombrados
		 * @throws IOException Si ocurrió un error al renombrar
		 */
		public function rename($dir, $find, $replace, $recursive = false)
		{
			Parameter::check(array(
					"string" => array($dir, $find, $replace),
					"boolean" => array($recursive)
				), __METHOD__);
			
			$count = 0;
			$i = new DirectoryIterator($dir);
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				$name = $file->getFileName();
				if ($file->isDir() && $recursive) $count += $this->rename($file->getPathName(), $find, $replace, $recursive);
				if (@preg_match($find, $name))
				{
					$newName = @preg_replace($find, $replace, $name);
					$dir = dirname($file->getPathName());
					if (!@rename($file->getPathName(), $dir.'/'.$newName))
						throw new IOException("Couldn't rename file ".$file->getPathName());
					$count++;
				}
			}
			return $count;
		}
		
		/**
		 * Mover archivos a otro directorio segun expresion regular.
		 *
		 * @param string $sourceDir Directorio donde buscar
		 * @param string $destinationDir Directorio donde mover los archivos
		 * @param string $regex Expresion regular para buscar archivos
		 * @param boolean $recursive Renombrar recursivamente
		 * @return integer Cantidad de archivos movidos
		 * @throws IOException Si ocurrió un error al mover
		 */
		public function move($sourceDir, $destinationDir, $regex, $recursive = false)
		{
			Parameter::check(array(
					"string" => array($sourceDir, $destinationDir, $regex),
					"boolean" => array($recursive)
				), __METHOD__);
			
			$count = 0;
			$destinationDir = preg_replace("#[/\\\\]$#", "", $destinationDir);
			$i = new DirectoryIterator($sourceDir);
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				$name = $file->getFileName();
				if ($file->isDir() && $recursive) $count += $this->move($file->getPathName(), $destinationDir, $regex, $recursive);
				if (@preg_match($regex, $name))
				{
					if (!@rename($file->getPathName(), $destinationDir.APP_PATH_SEPARATOR.$name))
						throw new IOException("Couldn't move file ".$file->getPathName());
					$count++;
				}
			}
			return $count;
		}
		
		/**
		 * Eliminar archivos segun expresion regular.
		 *
		 * @param string $dir Directorio donde buscar
		 * @param string $regex Expresión regular
		 * @param boolean $recursive Eliminar recursivamente
		 * @return integer Cantidad de archivos eliminados
		 * @throws IOException Si ocurrió un error al eliminar archivo
		 */
		public function delete($dir, $regex, $recursive = false)
		{
			Parameter::check(array(
					"string" => array($dir, $regex),
					"boolean" => array($recursive)
				), __METHOD__);
			
			$count = 0;
			$i = new DirectoryIterator($dir);
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				$name = $file->getFileName();
				if ($file->isDir() && $recursive) $count += $this->delete($file->getPathName(), $regex, $recursive);
				if (@preg_match($regex, $name))
				{
					if (!@unlink($file->getPathName()))
						throw new IOException("Couldn't delete file ".$file->getPathName());
					$count++;
				}
			}
			return $count;
		}
		
		/**
		 * Obtener archivos descendientes del directorio en un array unidimensional.
		 *
		 * @param 	string	$dir directorio padre
		 * @param 	boolean $includeDirs incluir directorios
		 * @param 	boolean	$recursive recorrer directorio recursivamente
		 * @param 	boolean	$hiddenFiles incluir archivos ocultos
		 * @param 	string 	$regex Devolver archivos que coincidan con la expresion regular
		 * @param 	string	$sortBy Columna a usar para ordenar la lista en forma ascendente. Los posibles valores son las mismas claves de cada archivo (ver abajo)
		 * @param 	integer $sortMode Modo de ordenamiento. Debe ser una de las constantes utilizadas en array_multisort()
		 * @return 	mixed[][]	Cada archivo es un array con las siguientes claves:
			  					name 	=> nombre completo del archivo
			  					path 	=> ruta absoluta del archivo incluyendo el nombre
			  					size	=> tamaño del archivo en bytes
			  					fsize	=> tamaño del archivo formateado (KB, MB, GB, etc)
			  					ext		=> extension del archivo
			  					mtime	=> fecha de modificacion del archivo
			  					mime	=> mime type del archivo
			  					isdir	=> indica si es un directorio
			  					isimage	=> indica si el archivo es una imagen
		 */
		public function listFiles($dir, $includeDirs = false, $recursive = true, $hiddenFiles = false,
									$regex = '', $sortBy = '', $sortMode = SORT_ASC)
		{
			Parameter::check(array(
					"string" => array($dir, $regex, $sortBy),
					"boolean" => array($includeDirs, $recursive, $hiddenFiles)
				), __METHOD__);
			
			$dir = preg_replace("#[/\\\\]$#", "", $dir);
			$i = new DirectoryIterator($dir);
			$files = array();
			foreach ($i as $file)
			{
				if ($file->isDot()) continue;
				if (!$hiddenFiles && strpos($file->getFileName(), ".") === 0) continue;
				if ($recursive && $file->isDir())
				{
					$files = array_merge($files, $this->listFiles($dir.APP_PATH_SEPARATOR.$file->getFileName(), $includeDirs, $recursive, $hiddenFiles, $regex));
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
						'isdir'		=> $file->isDir(),
						'isimage'	=> in_array($extension, array("jpg","jpeg","gif","png","bmp","tif","tiff"))
					);
				}
			}
			if (!empty($files) && $sortBy)
			{
				foreach ($files as $f) $cols[] = $f[$sortBy];
				array_multisort($cols, $sortMode, $files);
			}
			return $files;
		}
	}
?>