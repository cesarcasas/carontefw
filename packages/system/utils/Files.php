<?php

class Files {
   
   private $workingFolder;
      
   private $workingFolder_thumb;
   
   private $allowType;
   
   private $namingScheme;
   
   private $namingVariables;
   
   private $pointerKey;
   
   private $pointerName;
   
   final public function __construct() {
      
   }

   public function clean($name) {
      
      
      $search = 'á,à,ä,â,é,è,ë,ê,í,ì,ï,î,ó,ò,ö,ô,ú,ù,ü,û,ñ,Á,À,Ä,Â,É,È,Ë,Ê,Í,Ì,Ï,Î,Ó,Ò,Ö,Ô,Ú,Ù,Ü,Û,Ñ';
      $replac = 'a,a,a,a,e,e,e,e,i,i,i,i,o,o,o,o,u,u,u,u,n,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,N';
      $permalink = str_replace(explode(',',$search), explode(',',$replac), $name);
      
      $permalink = preg_replace('/[^a-z0-9]+/', '-', strtolower($permalink));
      if (substr($permalink, -1) == '-') $permalink = substr($permalink, 0, -1);

      return $permalink;     
   }
   
   final public function setFolder($folder, $create = false, $thumb=false) {
      
      if (!is_dir($folder)) {
         if (!$create) {
            die('Working folder doesn\'t exists.');
         }
         else {

            $created = @mkdir($folder, 0777, true);
            
            if (!$created) {
               die('Cannot create working folder.');
            }
            
         }
      }
      
      if (!is_writable($folder)) {
         $chmoded = @chmod($folder, 0777);
         
         if (!$chmoded) {
            die('Working folder is not writeable.');
         }
      }
      if(!$thumb){
      	$this->workingFolder = $folder.(substr($folder, -1) != '/' ? '/' : '');      
      }else{
      	 $this->workingFolder_thumb = $folder.(substr($folder, -1) != '/' ? '/' : '');      
      }
   }
   
    
    final public function crop($name, $width, $height, $newname=false){
	if (!$newname)
		$newname = $name;
		$width = (int)$width;
		$height = (int)$height;

	try {
		/* Read the image */
		$im = new imagick( $this->workingFolder."/".$name);

		/* create the thumbnail */
		$im->cropThumbnailImage($width, $height);

		//hack
		$geo = $im->getImageGeometry();
		$w = $geo['width'];
		$h = $geo['height'];

		if ($w < $width) {
			$im->scaleImage($width, 0);
		}

		$geo = $im->getImageGeometry();
		$w = $geo['width'];
		$h = $geo['height'];

		if ($h < $height) {
			$im->scaleImage(0, $height);
		}

		$geo = $im->getImageGeometry();
		$w = $geo['width'];
		$h = $geo['height'];

		$im->cropImage($width, $height, 0, 0);

		/* Write to a file */
		$im->writeImage( $this->workingFolder_thumb."/".$newname );
	} catch (Exception $e) {
		echo "<pre>";
		echo "".$e;
		echo "</pre>";
		die("exp: ");

		return false;
	}

	return true;
   }
   
   
   
   final public function allowTypes($string_types) {
      
      if ($string_types == '*') {
         $this->allowType = array('*');
      }
      
      $string_types = str_replace(array(' ', '.'), '', $string_types);
      $types = explode(',', $string_types);
      
      if (empty($types)) {
         die('Allow list definition is empty.');
      }
      
      $this->allowType = $types;
   }
   
   final public function name($scheme, $variables = array()) {
      if (empty($scheme)) {
         die('Naming scheme cannot be empty.');
      }
      
      $this->namingScheme = $scheme;
      $this->namingVariables = $variables;
   }
   
   final private function isAllowed($ext) {
      if (empty($this->allowType)) {
         return false;
      }
      
      if ($this->allowType[0] == '*') {
         return true;
      }
      
      return in_array($ext, $this->allowType);
   }
   
   final public function pregKeys($n) {
      if ($n[1] == '!') {
         return $this->pointerKey;
      }
      
      if ($n[1] == 'n') {
         return $this->clean($this->pointerName);
      }
            
      if (empty($this->namingVariables[$n[1]])) {
         return $n[0];
      }
      
      return $this->namingVariables[$n[1]];
   }
   
   final public function parseName($id, $name, $ext) {
      $this->pointerKey = $id;
      $this->pointerName = $name;
      $result = preg_replace_callback('|\{([0-9\!n]+)\}|i', array(&$this, 'pregKeys'), $this->namingScheme);
      
      return $result.'.'.$ext;
   }
   
   final private function processUpload($file, $id) {
      if (empty($file['name'])) {
         return array('no_file' => 'No se ha especificado un archivo.');
      }
      
      $original = $file['name'];
      if (!strpos($original, '.')) {
         return array('invalid_ext' => 'El tipo de archivo es inválido.'.strpos($original, '.'));
      }
      
      $extension = strtolower(substr($original, strripos($original, '.') + 1));
      $name = strtolower(substr($original, 0, strrpos($original, '.')));
      
      if (!$this->isAllowed($extension)) {
         return array('invalid_ext' => 'El tipo de archivo es inválido.');
      }
      
      $new_name = $this->parseName($id, $name, $extension);
      $destination = $this->workingFolder.$new_name;
      
      if (move_uploaded_file($file['tmp_name'], $destination) == false) {
         return array('cannot_move' => 'No se puede reubicar el archivo.');
      }
      
      if (!(@chmod($destination, 0777))) {
         // que hacemos si falla el chmod?
      }
      
      $uploadinfo = array(
         'original_name' => $name,
         'extension' => $extension,
         'new_name' => $new_name,
         'destination' => $destination
      );
      
      return array('file' => $uploadinfo);
      
   }
   
   final public function upload($files) {
      if (empty($this->workingFolder) || empty($this->allowType) || empty($this->namingScheme)) {
         die('Please, set working folder, type list and naming scheme before uploading.');
      }
      
      $errors = array();
      
      if (empty($files['name'])) {
         $errors['no_file'] = 'No se ha especificado un archivo.';
         
         return $errors;
      }
      
      if (is_array($files['name'])) {
         
         $return = array();
         
         for ($i = 0; $i < count($files['name']); $i++) {
            $file = array();
            foreach ($files as $k => $v) {
               $file[$k] = $v[$i];
            }
            
            $return[] = $this->processUpload($file, $i);
         }
         
         return $return;
         
      } else {
         
         return array($this->processUpload($files, 0));
      
      }
      
   }
   
   
final public function convertCMYKtoRGB($filePath){
$i = new Imagick($filePath);
$cs = $i->getImageColorspace();
$result=true;
if ($cs == Imagick::COLORSPACE_CMYK) {
    $i->setImageColorspace(Imagick::COLORSPACE_RGB);
    $i->setImageFormat('jpeg');
    $cs = $i->getImageColorspace();
    if ($cs != Imagick::COLORSPACE_CMYK) {
        $result=false;
        $i->writeImage($filePath);
    }

} else {
    $result=false;
}

if ($cs == Imagick::COLORSPACE_SRGB ||
    $cs == Imagick::COLORSPACE_RGB){
   // print "Image is RGB<br/>\n";
}
$i->clear();
$i->destroy();
$i = null;
}

final public function getresolucion($file){
	$i = new Imagick($file);
	$cs = $i->getImageResolution();
	$i->clear();
	$i->destroy();
	$i = null;
	return $cs;
}

   
}
