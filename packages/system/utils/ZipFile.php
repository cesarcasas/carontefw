<?php 
/**
 * This class use by compress file. 
 * 
 * Esta clase esta documentada como el orto y no implementa los estandares de nomenclatura especificados.
 * 
 *	@author Cesar Casas
 *  @version 1.0
 * 	@example $com=new ZipFile("file.gz");
 * 	@package system.utils
 * 	@return Compression object
 * @deprecated 
 */

import ("system.utils.Compression");

class ZipFile extends Main implements Compression
{
	
	/**
	 * Zip file name
	 *
	 * @var string
	 */
	private $FileNameGZ;
	
	/**
	 * Archivos a comprimir
	 *
	 * @var string[]
	 */
	private $FilesForCompress;
	
	/**
	 * El path donde va el archivo comprimido
	 *
	 * @var array
	 */
	private $FilesForCompressPath;

	/**
	 * Constructor 
	 *
	 * @param String $file
	 */
	function __construct($file=""){
	Parameter::check(array('string' => $file), __METHOD__);
	$this->FilesForCompress= array();
	if($file!="") $this->FileNameGZ=$file;
	}//fin del constructor

	
	/**
	 * Devuelve el array de archivos a comprimir
	 * 
	 * @return array
	 */
	
	public function getObjects(){
		return $this->FilesForCompressPath;
	}
	/**
	 * Seteamos el nombre del archivo comprimido a crear
	 *
	 * @param string $file
	 */
	function setFileName($file=""){
	Parameter::check(array('string' => $file),  __METHOD__);
	if($file!="") $this->FileNameGZ=$file;
	}//seteamos el nombre
	
	/**
	 * Agrega un archivo a la cola de compresion
	 * El $remove es una la porcion de cadena a limpiar (reemplazar) en el path del archivo dentro del  .zip. Si es vacio, tomara el path original.
	 * 
	 * @param string $file
	 * @param string $remove
	 */
	function addFile($file="", $remove=""){
		
		Parameter::check(array('string' => array($file, $remove)),  __METHOD__);
		
		if($file!="") {
			if(file_exists($file)) 
					$this->FilesForCompress[$file]=file_get_contents($file);
					$this->FilesForCompressPath[$file]=$remove;
					
		}//agrregamos todo
	}//fin del metodo AddFile
	
	/**
	 * Agregamos un archivo que creamos en el momento (pasamos el contenido)
	 *
	 * @param string $name
	 * @param string $content
	 * @param string $remove
	 */
	public function addVirtualFile($name="", $content="",$remove=""){
		
		$this->FilesForCompress[$name]=$content;
		$this->FilesForCompressPath[$name]=$remove;
	}
	
	/**
	 * Limpia una cadena de otra que se le pasa
	 *
	 * @param string $pathori
	 * @param string $pathremove
	 * @return string
	 */
	public function removePath($pathori="",$pathremove=""){
		
		if($pathremove=="") {
			$vv=split("/",$pathori);
			return array_pop($vv);
		}
		$pars=split(";",$pathremove);

		for($x=0;$x<count($pars); $x++) 			
			$pathori=str_replace($pars[$x],"",$pathori);
		
		return $pathori;
	}
	
	/**
	 * Agrega un array de archivos a la cola de compresion
	 * 
	 * Se debe mandar un array de dos posiciones.
	 *
	 * @param array $files
	 */
	function AddFiles($files=array()){
		
		if(count($files)>0) {
			foreach ($files as $fil) {
				$keys=array_keys($fil);
					$this->AddFile($fil[$keys[0]],$fil[$keys[1]]);
			}//fin del foreach
		}//fin del if
	}//agregamos un array de archivos
	
	/**
	 * Process the zipfile (create).
	 *
	 * @return boolean
	 */
	function Compress(){
	
	
					$ZipData = array(); 
					$Dircont = array(); 
					$DirFile = array();
					$offseto = 0;		
					
					//tomamos los datos de las propiedades
					$namezip=$this->FileNameGZ;
					$struct=$this->FilesForCompress;
					while(list($file,$data)=each($struct))
					{	
						$file=$this->removePath($file,$this->FilesForCompressPath[$file]);
						$file= str_replace("\\", "/", $file);  
						
					    $dir=explode("/",$file);
						for($i=0; $i<sizeof($dir); $i++)if($dir[$i]=="")unset($dir[$i]);					
						
						$ele=0;  		  //Nivel actual
						$dirname="";	  //Nombre archivo o directorio
						
						
						$num=count($dir); //Total de niveles
						
						while(list($idx,$val)=each($dir))
						{	
							$ty=(++$ele)==$num?true:false;
							$ty=trim($data)!=""?$ty:false;//Compruebar si el ultimo elemento es directorio o archivo
							$dirname.=$val.($ty?"":"/");
							if(isset($DirFile[$dirname]))continue; else $DirFile[$dirname]=true;			
							$gzdata="";				
							if($ty)
							{	
								$unziplen=strlen($data);  
								$czip=crc32($data);  
								$gzdata=gzcompress($data);  
								$gzdata=substr(substr($gzdata,0,strlen($gzdata)-4),2); 
								$cziplen=strlen($gzdata);  
							}						
							$ZipData[]="\x50\x4b\x03\x04".($ty?"\x14":"\x0a")."\x00\x00\x00".($ty?"\x08":"\x00")."\x00\x00\x00\x00\x00".
									   pack("V",$ty?$czip:0).pack("V",$ty?$cziplen:0).pack("V",$ty?$unziplen:0).pack("v",strlen($dirname)). 
									   pack("v",0).$dirname.$gzdata.pack("V",$ty?$czip:0).pack("V",$ty?$cziplen:0).pack("V",$ty?$unziplen:0);
							$Dircont[]="\x50\x4b\x01\x02\x00\x00".($ty?"\x14":"\x0a")."\x00\x00\x00".($ty?"\x08":"\x00")."\x00\x00\x00\x00\x00".
									   pack("V",$ty?$czip:0).pack("V",$ty?$cziplen:0).pack("V",$ty?$unziplen:0).pack("v",strlen($dirname)). 
									   pack("v", 0 ).pack("v",0).pack("v",0).pack("v",0).pack("V",$ty?32:16).pack("V",$offseto).$dirname;  		
							$offseto=strlen(implode("",$ZipData));			
						}//Fin While dir		
					}//Fin While archivos
					$data = implode("",$ZipData);  
					$cdir = implode("",$Dircont);  	
					$data=$data.$cdir."\x50\x4b\x05\x06\x00\x00\x00\x00".pack("v",sizeof($Dircont)).pack("v",sizeof($Dircont)).pack("V",strlen($cdir)).pack("V",strlen($data))."\x00\x00";
					$namezip;
				    if($namezip)//Construir el archivo
				    {	if(($fp=fopen($namezip,"wb")))
				   		{	fwrite($fp,$data);
							fclose ($fp);
							return true;
						}else return false;
				    }else return $data;	   
	
	}//comprimimos

	/**
	 * Open zipfile for read
	 *
	 * @param String $file
	 * @throws FileNotFoundException
	 */
	
	
	/**
	 * Extract ZipFile Content into $path
	 *
	 * @param String $path
	 */
	public function Descomp($path, $file){
		Parameter::check(array('string' => array($path, $file)), __METHOD__);
		
		if(!file_exists($file))	{
			throw new FileNotFoundException("the zipfile not found, fucking pech");
		
		}//too ok
		else $zip=zip_open($file);
		
			
		 while ($zip_entry = zip_read($zip)) {
	       $nombre=zip_entry_name($zip_entry); //nombre del archivo o carpeta
	       $sizeori=zip_entry_filesize($zip_entry); //tamaño original del archivo
	       $sizeoff= zip_entry_compressedsize($zip_entry); //tamaño del archivo comprimido
	       $methodzip=zip_entry_compressionmethod($zip_entry); //el metodo de compresion
		 
	       if (zip_entry_open($zip, $zip_entry, "r")) {
	       		switch ($methodzip){
	       			case 'stored':
	       				if(!is_dir($path."/".$nombre)) 
	       					mkdir($path."/".$nombre);
	       			break;
	       		
	       			case 'deflated':
	       					$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
	       					if(file_exists($path."/".$nombre)) unlink($path."/".$nombre);
	       					$file= fopen($path."/".$nombre,"ab+");
	       					fwrite($file, $buf);
	       					fclose($file);
	        			break;
	       		}//fin del switch que captura el metodo de compresion
	       		
	       		
	           
	
	           zip_entry_close($zip_entry);
	       }////si todo ok, le damos para adelante nomas
	     
	
	   }//fin del while
	
	}//fin del metodo ReadZip
	
	
	
	
}//fin de la clase Compression



?>