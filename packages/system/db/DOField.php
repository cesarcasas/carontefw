<?php
final class DOField {
   
   /**
    * Clase DOTable que contiene la clase actual.
    *
    * @var DOTable
    */
   public $parent;
   
   /**
    * Descriptor del campo actual.
    *
    * @var string
    */
   public $descriptor = '';
   
   /**
    * Nombre del campo actual.
    *
    * @var string
    */
   public $name = '';
   
   /**
    * Tipo de datos del campo actual.
    * Puede ser: numeric, text, datetime
    *
    * @var string
    */
   public $type = '';
   
   /**
    * Tamaño de datos del campo actual.
    * Puede ser: false, o un entero.
    *
    * @var mixed
    */
   public $length = false;
   
   /**
    * ¿El campo actual es un campo clave?
    * 
    * @var bool
    */
   public $key = false;
   
   /**
    * ¿El campo actual permite valores nulos?
    *
    * @var bool
    */
   public $null = false;
   
   /**
    * ¿El campo actual permite sólo numeros positivos y 0?
    *
    * @var false
    */
   public $unsigned = false;
   
   /**
    * Convierte un tipo de datos MySQL a un "tipo de datos" que podamos entender.
    *
    * @param string $type
    * @return string
    */
   final public function getType($type) {
      $return = 'unknown';
      $type = trim(strtolower($type));
      
      $string = array('char', 'varchar', 'binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob',
         'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set');
      
      $numeric = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'integer', 'decimal', 'numeric',
         'float', 'real', 'double', 'bit');
         
      $datetime = array('datetime', 'date', 'timestamp', 'time', 'year');
      
      if (in_array($type, $string)) {
         $return = 'string';
      }
      elseif (in_array($type, $numeric)) {
         $return = 'numeric';
      }
      elseif (in_array($type, $datetime)) {
         $return = 'datetime';
      }
      else {
         $return = 'unknown';
      }
      
      return $return;
   }
   
   /**
    * Constructor: Estructura de campos de tabla.
    *
    * @param array $column
    */
   final public function __construct($parent, $column) {
      $this->parent = $parent;
      
      $this->descriptor = $this->parent->parent->toCamelCase($column['Field']);
      $this->name = $column['Field'];
      if (strpos($column['Type'], '(')) {
         $bp = strpos($column['Type'], '(');
         $ep = strrpos($column['Type'], ')');
         $this->type = substr($column['Type'], 0, $bp);
         $this->length = substr($column['Type'], $bp + 1, $ep - $bp - 1);
      }
      else {
         $this->type = $column['Type'];
         $this->length = false;
      }
      $this->key = ($column['Key'] ? true : false);
      $this->null = ($column['Null'] ? true : false);
      $this->unsigned = (strpos($column['Type'], 'unsigned') ? true : false);
   }
   
}