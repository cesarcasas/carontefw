<?

class SecurityImage {
	
	var $oImage;
	var $iWidth;
	var $iHeight;
	var $iNumChars;
	var $iNumLines;
	var $iSpacing;
	var $sCode;
	var $fonts;
	var $fontPath;
		
	
	function __construct($iWidth = 100,$iHeight = 35,$iNumChars = 4,$iNumLines = 30,$iFontSize = 18, $fontPath){
		// get parameters
		
		$this->iWidth = $iWidth;
		$this->iHeight = $iHeight;
		$this->iNumChars = $iNumChars;
		$this->iNumLines = $iNumLines;
		$this->iFontSize = $iFontSize;
		$this->fontPath=$fontPath;
		
		
		// create new image
		if (function_exists("imagecreatetruecolor"))
		{
			//ver cual de las dos opciones funciona
				//	$this->oImage = imagecreate($iWidth,$iHeight);				
					$this->oImage = @imagecreatetruecolor($iWidth,$iHeight);
				
					
		}
		else
			$this->oImage = imagecreate($iWidth,$iHeight);

		// allocate white background colour
		// calculate spacing between characters based on width of image
		$this->iSpacing = (int)($this->iWidth / $this->iNumChars);
		$this->white = ImageColorAllocate($this->oImage, 255, 255, 255);
		$this->gray = ImageColorAllocate($this->oImage, 100, 100, 100);
		$this->black = ImageColorAllocate($this->oImage, 0, 0, 0);
		imagefill($this->oImage,0,0,$this->white);
		
	}
	function DrawLines() {
		//Este metodo genera un dibujo de cuadricula, lo comentamos por que no queda muy lindo que digamos.
		//randomize the canvas
		/*for($lcv=15;$lcv<$this->iHeight;$lcv+=15)
			ImageLine($this->oImage, 0, $lcv, $this->iWidth, $lcv, $this->gray);
		for($lcv=15;$lcv<$this->iWidth;$lcv+=15)
			ImageLine($this->oImage, $lcv, 0, $lcv, $this->iHeight, $this->gray);
			
		imagerectangle($this->oImage,0,0,$this->iWidth-1,$this->iHeight-1,$this->black);*/
	}
	function GenerateCode() {
		// reset code
		$this->sCode = '';
		$cs_min = (defined('CS_MIN')) ? CS_MIN : 65;
		$cs_max = (defined('CS_MAX')) ? CS_MAX : 90; 
		
		// loop through and generate the code letter by letter
		for ($i = 0; $i < $this->iNumChars; $i++) {
			// select random character and add to code string
			$this->sCode .= chr(mt_rand($cs_min, $cs_max));
		}
		
		
		
	}
	function DrawCharacters() {
		// loop through and write out selected number of characters
		$font = $this->fonts[rand(0, count($this->fonts)-1)];
		for ($i = 0; $i < strlen($this->sCode); $i++) {
			//$black = ImageColorAllocate($this->oImage, 0, 0, 0);
			$black = ImageColorAllocate($this->oImage, 127, 161, 255);
			//$angle = rand(-25,25);
			$angle = rand(-15,15);
			$yValue = rand(-5,5);
			$characterX = $this->iSpacing / 3 + $i * $this->iSpacing;
				if(function_exists('imagettftext')){
				
					imagettftext($this->oImage, $this->iFontSize, $angle, $characterX, $yValue+28, $black, $font, $this->sCode[$i]);
				}
				else {
					imagestring($this->oImage, 5, $characterX-1, $yValue+9, $this->sCode[$i], $this->black);
					imagestring($this->oImage, 5, $characterX, $yValue+10, $this->sCode[$i], $this->black);
				}
		}
	}
	
	public function getFonts($path='') {
		
		if ($handle = opendir($path)) {
		   while (false !== ($file = readdir($handle))) {
		       if ($file != "." && $file != ".." && stristr(strtolower($file), '.ttf')) {
				 $this->fonts[] = $path."/".$file;
		       }
		   }
		   closedir($handle);
		}
	}
	
	public function Create($sFilename = '') {
	// check for existence of GD GIF library
	if (!function_exists('imagejpeg')) {
		return false;
	}

	$this->DrawLines();
	$this->GenerateCode();
	$this->getFonts($this->fontPath);
	$this->DrawCharacters();

	header('Content-type: image/png');

	imagejpeg($this->oImage);

	// free memory used in creating image
	imagedestroy($this->oImage);
	$_SESSION['codeCupChat']=$this->sCode;
	return true;
	}
	function GetCode() {
		return $this->sCode;
	}
}
?>