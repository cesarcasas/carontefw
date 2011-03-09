<?php 
/**
 * Esta clase no cumple con los estandares del framework
 * @deprecated
 *
 */
class Security{
	public function __construct(){
		
	}
	
	public static function genPass($len){
		Parameter::check(
					array('integer' => $len)
					,__METHOD__);	
		
		
                if($len<=0) $len=8;
                $pass="";
                for($i=0;$i<$len;$i++){
                        switch(rand(1,3)) {
                                case 1:
                                $pass.= chr(rand(65,90));
                                break;
                                case 2:
                                $pass.= chr(rand(97,122));
                                break;
                                case 3:
                                $pass.= rand(0,9);
                                break;
                        }//fin de switch
                }
                return strtolower($pass);
        }

}
?>