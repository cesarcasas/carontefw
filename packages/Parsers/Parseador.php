<?php


class Parseador extends Main {
	
	/**
	 * Variable que contiene la data cruda
	 * @var string
	 */
	private $data;
	
	/**
	 * Variable que contiene la data ya parseada
	 * @var array
	 */

	private $parserdata = array();
	
	/**
	 * La regla para parseo
	 * @var string
	 */
	private $rule;
	
	/**
	 * Variable que contiene la data ya parseada
	 * @var boolean
	 */
	private $multipart;
	
	private $filters;
	private $rfilters;
	
	private $returnIndex = 1;
	
	public function __construct ($isFluent = false) {
		if ($isFluent) $this->multipart = true;
	}
	
	public function __toString() {
		return $this->getData();
	}
	
	static public function getInstance () {
		return new self();
	}
	
	private function getInstanceFluent ($data) {
		
		$obj = new self(true);
		$obj->setData($data);

		if (!is_null($this->rfilters)) foreach ($this->rfilters as $p => $r ) $obj->addRecursiveFilter($p,$r);	
		
		return $obj;
		
	}

	public function Clear() {
		$this->data = null;
		$this->parserdata = null;
		$this->rule = null;
		$this->multipart = false;
		$this->filters = array();
		$this->rfilters = array();
		$this->returnIndex = 1;
		
		return $this;
	}
	
	public function getData () {
		return $this->data;
	}
	
	public function setData ($data) {
		$this->data = $data;
		return $this;
	}
	
	public function setRule ($patern) {
		$this->rule = $patern;
		return $this;
	}
	
	public function addFilter ($expReg, $replace = "") {
		$this->filters[$expReg] = $replace;
		return $this;
	}
	
	public function addRecursiveFilter ($expReg, $replace = "") {
		$this->filters[$expReg] = $replace;
		$this->rfilters[$expReg] = $replace;
		return $this;
	}

	public function setDataByFile ($file) {
		if (file_exists($file)) {
			$this->data = file_get_contents($file);
			return $this;
		} else {
			throw new Exception("El archivo {$file} no existe");
		}
	}
	
	public function setDataByUrl ($url,$bypost = false) {
		
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, $bypost);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $this->data = curl_exec($ch);
        
        if (curl_errno($ch)) {
        	throw new Exception("Error con curl extrayendo url: $url, Curl dice: ". curl_error($ch));
        }
        
        curl_close($ch);
		
        return $this;
        
	}

	public function noCastResults () {
		$this->returnIndex = 0;
		return $this;
	}
	
	public function toUtf8() {
		$this->data = utf8_encode($this->data);
		return $this;
	}
	
	public function getMultisearch ($rule = null, $data = null) {
		
		$data = is_null($data)?
					$this->data:
					$data;
		
		$rule = is_null($rule)?
					$this->rule:
					$rule;
					
		if (!is_string($data)) throw new RuntimeException("Error al pasar parametro, \$data debe ser un array en busqueda multiple");
		
		if (preg_match_all($rule,$data,$res)) {
			
			$dud = array();
			$fc=count($res[0]);
			for ($i=0; $i < $fc; $i++) {
				
				$fo = count($res);
								
				for ($j=0; $j < $fo; $j++)
					$dud[$i][$j] = $res[$j][$i];
			}//for::$i
			return $dud;
			
		} else {
			return array();
		}//if
						
	}//method
	
	public function parsear($rule = null, $data = null) {
				
		$data = is_null($data)?
					$this->data:
					$data;
		
		$rule = is_null($rule)?
					$this->rule:
					$rule;
		
		
		if (is_array($data)) {
			foreach ($data as $dat) {
				$this->parsear($dat);
			}
			
			
		} else if (is_object($data)) {
			
			foreach ($data->getParsedData() as $dat) {
				$this->parserdata[] = $this->parsear($rule,$dat);
			}

		} else {
				
			$res = array();
			
			if ($this->filters) {
				foreach ($this->filters as $pat => $r) {
					$data = preg_replace($pat, $r, $data);
				}
			}
			
			if (preg_match_all($rule,$data,$res)) {
				//print_r ($res);
				foreach ($res[$this->returnIndex] as $re) {
					$this->parserdata[] = $this->getInstanceFluent($re);
				}
			} else {
				$this->parserdata = array();
			}
			
		}
		
		return $this->parserdata;
				
	}
	
	public function getUniqueSearch ($rule = null, $data = null) {
		
		$data = is_null($data)?
					$this->data:
					$data;
		
		$rule = is_null($rule)?
					$this->rule:
					$rule;
		
		
		if (!is_string($data)) {
			throw new RuntimeException("Si se espera un resultado unico, se debe enviar un string como data");
		} 

		
		if ($this->filters) {
			foreach ($this->filters as $pat => $r) {
				$data = preg_replace($pat, $r, $data);
			}
		}
		
		$res = array();
		
		/* Ser o no ser. esa es la cuestion... esto se tendria que validar .... */
		
		if (preg_match($rule,$data,$res)) {
			$this->parserdata = array($res[1]);
		} else {
			$this->parserdata = array(null);
		}
		
		return $this->parserdata[0];
				
	}
	
	/*
	public function getParseDataAsArray($elm = 0) {
		
		if (is_array($this->parsedata)) {
			
			return getParseDataAsArray ();
			
		}
		
	}*/
	
	public function getParsedData() {
		return $this->parserdata;
	}
	
}


//* $res = $obj->parsear("<li>(.*)</li>")->parsear("<span[\w\"\'\s\=]*id\s?\=\s?\"?nombre\"?[\w\"\'\s\=]>(.*)<\/span>");
// print_r($res->getParsedData());


?>