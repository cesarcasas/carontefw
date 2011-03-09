<?php

if (!defined('CRLF')) define('CRLF', "\r\n");

class ExcelPro {
	
	private $data;
	
	private $name;
	
	private $sheet;
	
	private $openSheet;
	
	private $openRow;
	
	private $file;
	
	private $direct;
	
	public function stream($file = false) {
	   if (empty($file)) {
	      $file = time().rand(1000,9999).'.xls';
	   }
		
	   $folder = dirname($file);
		if (!empty($folder)) {
   		@mkdir(dirname($file), 0777, true);
         @chmod(dirname($file), 0777);
		}

		@chmod($file, 0777);		
		file_put_contents($file, '');
		
	   $this->direct = true;
	   $this->file = $file;

	   return $file;
	}
	
	public function data($string) {
	   if (empty($this->direct)) {
		   $this->data .= $string.CRLF;
	   }
	   else {
	      file_put_contents($this->file, $string.CRLF, FILE_APPEND);
	   }
	}
	
	public function __construct($filename = 'spreadsheet', $styles = array(), $direct_save = false) {
		
		if (empty($filename)) $filename = 'spreadsheet';

		if ($direct_save) {
	      $this->stream($filename);
	   }
	   
		$this->data('<'.'?xml version="1.0" encoding="UTF-8"?'.'>');
		$this->data('<'.'?mso-application progid="Excel.Sheet"?'.'>');
		
		$this->data('<Workbook xmlns="urn:schemas-microsoft-com:office:Spreadsheet"
   xmlns:o="urn:schemas-microsoft-com:office:office"
   xmlns:x="urn:schemas-microsoft-com:office:excel"
   xmlns:ss="urn:schemas-microsoft-com:office:Spreadsheet"
   xmlns:html="http://www.w3.org/TR/REC-html40">');
		
		$this->data('<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"> 
   <Author>Website</Author> 
   <LastAuthor>Website</LastAuthor> 
   <Created>'.date('c').'</Created> 
   <Company>Microsoft Corporation</Company> 
   <Version>11.6568</Version> 
   </DocumentProperties>');
		
		if (!empty($styles)) {
			$this->addStyles($styles);
		}
		
		$this->sheet = 0;
		$this->name = $filename;
		$this->openSheet = false;
		$this->openRow = false;
	}
	
	private function addStyles($styles = array()) {
		if (empty($styles)) return false;
		
		$this->data('<Styles>');
		foreach ($styles as $id => $style) {
			if (empty($style)) continue;
			
			$this->data('<Style ss:ID="'.$id.'">');
			foreach ($style as $style_attribute => $style_element) {
				$style_propierties = array();
				foreach ($style_element as $stylet_name => $stylet_value) {
					$style_propierties[] = $stylet_name.'="'.$stylet_value.'"';
				}
				$this->data('<'.$style_attribute.' '.implode(' ', $style_propierties).' />');
			}
			$this->data('</Style>');
		}
		$this->data('</Styles>');
		
		return true;
	}
	
	public function addSheet($name = '') {
		
		if (empty($name)) $name = $this->name;
		
		if ($this->openSheet) {
			$this->closeSheet();
		}
		
		$this->data('<Worksheet ss:Name="'.$name.'"><Table>');
		
		$this->openSheet = true;
		$this->sheet++;
		
		return true;
	}
	
	public function closeSheet() {
		if (!$this->openSheet) return false;
		
		if ($this->openRow) {
			$this->closeRow();
		}
		
		$this->data('</Table></Worksheet>');
		$this->openSheet = false;
		
		return true;
	}
	
	public function setColumn($width = 60, $auto_width = false) {
		if (!$this->openSheet) return false;
		
		$this->data('<Column ss:AutoFitWidth="'.($auto_width ? '1' : '0').'" ss:Width="'.$width.'" />');
		
		return true;
	}
	
	public function closeRow() {
		if (!$this->openRow) return false;
		$this->data('</Row>');
		$this->openRow = false;
		return true;
	}
	
	public function addRow() {
		if (!$this->openSheet) return false;

		if ($this->openRow) {
			$this->closeRow();
		}
		
		$this->data('<Row>');
		$this->openRow = true;
		return true;	
	}
	
	public function addCell($value, $type = 'String', $style = '') {
		if (empty($type)) $type = 'String';
		$type = ucfirst($type);
		
		$value = htmlspecialchars($value);
		
		$style_attribute = '';
		if (!empty($style)) {
			$style_attribute = ' ss:StyleID="'.$style.'"';
		}
		
		$this->data('<Cell'.$style_attribute.'><Data ss:Type="'.$type.'">'.$value.'</Data></Cell>');
	}

	public function closeBook() {
		$this->closeSheet();
		$this->data('</Workbook>');		
	}
	
	public function debugDump() {
		$this->closeBook();
		
		echo '<pre>'.htmlspecialchars(print_r($this->data, true)).'</pre>';
	}

	function download() {
		$this->closeBook();
		
		$this->name = str_replace('/', '.', $this->name);
		
		$now = gmdate('D, d M Y H:i:s').' GMT';
		$USER_BROWSER_AGENT = $this->_get_browser_type();
		
		header('Content-Type: '.$this->_get_mime_type().';charset=utf-8');
		header('Expires: '.$now);
		
		if ($USER_BROWSER_AGENT == 'IE') {
			header('Content-Disposition: attachment; filename="' . $this->name . ".xls");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Content-Disposition: attachment; filename="' . $this->name . ".xls");
			header('Pragma: no-cache');
		}
		
		echo $this->data;
	}	
	
	function save($file = false) {
	   
	   if (empty($file)) {
	      $file = $this->name;
	   }
	   
		$this->closeBook();
		$folder = dirname($file);
		if (!empty($folder)) {
   		@mkdir(dirname($file), 0777, true);
         @chmod(dirname($file), 0777);
		}

		@chmod($file, 0777);		
		
		if (empty($this->direct)) {
		    file_put_contents($file, $this->data);
		}
		
		return $file;
	}		
	
	function _get_browser_type() {
		$USER_BROWSER_AGENT = "";
		
		if (@ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'OPERA';
		} else if (@ereg('MSIE ([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'IE';
		} else if (@ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'OMNIWEB';
		} else if (@ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'MOZILLA';
		} else if (@ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT = 'KONQUEROR';
		} else {
			$USER_BROWSER_AGENT = 'OTHER';
		}
		
		return $USER_BROWSER_AGENT;
	}
	
	function _get_mime_type()
	{
		$USER_BROWSER_AGENT = $this->_get_browser_type();
	
		$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
		? 'application/octetstream'
		: 'application/vnd.ms-excel';

		//: 'application/octet-stream';
		return $mime_type;
	}
	
}