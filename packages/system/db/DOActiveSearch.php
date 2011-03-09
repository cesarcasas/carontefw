<?php
/**
 * ActiveSearch
 *
 */
final class DOActiveSearch {
   
   /**
    * DataObject
    *
    * @var DataObject
    */
   private $parent;
   
   /**
    * DOTable
    *
    * @var DOTable
    */
   private $table;
   
   /**
    * Array con las tablas adjuntas
    *
    * @var unknown_type
    */
   private $join = array();
   
   /**
    * Constructor
    *
    * @param DataObject $parent
    */
   public function __construct($parent) {
      $this->parent = $parent;
      $this->table = $this->parent->mainTable;
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
    * Manejador de llamada a métodos dinámicos
    *
    * @param string $name
    * @param array $args
    * @return mixed
    */
   final public function __call($name, array $args) {
      if (substr($name, 0, 3) == 'get') {
         $field = substr($name, 3);

         if (!array_key_exists($field, $this->table->getFieldset())) {
            throw new DOException('Field don\'t exists: '.$field);            
         }
         
         if (!isset($args[0])) {
            throw new DOException('Missing $value argument from getXY() method.');            
         }
         
         $value = $args[0];
         return $this->get($value, $field);
      }
      elseif (substr($name, 0, 4) == 'find') {
         // hacer if si no hay args
         $field = substr($name, 4);
         
         if (!array_key_exists($field, $this->table->getFieldset())) {
            throw new DOException('Field don\'t exists: '.$field);            
         }
         
         if (!isset($args[0])) {
            throw new DOException('Missing $value argument from findXY() method.');            
         }
         
         $value = $args[0];
         if (!empty($args[1])) {
            $find_arg = $args[1];
         }
         else {
            $find_arg = array();
         }
         $params = array_merge(array('conditions' => array($field => $value)), $find_arg);
         return $this->find('all', $params);
      }
   }
   
   /**
    * Agrega una tabla adjunta al realizar operaciones de búsqueda de datos
    *
    * @param string $type
    * @param string $table
    * @param string $field_a
    * @param string $field_b
    * @param string $operator
    */
   final public function setJoin($type, $table, $field_a, $field_b, $operator = '=') {
      
      $relname = '';
      $relalias = '';
      $alias = false;
      
      if (is_array($table)) {
         if (count($table) != 2) {
            throw new DOException('You forgot defining the joint table alias.');
         }
         
         $alias = true;
         $relname = $table[0];
         $relalias = $table[1];
      }
      else {
         $alias = false;
         $relname = $table;
         $relalias = $table;
      }
      
      switch ($type) {
         case 'left':
            $type = 'LEFT JOIN';
            break;
         case 'right':
            $type = 'RIGHT JOIN';
            break;
         case 'inner':
         default:
            $type = 'INNER JOIN';
            break;
      }
      
      $field_a_prefix = '';
      
      if (strpos($field_a, '.') === false) {
      	$field_a_prefix = "{$this->table->getName()}.";
      }
      
      $jointable = $alias ? "{$relname} AS {$relalias}" : "{$relname}";
      
      $this->join[] = "{$type} {$jointable} ON ({$field_a_prefix}{$field_a} {$operator} {$relalias}.{$field_b})";
   }
   
   /**
    * Devuelve un fragmento SQL para aplicar las tablas adjuntas
    *
    * @return string
    */
   final public function sqlJoin() {
      $result = '';
      
      $result = implode(' ',$this->join);
      
      return $result;
   }
   
   /**
    * Super Find
    *
    * @param string $type
    * @param array $parameters
    * @return mixed
    */
   final public function find($type, $parameters = array()) {
      
      $type = strtolower($type);
      if (!in_array($type, array('all', 'first', 'count', 'list', 'pager'))) {
         throw new DOException('Invalid find() type.');            
      }
      
      // el tipo 'pager' es especial, asi que lo procesamos acá nomás
      if ($type == 'pager') {
         $page = (!empty($parameters['page']) ? $parameters['page'] : 1);
         $page_size = (!empty($parameters['page_size']) ? $parameters['page_size'] : 20);

         $parameters['limit'] = $page_size;
         $parameters['start'] = (($page - 1) * $page_size);
         
         $result = $this->find('all', $parameters);
         
         $found_rows = $this->parent->query('SELECT FOUND_ROWS()')->fetchRow();
         $count = (isset($found_rows[0]) ? $found_rows[0] : -1);
         
         $total_pages = ceil((float)((float)$count / (float)$page_size));
         
         return array(
            'paginator' => array(
               'page' => $page,
               'page_size' => $page_size,
               'page_result' => count($result),
               'total_pages' => $total_pages,
               'total_rows' => $count
            ),
            'data' => $result
         );
      }

      $parameters['return'] = (!empty($parameters['return']) ? strtolower($parameters['return']) : 'array');

      // Ahora dependiendo de que estilo de find se trata, hacemos override de ciertas cosas :3
      switch ($type) {
         case 'first':
            $parameters['limit'] = 1;
            $parameters['groups'] = array();
            break;
         case 'count':
            $primary = $this->table->prepareField($this->table->getKey());
            $parameters['limit'] = 0;
            $parameters['start'] = 0;
            $parameters['groups'] = array();
            $parameters['fields'] = array('=COUNT(*)');
            $parameters['order'] = array();
            break;
         case 'list':
            $primary = $this->table->getKey();
            $listfield = (!empty($parameters['fields'][0]) ? $parameters['fields'][0] : $primary);
            $parameters['groups'] = array();
            $parameters['fields'] = array($primary, $listfield);
            break;
         default:
            break;
      }

      // Distinct
      $distinct = '';
      if (!empty($parameters['distinct'])) {
         $distinct = 'DISTINCT';
      }
      
      // Campos
      $select = '*';
      if (!empty($parameters['fields'])) {
         $fields = array();
         
         foreach ($parameters['fields'] as $field) {
            $prop = $this->table->prepareField($field);

            if (!$prop) {
               throw new DOException('Cannot prepare a FIELDS element for find(): '.$field);            
            }
            
            $fields[] = $prop['name'];
         }
         
         $select = trim(implode(', ', $fields));
         if (empty($select)) {
            $select = '*';
         }
      }
      
      // Condiciones
      $where = '';
      if (!empty($parameters['conditions'])) {
      	 if (is_array($parameters['conditions'])) {
	         $conditions = array();
	         
	         foreach ($parameters['conditions'] as $field => $value) {
	            $oper = '=';
	            
	            $field = trim($field);
	            $lastspace = strrpos($field, ' ');
	            $lastpart = trim(substr($field, $lastspace + 1));
	            $operators = array('=', '>', '<', '>=', '<=', '<=>', 'is_not_null', 'is_not', 'is_null', 'is', 'like', '!=', '<>', 'not_like', 'between', 'not_between', 'in', 'noop');
	            
	            if (in_array(strtolower($lastpart), $operators)) {
	               $oper = strtoupper(str_replace('_', ' ', $lastpart));
	               if ($oper == 'NOOP') {
	                  $oper = '';
	               }
	               $name = trim(substr($field, 0, $lastspace));
	            } else {
	               $name = $field;
	            }
	            
	            $prop = $this->table->prepareField($name, $value);
	            if (!$prop) {
	               throw new DOException('Cannot prepare a CONDITIONS field for find(): '.$name);            
	            }
	            
	            $conditions[] = "{$prop['name']} {$oper} {$prop['value']}";  
	                    
	         }
	         
	         $where = implode(' AND ', $conditions);
	         $where = 'WHERE '.$where;
	         
	         if (empty($conditions)) {
	            $where = '';
	         }
	      }
	      else {
	      	$where = 'WHERE '.$parameters['conditions'];
	      }
      }
      
      // Orden
      $order = '';
      if (!empty($parameters['order'])) {
         $orderby = array();
         
         foreach ($parameters['order'] as $field => $sort) {
            $sort = (strtolower($sort) == 'desc' ? 'DESC' : 'ASC');            
            
            $prop = $this->table->prepareField($field);
            
            if (!$prop) {
               throw new DOException('Cannot prepare a ORDER field for find(): '.$field);            
            }            

            $orderby[] = "{$prop['name']} {$sort}";
         }
         
         $order = implode(', ', $orderby);
         $order = 'ORDER BY '.$order;
         
         if (empty($orderby)) {
            $order = '';
         }
         
      }

      // Agrupamiento
      $group = '';
      if (!empty($parameters['groups'])) {
         $groupby = array();
         
         foreach ($parameters['groups'] as $field) {
            $prop = $this->table->prepareField($field);
            
            if (!$prop) {
               throw new DOException('Cannot prepare a GROUP field for find(): '.$field);            
            }
                        
            $groupby[] = $prop['name'];
         }
         
         $group = implode(', ', $groupby);
         $group = 'GROUP BY '.$group;
         
         if (empty($groupby)) {
            $group = '';
         }
         
      }      
      
      // Limite y offset
      $limit = '';
      if (!empty($parameters['limit'])) {
         if (empty($parameters['start'])) {
            $limit = 'LIMIT '.$parameters['limit'];
         }
         else {
            $limit = 'LIMIT '.$parameters['start'].', '.$parameters['limit'];
         }
      }
      
      $sql_calc = '';
      if (empty($parameters['no-count'])) {
         $sql_calc = 'SQL_CALC_FOUND_ROWS';
      }
      
      $sql = "SELECT {$sql_calc} {$distinct} {$select} FROM {$this->table->getName()} {$this->sqlJoin()} {$where} {$group} {$order} {$limit}";
      
      $return = false;

      switch ($parameters['return']) {
         case 'sql':
            $return = $sql;
            break;
         case 'object':
            $return = $this->parent->query($sql);
            break;
         case 'array':
         default:
         	
         	$query = $this->parent->query($sql);
         	
            $return = array();
            
            while ($row = $query->fetchAssoc()) {
               $return[] = $row;
            }
            
            if ($type == 'count') {
               $return = (!empty($return[0]['COUNT(*)']) ? $return[0]['COUNT(*)'] : false);
            }
            
            break;
      }
      
      return $return;
   }
   
   /**
    * Obtiene un único registro en base a la $id provista
    * Si se especifica un campo alternativo, nos devuelve el primer registro
    * en el cual éste campo sea igual a éste valor
    *
    * @param mixed $id
    * @param string $alternate_field
    * @return array
    */
   final public function get($id, $alternate_field = null) {
      
      if (!isset($id) || ($id == null)) {
         $id = array('NULL'); 
      }
      
      if (empty($alternate_field)) {
         $alternate_field = $this->table->getKey();
      }
      
      $params = array(
         'no-count' => 1,
         'conditions' => array(
            $alternate_field => $id
         ),
         'result' => 'array'
      );
      
      return $this->find('first', $params);
   }
   
   /**
    * Devuelve la existencia de un registro en base a la $id provista
    * Si se especifica un campo alternativo, nos devuelve si existe algun registro
    * en el cual éste campo sea igual a éste valor
    *
    * @param mixed $id
    * @param string $alternate_field
    * @return bool
    */
   final public function exists($id, $alternate_field = null) {
      
      if (!isset($id) || ($id == null)) {
         $id = array('NULL'); 
      }
      
      if (empty($alternate_field)) {
         $alternate_field = $this->table->getKey();
      }
      
      $params = array(
         'no-count' => 1,
         'fields' => array('=1'),
         'conditions' => array(
            $alternate_field => $id
         ),
         'result' => 'array'
      );
      
      return (count($this->find('first', $params)) > 0 ? true : false);
   }
}
