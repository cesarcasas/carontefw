<?php

import(
   "system.db.DOField",
   "system.db.DOTable",
   "system.db.DOActiveSearch",
   "system.db.DOActiveRecord",
   "system.db.DOException"
);

/**
 * Superclase: contenedora de DOTable, DOActiveRecord, DOActiveSearch
 * @uses system.db.DOField
 * @uses system.db.DOTable
 * @uses system.db.DOActiveSearch
 * @uses system.db.DOActiveRecord
 * @uses system.db.DOException
 *
 */
final class DataObject {
   
   /**
    * Tabla de base de datos activa.
    *
    * @var DOTable
    */
   public $mainTable;
   
   /**
    * Objeto de abstracción de base de datos.
    *
    * @var mixed
    */
   public $dbo;
   
   /**
    * Campo clave del objeto de datos.
    *
    * @var string
    */
   private $key;
   
   /**
    * ActiveRecord asociado
    *
    * @var DOActiveRecord
    */
   public $ActiveRecord;
   
   /**
    * ActiveSearch asociado
    *
    * @var DOActiveSearch
    */
   public $ActiveSearch;
   
   /**
    * Debug
    *
    * @var bool
    */
   public $debug = false;
   
   /**
    * Debug SQL Queries
    *
    * @var array
    */
   private $debugQueries = array();
   
   /**
    * Genera un error fatal.
    *
    * @param string $message
    */
   final public function error($message) {
      die($message);
   }
   
   /**
    * Hace un print_r de la instancia.
    *
    */
   final public function debugDump() {
      $debug = print_r($this, true);
      echo "<pre>";
      echo str_replace("\n *RECURSION*\n"," (Recursive - Parent Reference)\n", $debug);
      echo "</pre>";      
   }
   
   final public function addSQLDebug($query, $info) {
      $this->debugQueries[] = array($query, $info);
   }
   
   static public function upperCallback($c) {
      return strtoupper($c[1]);
   }   
   
   static public function underCallback($c) {
      return '_'.strtolower($c[0]);
   }
   
   /**
    * Convierte una cadena_de_texto a CadenaDeTexto
    *
    * @param string $string
    * @return string
    */
   final public function toCamelCase($string) {
      $string = strtolower($string);
      $string[0] = strtoupper($string[0]);      
      $string = preg_replace_callback('/_([a-z])/', array(__CLASS__, 'upperCallback'), $string);
      return $string;
   }
   
   /**
    * Convierte una CadenaDeTexto a cadena_de_texto
    *
    * @param unknown_type $string
    * @return unknown
    */
   final public function toUnderscore($string) {
      $string[0] = strtolower($string[0]);
      $string = preg_replace_callback('/([A-Z])/', array(__CLASS__, 'underCallback'), $string);
      
      return $string;
   }
   
   /**
    * Escapa los caracteres especiales utilizados por MySQL
    *
    * @param string $string
    * @return string
    */
   final public function sanitize($string) {
      return $this->mainTable->sanitize($string);
   }
   
   final public function enquote($string) {
      return $this->mainTable->enquote($string);
   }
   
   /**
    * Convierte un array de CadenasDeTexto a cadenas_de_texto
    *
    * @param array $data
    * @return array
    */
   final public function arrayToUnderscore(array $data) {
      $result = array();
      
      foreach ($data as $key => $value) {
         if (is_array($value)) {
            $value = $this->arrayToUnderscore($value);
         }
         
         if (!is_int($key)) $key = $this->toUnderscore($key);
         $result[$key] = $value;
      }
      
      return $result;
   }
   
   /**
    * Convierte una array de cadenas_de_texto a CadenasDeTexto
    *
    * @param array $data
    * @return array
    */
   final public function arrayToCamelCase(array $data) {
      $result = array();
      
      foreach ($data as $key => $value) {
         if (is_array($value)) {
            $value = $this->arrayToCamelCase($value);
         }
         
         if (!is_int($key)) $key = $this->toCamelCase($key);
         $result[$key] = $value;
      }
      
      return $result;
   }

    /**
    * Ejecuta una consulta en el DBO activo
    *
    * @param string $sql
    * @return MySQLResultSet
    */
   final public function query($sql) {
      $microtime = microtime(1);
      
      $return = $this->dbo->executeQuery($sql);
      //print_r($this->dbo);
      $microtime = round((microtime(1) - $microtime) * 1000, 2);
      
      if ($this->debug) {
         $rows = $return->numRows();
         $this->addSQLDebug($sql, "QUERY ({$rows} rows; {$microtime} ms.)");
         
      }
      
      return $return;
   }
    final public function pagedQuery($sql,$page_size=10,$page=1) {
      $microtime = microtime(1);
      
      $return = $this->dbo->executePagedQuery($sql, $page_size,'p', false, '', -1, $page);
     
      $microtime = round((microtime(1) - $microtime) * 1000, 2);
      
      if ($this->debug) {
         $rows = $return->numRows();
         $this->addSQLDebug($sql, "QUERY ({$rows} rows; {$microtime} ms.)");
         
      }
      
      return $return;
   }
   
   /**
    * Ejecuta un comando en el DBO activo
    *
    * @param string $sql
    * @return mixed
    */
   final public function execute($sql) {
      $return = $this->dbo->executeUpdate($sql);

      if ($this->debug) {
         $this->addSQLDebug($sql, 'EXECUTE');
      }

      return $return;
   }
   
   /**
    * Constructor: Genera instancias de las clases DOTable, DOActiveRecord y DOActiveSearch.
    *
    * @param string $table
    * @param string $primary_key
    * @param MySQLConnection $dbo
    * @throws DOException
    */
   final public function __construct($table, $primary_key, $dbo, $debug = false) {
      $this->debug = $debug;
      
      // Instanciamos nuestro DBO
      $this->dbo = $dbo;
      
      $this->key = $this->toCamelCase($primary_key);
      
      // Instanciamos la tabla principal
      $this->mainTable = new DOTable($this, $table, $primary_key);
            
      // ActiveRecord y ActiveSearch
      $this->ActiveRecord = new DOActiveRecord($this);
      $this->ActiveSearch = new DOActiveSearch($this);
      
      
   } // __construct
   
   /**
    * Destructor: Si esta habilitado el DEBUG del objeto, realiza un dump al final del request.
    *
    */
   final public function __destruct() {
      if ($this->debug) {
         echo '<br /><hr /><ol>';
         foreach ($this->debugQueries as $query) {
            echo '<li>'.htmlentities($query[1].' >> '.$query[0]).'</li>';
         }
         echo '</ol>';
      }
   }
   
}


