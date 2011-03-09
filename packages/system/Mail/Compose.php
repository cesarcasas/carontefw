<?php 
import("system.Mail.Mail");
class BuildMail extends Main{
	
	protected $from;
	protected $fromname;
	protected $to;
	protected $headers="";
	protected $paramF;
	protected $template;
	protected $vars;
	protected $subject;
	protected $body="";
	protected $spooler=false;
	protected $html=false;
	protected $newsLetter_id=-1;
	protected $files=array();
	protected $randomhash="";
	
	
	public function __construct(){
		$this->from="";
		$this->fromname="";
		$this->to="";
		
		$this->subject="";
		$this->paramF="";
		$this->template="";
		$this->vars=array();
		$this->body="";
		$this->newsLetter_id=-1;
		$this->files=array();
		$this->randomhash = md5(microtime());
	}
	
	public function setTemplate($filePath=''){
				
		if(!file_exists($filePath)) {
			
			return false;
		}
		else {
			$this->template=$filePath;
			return true;
		}
	}
	
	public function setHeader(){
	   if (empty($this->files)) {
   		if($this->html){
   			$this->headers= "MIME-Version: 1.0\r\n";
   			$this->headers .= "Content-type: text/html; charset=utf-8\r\n";
   			$this->headers .= "From: $this->fromname <".$this->from.">\r\n";
   			$this->headers .= "Reply-To: ".$this->from."\r\n";
   			$this->headers .= "Return-path: ".$this->from."\r\n";
   	}
   	else {
   		$this->headers .= "From:  $this->fromname <".$this->from.">\r\n";
   		$this->headers .= "Reply-To: ".$this->from."\r\n";
   		$this->headers .= "Return-path: ".$this->from."\r\n";
   	}
	} else {
			$this->headers= "MIME-Version: 1.0\r\n";
			$this->headers .= "Content-type: multipart/mixed; boundary=\"CARONTE-mixed-{$this->randomhash}\"\r\n";
			$this->headers .= "From: $this->fromname <".$this->from.">\r\n";
			$this->headers .= "Reply-To: ".$this->from."\r\n";
			$this->headers .= "Return-path: ".$this->from."\r\n";
	   
	}
	
	}
	
	
	public function setSubject($subject=''){
		$this->subject=$subject;
	}
	public function setFromName($subject=''){
		$this->fromname=$subject;
	}
	
	
	public function setTo($to=''){
		$this->to=$to;
	}
	
	public function setHtml($to=''){
		$this->html=$to;
	}
	
	public function setSpooler($to=''){
		$this->spooler=$to;
	}
	public function setFrom($to=''){
		$this->from=$to;
	}
	
	public function setVars($vars=array()){
		$this->vars=$vars;
	}
	
	public function setNewsletter($news_id=-1){
		$this->newsLetter_id=$news_id;
	}
	
	private function prepareAttachFolder($folder) {
	   if (!is_dir($folder)) {
	      mkdir($folder, 0777, true);
	      if (!@chmod($folder, 0777)) {
	         return false;
	      }
	   }
	   
	   return $folder;
	}
	
	public function addAttach($file) {
	   
	   if (!is_file($file) || !is_readable($file)) {
	      return false;
	   }
	   
	   $folder = PATH_APPLICATION.'/files/mail_attach/';
	   $newfile = basename($file);
	   $this->prepareAttachFolder($folder);
	   
	   if (is_file($folder.$newfile)) {
         $this->files[] = $newfile;
	      return true;
	   }
	   
	   if (@copy($file, $folder.$newfile)) {
	     $this->files[] = $newfile;
	     return true;
	   }
	   
	   return false;
	}
	
	public function setAttach($file) {
	   
	   $file = PATH_APPLICATION.$file;
	   
	   if (!is_file($file) || !is_readable($file)) {
	      return false;
	   }
	   	   
      $this->files[] = $file;
      return true;
	}

	public function doAttach($body, $nofolder = false) {
	   
	   if (empty($this->files)) {
	      return $body;
	   }
	   
	   $folder = $nofolder ? '' : PATH_APPLICATION.'/files/mail_attach/';

	   $body_type = $this->html ? 'text/html' : 'text/plain';
	   $randomhash = $this->randomhash;
	   $newbody = "--CARONTE-mixed-{$randomhash}\r\n";
	   $newbody .= "Content-Type: {$body_type}; charset=utf-8\r\n";
	   $newbody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	   $newbody .= $body."\r\n\r\n";
      $randomhash = $this->randomhash;
	   
	   foreach ($this->files as $file) {
	      
	   
	      if (!is_readable($folder.$file)) {
	         continue;
	      }	      
	      
	      $attachment = chunk_split(base64_encode(file_get_contents($folder.$file)));
	      $basefile = basename($file);
	      
	      //$content_type = 'application/octet-stream';
	      $content_type = 'application/octet-stream';
	      	      
	      $newbody .= "--CARONTE-mixed-{$randomhash}\r\n";
	      $newbody .= "Content-Type: {$content_type}; name=\"{$basefile}\"\r\n";
	      $newbody .= "Content-Transfer-Encoding: base64\r\n";
	      $newbody .= "Content-Disposition: attachment\r\n\r\n";
	      $newbody .= $attachment."\n";
	         
	   }
	   
	   $newbody .= "--CARONTE-mixed-{$randomhash}--\r\n\r\n";
	   
	   return $newbody;
	}
	
	public function send($body=""){
		self::setHeader();
		$body=$body=="" ? file_get_contents($this->template) : $body;
				
		$keys=array_keys($this->vars);
		foreach ($keys as $key){
			$body=str_replace("{".$key."}", $this->vars[$key], $body);
		}
		
		
		if($this->spooler){
		   $attach = FilesNewsletter::get($this->newsLetter_id);
		   $hash = $this->randomhash;
		   
			//Mail::addToSpooler($this->from,$this->fromname,$this->to,$this->headers,addslashes($this->body),$this->subject,$this->html?1:0);
			Mail::addToSpooler($this->from,$this->fromname,$this->to,$this->headers,$body,$this->subject,$this->html?1:0,$this->newsLetter_id, $attach, $hash);
		}else{

		   $this->body=$this->doAttach($body);

			mail($this->to,$this->subject,$this->body,$this->headers,$this->to);
			
			//echo $this->body;
		}
		//mail("ddndevmon@gmail.com",$this->subject,$this->body,$this->headers);
		/*mail("info@bsdsolutions.com.ar",$this->subject,$this->body,$this->headers);*/
	}
	
	public function sendSpooler($body,$aunto,$de,$deName,$para,$header,$attach, $hash){
	  
	   $attach = unserialize($attach);
	   
	   if ((is_array($attach)) && (count($attach) > 0)) {
	      $this->randomhash = $hash;
	      $this->files = array();
	      
	      foreach ($attach as $file) {
	         $this->setAttach($file['file_url']);
	      }
	      
	      $this->setHtml(true);
	      
	      $body = $this->doAttach($body, true);

   	   $header_data = explode("\n", $header);
   	   $newheader = array();
   	   foreach ($header_data as $mini) {
   	      
   	      if (stripos($mini, 'Content-Type:') !== false) {
   	         $mini = "Content-type: multipart/mixed; boundary=\"CARONTE-mixed-{$this->randomhash}\"\r";
   	      }
   	      
   	      $newheader[] = $mini;
   	   }
   	   $header = implode("\n", $newheader);	      
	   }
	   /*   
	   var_dump($header);
	   var_dump($body);
	   
	   die();
		*/		
		mail($para,$aunto,$body,$header,$para);
		//die();
		//mail("ddndevmon@gmail.com",$this->subject,$this->body,$this->headers);
		/*mail("info@bsdsolutions.com.ar",$this->subject,$this->body,$this->headers);*/
	}
	
	public function getBody(){
		//self::setHeader();
		$body=file_get_contents($this->template);
		$keys=array_keys($this->vars);
		foreach ($keys as $key){
			$body=str_replace("{".$key."}", $this->vars[$key], $body);
		}
		$this->body=$body;

		
		return $body;
	}
	
	
}
?>