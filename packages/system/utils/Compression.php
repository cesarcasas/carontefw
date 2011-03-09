<?php 
	/**
	 * Interface Basica para las clases que manejen mecanismos de compresion en archivos (zip, rar, ace, etc).
	 *
	 * @author Cesar Casas
	 * @version 1.0
	 * @package system.utils
	 */
	interface Compression
	{
		/**
		 * Agrega un archivo al spool de compresion
		 *
		 * @param string $file
		 */
		public function addFile($file = "");
		/**
		 * Agrega un array de archivos al spooler de compresion
		 *
		 * @param string[] $files
		 */
		public function addFiles(array $files = array());
		/**
		 * Procesa el spooler de compresion (comprime)
		 *
		 * @return void
		 */
		public function compress();
		/**
		 * Descomprime el archivo indicado
		 *
		 * @param string $path
		 * @param string $file
		 * @return void
		 */
		public function descomp($path, $file);
	}
?>