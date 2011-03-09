<?php
/**
 * Lógica de tablas; contiene un set de DOField.
 *
 */
final class DOTable {
   
   /**
    * Clase DataObject que contiene la instancia actual.
    *
    * @var DataObject
    */
   public $parent;
   
   /**
    * Nombre de la tabla actual.
    *
    * @var string
    */
   private $name;
   
   /**
    * Campo clave.
    *
    * @var string
    */
   private $key;
   
   /**
    * Colección de DOFields.
    *
    * @var DoField[string]
    */
   private $fieldset = array();
      
   /**
    * Constructor: Obtiene la estructura de la tabla.
    *
    * @param DataObject $parent
    * @param string $table
    * @throws DOException
    */
   final public function __construct($parent, $table, $primary_key) {

      # Especificamos nuestra instancia paterna.
      $this->parent = $parent;
      
      # Intentamos obtener la estructura de nuestra tabla
      $sqlStructure = "DESCRIBE {$table}";
      
      $this->name = $table;
      $structure = $this->parent->query($sqlStructure);
      
      while ($column = $structure->fetchAssoc()) {
         $this->fieldset[$this->parent->toCamelCase($column['Field'])] = new DOField($this, $column);
      }
      
      if (count($this->fieldset) == 0) {
         throw new DOException('Missing table: '.$table);
      }
      
      $this->key = $this->parent->toCamelCase($primary_key);
          
      if (!array_key_exists($this->key, $this->fieldset)) {
         throw new DOException('Missing primary key: '.$primary_key);
      }
      
   }
   
   /**
    * Devuelve el fieldset
    *
    * @return unknown
    */
   final public function getFieldset() {
      return $this->fieldset;
   }
   
   /**
    * Devuelve el campo clave
    *
    * @return string
    */
   final public function getKey() {
      return $this->key;
   }
   
   /**
    * Devuelve el nombre de la tabla
    *
    * @return string
    */
   final public function getName() {
      return $this->name;
   }
   
   /**
    * Ejecutamos escapeString del DBO
    *
    * @param string $value
    * @return string
    */
   final public function sanitize($value) {
      return $this->parent->dbo->escapeString($value);
   }
   
   /**
    * Devuelve el valo que pasamos como parametro con comillas anexadas
    *
    * @param unknown_type $value
    * @return unknown
    */
   final public function enquote($value) {
      return '\''.$value.'\'';
   }
   
   /**
    * Prepara un campo para su posterior ejecución de QUERY
    *
    * @param string $field
    * @param mixed $value
    * @return array
    */
   final public function prepareField($field, $value = null) {
      $fieldexpr = false;
      $valuexpr = false;
      if (strpos($field, '=') === 0) {
         $fieldexpr = true;
         $field = substr($field, 1);
      }
      
      if (is_array($value)) {
         $valuexpr = true;
         $value = $value[0];
      }
      
      if (!$fieldexpr) {
         $fieldset = $this->parent->mainTable->getFieldset();
         
         if (!array_key_exists($field, $fieldset)) {
            return false;
         }
         
         $field = $fieldset[$field];
         $type = $field->getType($field->type);
         
         $result = array(
            'name' => $field->name,
            'type' => $type
         );
      }
      else {
         $type = 'expresion';
         
         $result = array(
            'name' => $field,
            'type' => $type
         );   
      }
      
      
      $result['value'] = $value;
      if ($value !== null) {
         if (!$valuexpr) {
            if (($type == 'string') || ($type == 'datetime')) {
               $value = $this->sanitize($value);
               $result['value'] = $this->enquote($value);
            } elseif ($type == 'numeric') {
               if (!is_numeric($value)) {
                  throw new DOException('Expected a numeric value for field: '.$name);
               }
            } else {
               if ((substr($value, 0, 2) == "'%") && (substr($value, -2) == "%'")) {
                  $value = $this->sanitize(substr($value, 2, -2));
                  $result['value'] = "'%{$value}%'";
               }
            }
         }
      }
            
      return $result;
   }
    
}