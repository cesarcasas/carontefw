<?php

/**
 * Google Services Class
 */

class Google {
   
   private $host_language = 'es';
   
   private $api_key;
   
   final public function __construct($api_key = '') {
      if (empty($api_key)) {
         if (!defined('GOOGLE_API_KEY')) {
            $api_key = GOOGLE_API_KEY;
         }
      }
      
      $this->api_key = $api_key;
   }
   
   /**
    * Executes a curl request
    *
    * @param string $url
    * @return string
    */
   final private function fetch($url) {
      
      $curl = curl_init();
      
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_REFERER, "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}");
      
      $result = curl_exec($curl);
      
      curl_close($curl);
      
      return $result;      
   }
   
   /**
    * Translates a string from one language to another.
    *
    * @param string $string
    * @param string $lang as 'xx-xx'
    * @return string
    */
   final public function translate($string, $lang) {
      $string = rawurlencode($string);
      $lang = rawurlencode($lang);
      
      $url = "http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q={$string}&langpair={$lang}&key={$this->api_key}";
      
      $result = json_decode($this->fetch($url), true);
      
      if ($result['responseStatus'] != 200) {
         return false;
      }
      
      return $result['responseData']['translatedText'];
   }
   
   /**
    * Searchs a string in Google Search
    *
    * @param string $type can be: web, local, video, blogs, news, books, images, patent
    * @param string $query the query string
    * @param array $params lang('en', 'es', etc), size('small', 'large') and starting offset
    * @return mixed
    */
   final public function search($type, $query, $params = array()) {
      $query = urlencode($query);
      $type = strtolower($type);
      $lang = (empty($params['lang']) ? 'en' : strtolower($params['lang']));
      $size = (empty($params['size']) ? 'small' : strtolower($params['size']));            
      $start = (empty($params['start']) ? 0 : (int)($params['start']));            
      
      switch ($type) {
         case 'web':
         case 'local':
         case 'video':
         case 'blogs':
         case 'news':
         case 'books':
         case 'images':
         case 'patent':
            break;
         default:
            $type = 'web';
            break;
      }
      
      switch ($size) {
         case 'small':
         case 'large':
            break;
         default:
            $type = 'large';
            break;
      }
      
      $url = "http://ajax.googleapis.com/ajax/services/search/{$type}?key={$this->api_key}&q={$query}&v=1.0&hl={$lang}&rsz={$size}&start={$start}";
   
      $result = json_decode($this->fetch($url), true);
      
      if ($result['responseStatus'] != 200) {
         return false;
      }
      
      return $result['responseData'];
   }
   
   final private function chart_data($data = array()) {
      
      $multi = false;
      
      $result = array();
      
      foreach ($data as $row) {
         if (is_array($row)) {
            $result[] = implode(',', $row);
            $multi = true;
         }
         else {
            $result[] = $row;
         }
      }
      
      return 't:'.implode(($multi ? '|' : ','), $result);
      
   }
   
   final public function chart($type, $size, $data, $parameters = array()) {
      
      // get method vars
      $loc = 'http://chart.apis.google.com/chart?';
      $cht = '';
      $chs = '';
      $chd = '';
      
      $type = explode(':', $type, 2);
      if (count($type) != 2) {
         return false;
      }
      
      $chart = $type[0];
      $type = $type[1];
      
      $type_tran = array(
         'line' => array(
            'flat' => 'lc',
            'spark' => 'ls',
            'pair' => 'lxy'
         ),
         'bar' => array(
            'flat' => 'bhs',
            'stack-horiz' => 'bhs',
            'stack-vert' => 'bvs',
            'group-horiz' => 'bhg',
            'group-vert' => 'bvg'
         ),
         'pie' => array(
            'flat' => 'p',
            '3d' => 'p3',
            'concentric' => 'pc'
         ),
         'venn' => array(
            'flat' => 'v'
         ),
         'scatter' => array(
            'flat' => 's'
         ),
         'radar' => array(
            'flat' => 'r',
            'solid' => 'rs'
         ),
         'map' => array(
            'flat' => 't',
         ),
         'meter' => array(
            'flat' => 'gom'
         ),
         'qr' => array(
            'flat' => 'qr'
         )
      );
      
      if (!array_key_exists($chart, $type_tran)) {
         return false;
      }
      else {
         if (!array_key_exists($type, $type_tran[$chart])) {
            return false;
         }
         else {
            $cht = $type_tran[$chart][$type];
         }
      }
      
      $preg = '/^[0-9]+x[0-9]+$/';
      
      if (!filter_var($size, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $preg)))) {
         return false;
      }
      
      $chs = $size;
      
      $chd = $this->chart_data($data);
      
      $url = $loc."cht={$cht}&chs={$chs}&chd={$chd}";
      
      return $url;
      
   }
   
}