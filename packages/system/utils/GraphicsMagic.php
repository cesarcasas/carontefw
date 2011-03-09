<?php 
	/**
	 * Clase para manejar imagenes mediante la libreria Grahpics Magic
	 * 
	 * @package system.utils
	 */
	class GraphicsMagic extends Main
	{
		protected $imagePath;
		protected $commands = array();
		
		public function __construct($imagePath)
		{
			Parameter::check(array(
					'string' 	=> array($imagePath)
				),__METHOD__);
				
			$this->imagePath = $imagePath;
		}
		/**
		 * Setear ruta de imagen a modificar.
		 *
		 * @param string $imagePath
		 * @return void
		 */
		public function setSource($imagePath)
		{
			Parameter::check(array(
					'string' 	=> array($imagePath)
				),__METHOD__);
				
			$this->imagePath = $imagePath;
		}
		/**
		 * Redimensionar imagen
		 *
		 * @param string $dimension Ej: 100x120 210x x120 125%
		 * @param string $options Opciones adicionales
		 * @return void
		 */
		public function resize($dimension, $options = ">")
		{
			Parameter::check(array(
					'string' 	=> array($dimension, $options)
				),__METHOD__);
				
			$this->commands[] = '-size '.escapeshellarg($dimension).' -resize '.escapeshellarg($dimension.$options);
		}
		/**
		 * Cortar parte de la imagen. Ej: 50x50 corta 50px a los costados, arriba y abajo de la imagen
		 *
		 * @param string $dimensions
		 */
		public function shave($dimensions)
		{
			Parameter::check(array(
					'string' 	=> array($dimensions)
				),__METHOD__);
			
			$this->commands[] = '-shave '.escapeshellarg($dimensions);
		}
		/**
		 * Cambiar la relacion de aspecto de una imagen recortando las partes sobrantes.
		 * Se debe guardar cambios aplicados a la imagen antes de utilizar este método.
		 *
		 * @param integer $ratio Letterbox: 1.33, Widescreen: 1.78, Scope: 2.35
		 * @return void
		 */
		public function changeAspectRatio($ratio = 1.78)
		{
			$size = getimagesize($this->imagePath);
			$oldRatio = $size[0]/$size[1];
			$diffX = $oldRatio > $ratio ? ($size[0] - $size[1]*$ratio)/2 : 0;
			$diffY = $oldRatio < $ratio ? ($size[1] - $size[0]/$ratio)/2 : 0;
			if ($diffX || $diffY) $this->shave("{$diffX}x{$diffY}");
		}
		/**
		 * Guardar cambios aplicados a la imagen.
		 * Si se proporciona una ruta, la imagen se guardará allí.
		 * "-" indica buffer de salida y se imprimira la salida en pantalla.
		 *
		 * @param string $targetPath
		 * @return void
		 */
		public function save($targetPath = '')
		{
			Parameter::check(array(
					'string' 	=> array($targetPath)
				),__METHOD__);
				
			$command = $targetPath ? 'convert' : 'mogrify';
			$exec = "gm $command ".implode(' ', $this->commands).' '.$this->imagePath." ".$targetPath;
            //$exec = "/usr/local/bin/gm $command ".implode(' ', $this->commands).' '.$this->imagePath;
            passthru($exec, $output);
			$exec = "/usr/local/bin/gm $command ".implode(' ', $this->commands).' '.$this->imagePath." ".$targetPath;
			passthru($exec, $output);
                        file_put_contents("/tmp/gm.log", "$exec : $output");
			
		}
		
		
	}
?>