<?php
/**
 * ActiveRecord
 *
 */
final class DOActiveRecord {
   
   /**
    * Clase paterna
    *
    * @var DataObject
    */
   private $parent;
   
   /**
    * Puntero del registro activo
    *
    * @var mixed
    */
   private $pointer;
   
   /**
    * Array con datos del registro activo
    *
    * @var array
    */
   private $data;
   
   /**
    * Modo: insertar o modificar
    *
    * @var string
    */
   private $mode;
   
   /**
    * Valor del puntero del registro activo
    *
    * @var mixed
    */
   private $keyvalue;

   /**
    * Referencia al DOTable
    *
    * @var DOTable
    */
   private $table;
   
   /**
    * Constructor.
    *
    * @param DataObject $parent
    */
   function __construct($parent) {
      $this->parent = $parent;
      
      $this->pointer = 0;
      
      $this->data = array();
      
      $this->table = $this->parent->mainTable;
      
      $this->clean();
      
      $keyvalue="";
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
   
   /**
    * Limpia la instancia
    *
    */
   final public function clean() {
      $this->data = array();
    	foreach ($this->table->getFieldset() as $field => $dofield) {
        $this->data[$dofield->descriptor] = null;
      }
      
   }
   
   /**
    * Seteo de variable
    *
    * @param mixed $var
    * @param mixed $value
    */
   final public function __set($var, $value) {
      
      if (array_key_exists($var, $this->table->getFieldset())) {
         $this->data[$var] = $value;
      } else {
         throw new DOException('Field doesn\'t exists: '.$var);
      }
      
   }
   
   /**
    * Establece una expresión al insertar/actualizar el registro activo
    *
    * @param unknown_type $expr
    * @param unknown_type $value
    */
   final public function set($expr, $value) {
      $this->data['='.$expr] = $value;
   }
   
   ##### It's magic time!
   
   /**
    * Establece el modo de inserción de registro
    *
    */
   function insert() {
      $this->mode = 'insert';
      $this->clean();
   }
   
   /**
    * Establece el modo de edición de registro
    *
    * @param mixed $id
    * @param bool $force
    * @return mixed
    */
   function edit($id, $force = false) {
      $this->mode = 'edit';
      $this->clean();
      
      $get = $this->parent->ActiveSearch->get($id);
      
      if (!empty($get[0]) || $force) {
         $this->keyvalue = $id;
         return true;
      }
      else {
         return false;
      }
   }
   
   /**
    * Aplica la actualización o inserción del registro activo
    *
    * @return mixed
    */
   function save() {
      $data = $this->data;
            
      $query = "";
      
      $fields = array();
      
      $edit = ($this->mode == 'edit');
	   
      if (!empty($data)) {
      $query .= ($edit ? 'UPDATE ' : 'INSERT INTO ').$this->table->getName();
	   
      $query .= ' SET ';
      foreach ($data as $field => $value) {

         if ($value === null) {
            continue;
         }

         if ((!$edit) || ($field != $this->table->getKey())) {
            $format = $this->table->prepareField($field, $value);
            
            if (empty($format)) continue;
            
            $fields[] = $format['name']."=".$format['value'];  
         }
      }
	   
	   $query .= implode(",", $fields);
	   
	   $query .= ($edit ? " WHERE ".$this->parent->toUnderscore($this->table->getKey())."=".$this->keyvalue : '');
	   //echo($query);
	   $this->parent->execute($query);
	   
	   //var_dump($data);
	   
	   if (!$edit) {
	      return $this->parent->dbo->getLastInsertId();
	   }
	   else {
	      return $this->keyvalue;
	   }
	      
	   }
	   else {
	   	//nada para guardar   
	   	return false;	
	   }
   }
   
   /**
    * Elimina el registro activo (sólamente si se está en modo inserción)
    *
    */
   function delete()
   {
   	if($this->mode=="edit"&&$this->keyvalue!="") {
         $cond=$this->table->prepareField($this->table->getKey(), $this->keyvalue);
         
         $query="DELETE FROM ".$this->table->getName()." WHERE ".$cond['name']."=".$cond['value'];

         $this->parent->execute($query);
         $this->clean();
   	}
   	else {
   		throw new DOException('You must select a record in edit mode in order to delete().');
   	}
   }
   
   /**
    * Elimina un conjunto de registros en base a las condiciones que se pasan como parámetro
    *
    * @param array $parameters
    * @return mixed
    */
   function deletePack($parameters) {
      
      // Condiciones
      $where = '';
      if (!empty($parameters['conditions'])) {
         $conditions = array();
         
         foreach ($parameters['conditions'] as $field => $value) {
            $oper = '=';
            
            $lastspace = strrpos($field, ' ');
            $lastpart = trim(substr($field, $lastspace + 1));
            $operators = array('=', '>', '<', '>=', '<=', '<=>', 'is_not_null', 'is_not', 'is_null', 'is', 'like', '!=', '<>', 'not_like', 'between', 'not_between');
            
            if (in_array(strtolower($lastpart), $operators)) {
               $oper = strtoupper(str_replace('_', ' ', $lastpart));
               $name = trim(substr($field, 0, $lastspace));
            } else {
               $name = $field;
            }
            
            $prop = $this->table->prepareField($name, $value);
            if (!$prop) {
               throw new DOException('Cannot prepare a CONDITIONS field for deletePack(): '.$name);            
            }
            
            $conditions[] = "{$prop['name']} {$oper} {$prop['value']}";           
         }
         
         $where = implode(' AND ', $conditions);
         $where = 'WHERE '.$where;
         
         if (empty($conditions)) {
            $where = '';
         }

         $limit=isset($parameters['limit'])!=""?"LIMIT ".$parameters['limit']:"" ;      
      }

      if (empty($where)) {
         return false;
      }
      
      $sql = "DELETE FROM {$this->table->getName()} {$where} {$limit}";
      
      return $this->parent->execute($sql); 
   }

     
}