<?php 


class Events{
    
    
    private $tableEvents;
    private $db;
    private $collection;
    private $storage;
    private $user;
    
    
    /**
     * Enter description here...
     *
     * @param boolean $storage  almacena o no los datos en forma persistente
     * @param integer $user  el id de usuario al que se le asignaran los eventos.
     */
    function __construct($storage=false, $user=0){
    	global $db;
        $this->tableEvents=TABLE_EVENTS;
        $this->db =$db;
        
        //false: memory  true: database
        $this->storage= $storage==false || $storage=="" ? false : true;
        $this->user=$user;
    }
    
    
    /**
     * Agrega un evento (ya sea en el array principal o el la DB
     *
     * @param string $evt_source informacion del origen del evento
     * @param integer $evt_const el ID del evento
     * @param string $evt_data json del objeto de datos
     */
    public function add($evt_source, $evt_const, $evt_data){

        if($this->storage) $this->addIntoDatabase($this->user, $evt_source, $evt_const, $evt_data);
        else                $this->register($evt_source, $evt_const, $evt_data);
    }
    
    
     
    
    /**
     * Graba el evento en la tabla de la base de datos
     *
     * @param integer $user_id el id del usuario al que se le esta asignando un evento.
     * @param string $evt_source informacion del origen del evento
     * @param integer $evt_const el ID del evento
     * @param string $evt_data json del objeto de datos
     */
    private function addIntoDatabase($user_id=0, $evt_source=0, $evt_const=0, $evt_data=''){
            
    	$user=(int)$user_id==0 ? $this->user : $user_id;
    	
    	//,evt_data='".$this->db->escapeString(json_encode($evt_data))."'
    	
    	//die(json_encode($evt_data));
    	
        $sql="INSERT INTO ".$this->tableEvents."
        SET 
        user_id=".$user."
        ,evt_source='$evt_source'
        ,evt_const='$evt_const'
        ,evt_data='".$this->db->escapeString(json_encode($evt_data))."'
        ,evt_ts=timestamp (now())
        ";    
        
        $this->db->executeUpdate($sql);
    }
    

    /**
     * Registra el evento en un array
     *
     * @param string $evt_source informacion del origen del evento
     * @param integer $evt_const el ID del evento
     * @param string $evt_data json del objeto de datos
     */
    private function register($evt_source, $evt_const, $evt_data){
        $this->collection[]=$this->makeArrayEvent($evt_const, $evt_data);
    }
    
    
    /**
     * Genera el array final de eventos 
     *
     * @param unknown_type $evt_const
     * @param unknown_type $evt_data
     * @return array
     */
    private function makeArrayEvent($evt_const, $evt_data){
        return array('event' => $evt_const, 'params' => $evt_data);
    }
    
    /**
     * Registra los eventos de la base de datos en el array principal
     *
     * @return void
     */
    private function getEventsFromDatabase(){
        $user=$this->user;
        
         $sql="SELECT * FROM ".$this->tableEvents."
        WHERE user_id=$user OR user_id=0";    
        
        $result=$this->db->executeQuery($sql);
        
        while($filas=$result->fetchArray()){
        	
        	
        	//die(var_dump(json_decode($filas['evt_data'])));
        	
            $this->register($filas['evt_source'], $filas['evt_const'], json_decode($filas['evt_data']));
            ///$this->register($filas['evt_source'], $filas['evt_const'], $filas['evt_data']);
        }
        
        $this->clear();
    }
    
    
    /**
     * clear events for a user
     *
     */
    public function clear(){
        $user=$this->user;
        $sql="DELETE FROM ".$this->tableEvents."
        WHERE user_id=".(int)$user;
        $this->db->executeUpdate($sql);
    }
    
    
    /**
     * Devuelve el total de eventos registrado (tanto en memoria como en la DB
     *
     * @return array
     */
    public function getRegisterEvents(){
        $this->getEventsFromDatabase();
        
        
        return $this->collection;
    }
    
} 