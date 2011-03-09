<?php

/**
 * Clase generadora de instrucciones SQL
 *
 * cambios de 1.2 a 1.3:
 * 
 * se reemplaza fetch_array por fetch_assoc en get_select
 * 
 * La clase genera instrucciones SQL y las valida para su uso en el motor de base de datos.
 * La idea es la facilitaci?n del trabajo final de generar metodos que interact?en la base de datos,
 * estando contemplados los aspectos necesarios para evitar errores humanos, injecci?n de c?digo SQL
 * y reduciendo considerablemente el c?digo de quien la implemente.
 *
 * Las instrucci?nes son ejecutadas por la clase de SQL del sistema de JLI, al cual est? implementado
 * el sistema.
 *
 * 	La clase facilita tando el trabajo del developer que el updateo a una tabla seria asi de facil:
 *
 * 	$insertData = array (
 * 		cpl_id => 1,
 *		cpf_fee => 1.2,
 *		dis_id => 0,
 *		dis_extradata1 => "a'ds",
 *	 	dis_extradata2 => 3,
 *		cpf_usdfee => "34.4321"
 *	);
 *
 * 	if ($sqlmaker_obj_sqlMaker->get_update("Course_Place_Fee",$insertData,"1=1")) {
 * 		return true;
 * 	} else {
 * 		return false;
 * 	}
 *
 * 	La clase autom?ticamente analiza el ped?do y castea y valida los datos ingresados a los distintos
 * 	campo de la tabla, si un valor llega alfanum?rico y el campo es un int, este rebotara el pedido
 * 	devolviendo el c?digo de error correspondiente, el cual se puede saber usando el metodo "error_text"
 *
 *	La validaci?n para errores humano simplemente rebota querys malformados
 *
 * 	"SELECT * FROM alguna_tabla;" es una query v?lida, pero
 * 	"SELECT FROM alguna_tabla;" deja de ser v?lida.
 * 	"SELECT * FROM alguna_tabla WHERE texto like '%Jhonn's Smith%'; " tambien sera validado
 *
 * 	"UPDATE alguna_tabla a = 1;" Es incorrecta, por lo que devolver? una query rota,
 *
 * 	PERMISOS EN CHEQUEO:
 * 				   Un tipico error humano es el de instrucciones SQL del tipo UPDATE o DELETE
 * 	sin la condici?n para limitar los rows afectados.
 *
 * 	"UPDATE Productos SET precio = 10;", en primera norma esta instrucci?n va a ser rebotada como
 * 	incorrecta por el metodo "verifica_query". Evitando un error humano que podria costar el rom-
 * 	pimiento de toda la base de datos. Pero como existe la posibilidad de querer updatear o eliminar
 * 	todos los datos de una tabla, se puede agreagar por parametro el aviso.
 *
 * 	verifica_query ("DELETE FROM datos_temps;",2,false,false) // aca le decimos que no verifique
 * 	la existencia del where setiando como false el parametro $no_delete_all y $no_update_all, tambien
 * 	se observa que $querry_level se setea en 2, para dearle permiso de delete y update.
 *
 * 	NIVELES DE QUERY:
 *
 * 		1. (default) SELECT e INSERT, no se afectan registros existentes
 * 		2. SELECT,INSERT,UPDATE y DELETE, aca ya puede afectar registros existentes
 * 		3. Incluye tambien: TRUNCATE, VACUUM (POSTGRES) y OPTIMIZE TABLE (MySQL)
 * 		7. (Modo sistema) Estas ya son para funcionamiento, como por ejemplo, detener el proceso,
 * 		   agregar usuarios, darle permisos. Esto se considera peligroso asi que su uso solo debe
 * 		   ser en caso necesario y no incluye las instrucciones estandart anteriores.
 *
 * @author Oscar J.Gentilezza Arenas <tioscar@gmail.com>
 * @version 1.3 (12/10/06) mod Para el FW De Cesar :P (15/09/2008)
 * @package system
 * @subpackage db
 */

class SqlMaker {

	public $systaxis_failed_level = 3;
	public $err_cod;
	protected $db_op_id;
	public $err_extra;
	protected $sqlmaker_obj_sql;
	
	/* Opciones por motor */ 
	
	//Mysql:
	public $use_mysql_replace = false;

	
	/* Opciones de listado */
	
	public $return_array_list = true;
	
	private $join_tables = false;

	//*** Expresiones regulares mas usadas ***//
	public $exp_table = "[\w\*\s]+";		//Expreg para validar la tabla
	public $exp_where = "([\w\.]+=[\'\w\.\(\)]+|\w+\.\sIN)+";	//Expreg para validar el contenido del Where
	public $valid_cescape = "\\'";		//El escape de la comilla simple, para MSSQL es '' y no \'

	
	//**** Caché ****/
	/*
	public $cache_hab = true;
	public $cache_dir = "/data/cache/db/";
	
	public $cache_model_hab = true;
	public $cache_list_hab = false;
	
	public $cache_exep_model = array();
	public $cache_exep_list = array();
	
	//** Encoding **
	
	public $out_encoding = null;
	

	*/
	
	function __construct (DB &$sqlmaker_obj_sql) {
		
		$this->join_tables = array();

	}//method

	function actualDb () {
		//return $this->sqlmaker_obj_sql->dbname;
		return null; // No implementado en clase de DB
	}//method::get_actual_db
	
	/**
	 * Lista todas las tablas de la db abierta por el objeto usado
	 *
	 * @return array
	 */

	function showTables () {
		$res = $this->sqlmaker_obj_sql->executeQuery("Show tables;");

		if ($res) {
			unset($dato);
			while ($dato = $res->fetchArray($res)) $data[] = $dato[0];
			$this->err_cod = false;
			$this->err_extra = "";
			return isset($data)?$data:false;
		} else {
			$this->err_cod = SQL_MAKER_ERROR_NO_EXECUTE;
			$this->err_extra = "No se pudieron listar las tablas";
			return false;
		}//if

	}//function

	/**
	 * Prepara (si no existe o si se fuerza)
	 *
	 * @param array $scpts = Ecepciones, tablas que no deben ser tratadas
	 * @param boolean $force = Forzar, si ya existe una tabla en cache la sobreescribe
	 */

	function prepare_tableinfo_cache ($scpts = array(),$force = false) {
		$tablas = $this->show_tables();

		foreach (is_array($tablas)?$tablas:array() as $tabla) {
			if (!array_search($tabla,$scpts)) {
				$this->get_table_info($tabla,$force);
			}//if
		}//fe
	}//methods

	/**
	 * Verifica que una query no alla un n?mero inpar de comillas, para evitar los "campo = 'text'o' "
	 *
	 * @param string $query
	 * @return boolean
	 */

	function es_par_comillas ($query) {
		$pregRes = explode("'",str_replace($this->valid_cescape,"",$query));
		$veces = count($pregRes)-1;

		if (($veces/2)==(int)($veces/2)) {
			return true;
		} else {
			return false;
		}//if
	}//method

	/**
	 * Devuelve los datos de campos de una tabla para validacion.
	 *
	 * @param string $table
	 * @return array
	 */

	function get_table_info ($table,$force = false) {
		global $logear;

		if (($this->cache_hab && $this->cache_model_hab) &&  !$force && $data = $this->load_cache_table($table)) {
			return $data;
		}//if
		
		$this->get_select($table,false,"1=2");

		if ($this->err_cod) {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Error al extraer la iformacion de la tabla $table");
			return false;
		}//if

		for ($i =0; $i < $this->sqlmaker_obj_sql->num_fields($this->db_op_id); $i++) {
			$fila = $this->sqlmaker_obj_sql->fetch_field($this->db_op_id,$i);

			$fila_data[$i]['name'] = $fila->name;
			$fila_data[$i]['unsigned'] = $fila->unsigned?true:false;
			$fila_data[$i]['require'] = $fila->not_null?true:false;
			$fila_data[$i]['len'] = $this->sqlmaker_obj_sql->field_len($this->db_op_id,$i);
			$fila_data[$i]['def'] = $fila->def;

			switch ($fila->type) {
				case "int":
					$fila_data[$i]['type'] = 1;
					break;
				case "string":
					$fila_data[$i]['type'] = 2;
					break;
				case "blob":
					$fila_data[$i]['type'] = 3;
					break;
				case "date":
					$fila_data[$i]['type'] = 4;
					break;
				case "datetime":
					$fila_data[$i]['type'] = 5;
					break;
				case "real":
					$fila_data[$i]['type'] = 6;
					break;
			}//switch

		}//for

		if ($this->cache_hab && $this->cache_model_hab)	$this->cache_table($table,$fila_data);

		return $fila_data;

	}//method

	/**
	 * Valida si el tipo de dato es correcto al enviado por el developer
	 *
	 * @param string $campo
	 * @param variant $valor
	 * @return string = valor casteado
	 */

	function valida_datos ($campo,$valor) {
		global $logear,$task,$subtask;
		switch ($campo['type']) {
			case 1:
				$valor_final = (int) $valor;

				if (ereg("\d",$valor)) {
					$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Valor icorrecto para el tipo de datos, no es integer");
					$this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
					$this->err_extra = $valor;
					return false;
				}//if

				if ($campo['unsigned']&& (int) $valor<0) {
					$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Valor icorrecto para el tipo de datos, no es unsigned");
					$this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
					$this->err_extra = $valor;
					return false;
				}//if

				return $valor_final;
			case 2:
				$valor_final = "'".limpiar_sql($valor)."'";
				return $valor_final;
			case 3:
				$valor_final = "'".limpiar_sql($valor)."'";
				return $valor_final;
			case 4:
/*
				if (!preg_match( "/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/i", $valor) ) {
				    $logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Fecha no v?lida $valor (Y-m-d)");
				    $this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
				    $this->err_extra = $valor;
				    return false;
				}//if
*/
				$valor_final = "'".$valor."'";
				return $valor_final;
			case 5:
				/*
				if (!preg_match( "/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) \d{2}:\d{2}:\d{2}$/i", $valor) ) {
				    $logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Fecha no v?lida $valor (Y-m_d h:i:s)");
				    $this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
				    $this->err_extra = $valor;
				    return false;
				}//if
				
				*/
				$valor_final = "'".$valor."'";

				return $valor_final;
			case 6:
				$valor_final = (float) $valor;

				if (ereg("\d",$valor)) {
					$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Valor icorrecto para el tipo de datos, no es real");
					$this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
					$this->err_extra = $valor;
					return false;
				}//if

				if ($campo['unsigned']&& (int) $valor<0) {
					$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Valor icorrecto para el tipo de datos, no es unsigned");
					$this->err_cod = SQL_MAKER_ERROR_INVALID_FIELD_DATA;
					$this->err_extra = $valor;
					return false;
				}//if

				return $valor_final;
			default:
				$valor_final = "'".limpiar_sql($valor)."'";
				return $valor_final;
		}//switch

	}//method

	/**
	 * Devuelve un c?digo de error numerico  (err_cod) en
	 *
	 * @param unknown_type $err
	 * @return unknown
	 */

	function error_text ($err = false) {
		if (!$err) {
			$err = $this->err_cod;
			$extra = $this->err_extra;
		}//if
		if (!$err) return false;

		switch ($err) {
			case SQL_MAKER_ERROR_INVALID_FIELD_DATA:
				return "Tipo de dato invalido para el campo". ($this->err_extra?"($this->err_extra)":"");
				break;
			case SQL_MAKER_ERROR_NO_EXECUTE:
				return "No se pudo ejecutar la instrucci?n generada". ($this->err_extra?"($this->err_extra)":"");
				break;
			case SQL_MAKER_ERROR_NO_FIELD:
				return "No existe el campo requerido o enviado". ($this->err_extra?"($this->err_extra)":"");
				break;
			case SQL_MAKER_ERROR_NO_QUERY_BAD_ESCAPE:
				return "La query est? mal escapada ' = \'". ($this->err_extra?"($this->err_extra)":"");
				break;
			case SQL_MAKER_ERROR_NO_QUERY_IDENTIFY:
				return "No se ha podido identificar la query; inv?lida o fuera de permiso". ($this->err_extra?"($this->err_extra)":"");
				break;
			default:
				return "Error desconocido";
		}//switch
	}//method

	/**
	 * Agrega una tabla para hacer una consulta por x join
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $identifer
	 */

	function xjoin_start ($table = false,$ident = false) {
		if (!$table) return false;
		$this->join_tables = array();
		$this->join_tables[] = array(
			"table" => $table,
			"codition" => false,
			"identifer" =>  $ident,
			"type" =>  false
		);//array
	}//method

	/**
	 * Adiere una tabla para el join
	 *
	 * @param define $type = SQL_MAKER_TYPE_INNER o SQL_MAKER_TYPE_LEFT
	 * @param string $table
	 * @param string $condition
	 * @param string $identifer
	 * @return boolean
	 */

	function xjoin_add ($type = false, $table = false, $condition = false, $identifer = false ) {

		if (!$type) $type = SQL_MAKER_TYPE_INNER;

		if (!preg_match("/$this->exp_table/i",$table)) {
			$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_BAD_ESCAPE;
			$this->err_extra = "Tabla invalida $table";
			return false;
		} else if (!preg_match("/(.*)/i",$condition)) {
			$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_BAD_ESCAPE;
			$this->err_extra = "Condicion invalida $condition";
			return false;
		} else if (!preg_match("/^\w+/i",$identifer)) {
			$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_BAD_ESCAPE;
			$this->err_extra = "Identificador invalido $identifer";
			return false;
		}//elseif

		$this->join_tables[] = array(
			"table" => $table,
			"codition" => $condition,
			"identifer" => $identifer,
			"type" => $type
		);//array

		return true;

	}//method

	/**
	 * Devuelve un from con los joins antes definidos
	 *
	 * @return string
	 */

	function get_join_from () {

		$cadena = "";

		$texts = array (
			1=>"INNER",
			2=>"LEFT"
		);//array

		foreach ($this->join_tables as $joins) {

			if ($joins['type']) {
				$cadena .= " ".$texts[$joins['type']] ." JOIN ". $joins['table'];
				$cadena .= $joins['identifer'] ? " AS {$joins['identifer']}" : "";
				$cadena .= $joins['codition'] ? " ON {$joins['codition']}" : "";
			} else {
				$cadena .= " {$joins['table']}";
				$cadena .= $joins['identifer'] ? " AS {$joins['identifer']}" : "";
			}//if

		}//fe

		return $cadena;

	}//method

	/**
	 * Verifica la query sql si es valida para ser ejecutada
	 *
	 * @param string $query = Query a validar
	 * @param integer $querry_level = Nivel de query
	 * @param boolean $no_update_all = No validar un update sin where (true por defoult)
	 * @param boolean $no_delete_all = No validar un delete sin where (true por defoult)
	 * @return boolean
	 */

	function verifica_query ($query, $querry_level = 1, $no_update_all = true, $no_delete_all = true) {
		global $logear;
		return true;
		$query = trim(preg_replace("/\n/"," ",$query));
		$query = trim(preg_replace("/\s+/"," ",$query));
		$query = preg_replace("/\s?=\s?/i","=",$query);
		$query = preg_replace("/\s?,\s?/i",",",$query);
		$query = preg_replace("/\s?\(\s?/","(",$query);
		$query = preg_replace("/\s?\)/",")",$query);

		switch ($querry_level) {
			case 1:
				if (!preg_match("/^(SELECT |INSERT )/i",$query)) {
					$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
					$this->err_extra = $query;
					return false;
				}//if
				break;
			case 2:
				if (!preg_match("/^(SELECT |INSERT |UPDATE |REPLACE |DELETE )/i",$query)) {
					$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
					$this->err_extra = $query;
					return false;
				}//if
				break;
			case 3:
				if (!preg_match("/^(SELECT |INSERT |UPDATE |REPLACE |DELETE |TRUNCATE |VACUUM |OPTIMIZE TABLE )/i",$query)) {
					$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
					$this->err_extra = $query;
					return false;
				}//if
				break;
			case 7:
				if (!preg_match("/^(ABORT |BEGIN |CLOSE |CLUSTER |COMMIT |COPY |CREATE AGGREGATE |CREATE DATABASE |CREATE FUNCTION |CREATE GROUP |CREATE INDEX |CREATE LANGUAGE |CREATE OPERATOR |CREATE RULE |CREATE SEQUENCE |CREATE TABLE |CREATE TABLE AS |CREATE TRIGGER |CREATE TYPE |CREAR USUARIO |CREAR VISTA |DECLARE |DELETE |DROP AGGREGATE |DROP DATABASE |DROP FUNCTION |DROP GROUP |DROP INDEX |DROP LANGUAGE |DROP OPERATOR |DROP RULE |DROP SEQUENCE |DROP TABLE |DROP TRIGGER |DROP TYPE |DROP USER |DROP VIEW |GRANT |LISTEN |LOAD |LOCK |MOVE |RESET |REVOKE |ROLLBACK |SHOW |OPTIMIZE TABLE )/i",$query)) {
					$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
					$this->err_extra = $query;
					return false;
				}//if
				break;
			default:
				$this->err_cod = false;
				$this->err_extra = false;
				$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"[SqlMaker] No se inserto un tipo valido de query");
		}//switch

		if (preg_match("/^SELECT /i",$query)) {
			$expReg = "/^SELECT .+ FROM $this->exp_table/i";
			if (!preg_match($expReg,$query)) {
				$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
				$this->err_extra = $query;
				return false;
			}//if
		}//if

		/* Por un error, aparentemente de PHP esto queda inutilizado
		if (preg_match("/^UPDATE /i",$query)) {
			$expReg = "/^UPDATE $this->exp_table SET ([\w\s]+=(.)+)+". ($no_update_all?" WHERE $this->exp_where":"") ."/i";
			if (!preg_match($expReg,$query)) {
				$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
				$this->err_extra = $query;
				return false;
			}//if
		}//if
		*/
		
		/* Por un error, aparentemente de PHP esto queda inutilizado
		if (preg_match("/^INSERT /i",$query)) {
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"e");
			$expReg = "/^INSERT INTO $this->exp_table(\([\w\s\,]+\))? VALUES\((.)+\)/i";
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"e#");
			if (!preg_match($expReg,$query)) {
				$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"f");
				$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
				$this->err_extra = $query;
				return false;
			}//if
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"g");
		}//if
		*/
		
		if (preg_match("/^DELETE /i",$query)) {
			$expReg = "/^DELETE FROM $this->exp_table". ($no_delete_all?" WHERE $this->exp_where":"") ."/i";
			if (!preg_match($expReg,$query)) {
				$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_IDENTIFY;
				$this->err_extra = $query;
				return false;
			}//if
		}//if

		if (!$this->es_par_comillas($query)) {
			$this->err_cod = SQL_MAKER_ERROR_NO_QUERY_BAD_ESCAPE;
			$this->err_extra = $query;
			return false;
		}//if

		$this->err_cod = false;
		$this->err_extra = false;
		
		return true;

	}//method

	/**
	 * Genera y/o ejecuta una instrucci?n SELECT
	 *
	 * @param string $table = Tabla
	 * @param string $wath = Elementos a listar (*=todo por defoult)
	 * @param string $where = Condici?n
	 * @param string $orderby = Ordenado por
	 * @param string $limit = Limite
	 * @param boolean $only_query = Si devuelve la query o lo ejecuta
	 * @return array/string
	 */

	function get_select ($table = false, $wath = false, $where = false, $orderby = false, $limit = false, $only_query = false) {
		global $logear;

		if (!$table) {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"[SqlMaker] No se espesifico tabla a listar");
			return false;
		} else {
			$table = limpiar_sql($table);
		}//if

		if (!$wath) {
			$wath ="*";
		} else {
			$wath = preg_replace('/\n/'," ",$wath);
			$wath = limpiar_sql($wath);
		}//if

		$sql = "SELECT $wath FROM $table";

		if ($where) $sql.=" WHERE $where";
		if ($orderby) $sql.=" ORDER BY $orderby";
		if ($limit) $sql.=" LIMIT $limit";
		$sql.=";";

		if ($this->verifica_query($sql)) {
			if ($only_query) {
				$this->err_cod = false;
				$this->err_extra = false;
				return $sql;
			} else {
				if ($res=$this->sqlmaker_obj_sql->query($sql,__FILE__,__LINE__)) {
					
					if ($this->return_array_list) {
						while ($dato = $this->sqlmaker_obj_sql->fetch_assoc($res)) {
							$data[] = $dato;
						}//while
					} else {
						return $res;
					}//if

					if (isset($data)) {
						$this->err_cod = false;
						$this->err_extra = false;
						return $data;
					} else {
						$this->err_cod = false;
						$this->err_extra = false;
						return false;
					}//if
				} else {
					$this->err_cod = SQL_MAKER_ERROR_NO_EXECUTE;
					$this->err_extra = $sql;
					return false;
				}//if
			}//if
		} else {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,$this->systaxis_failed_level,"[SqlMaker] Query rota: ". limpiar_sql($sql));
			return false;
		}//if

	}//method

	/**
	 * Genera y ejecuta seg?n lo pedido un DELETE
	 *
	 * @param string $table = Tabla
	 * @param string $where = Condici?n
	 * @param boolean $no_delete_all = Si puede borrar sin condicion (todo)
	 * @param boolean $only_query = Si solo devuelve la query
	 * @return boolean/string
	 */

	function get_delete ($table = false, $where = false, $no_delete_all = true, $only_query = false) {
		global $logear;

		if (!$table) {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"[SqlMaker] No se espesifico tabla a listar");
			return false;
		} else {
			$table = limpiar_sql($table);
		}//if

		$sql = "DELETE FROM $table";

		if ($where) $sql.=" WHERE $where";
		$sql.=";";

		if ($this->verifica_query($sql,2,false,$no_delete_all)) {
			if ($only_query) {
				return $sql;
			} else {
				if ($res=$this->sqlmaker_obj_sql->query($sql,__FILE__,__LINE__)) {
					$this->err_cod = false;
					$this->err_extra = false;
					return true;
				} else {
					$this->err_cod = SQL_MAKER_ERROR_NO_EXECUTE;
					$this->err_extra = $sql;
					return false;
				}//if
			}//if
		} else {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,$this->systaxis_failed_level,"[SqlMaker] Query rota: ". limpiar_sql($sql));
			return false;
		}//if

	}//method

	/**
	 * Genera y/o ejecuta una instruccion Insert
	 *
	 * @param string $tabla
	 * @param array $datos
	 * @param boolean $only_query
	 * @return int/string = Si $only_query = false devuelve el ID del nuevo registro
	 */

	function get_insert ($tabla,$datos,$only_query = false) {
		global $logear;

		if ($this->use_mysql_replace) {
			$sql = "REPLACE INTO $tabla";
		} else {
			$sql = "INSERT INTO $tabla";
		}//if

		$campos = array();
		$valores = array();
		$campos_db = array();

		reset($datos);

		while (list($key, $val) = each($datos)) {
    			$campos[] = $key;
    			$valores[] = $val;
		}//while

		$tabla_data = $this->get_table_info($tabla);

		foreach (is_array($tabla_data)?$tabla_data:array() as $t_data) {
			$campos_db[$t_data['name']] = $t_data;
		}//fe

		$i=0;

		foreach ($campos as $campo) {
			if (!isset($campos_db[$campo])) {
				$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Campo desconocido: $campo");
				$this->err_cod = SQL_MAKER_ERROR_NO_FIELD;
				$this->err_extra = $campo;
				return false;
			}//if
			$valores_final[$i] = $this->valida_datos($campos_db[$campo],$valores[$i]);
			if ($valores_final[$i]===false) return false;
			$i++;
		}//fe

		$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"a");
		
		$sql .= "(". implode(",",$campos) . ") VALUES (" . implode(",",$valores_final) . ");";

		if ($this->verifica_query($sql)) {
			if ($only_query) {
				return $sql;
			} else {
				if ($res= $this->sqlmaker_obj_sql->query($sql,__FILE__,__LINE__)) {
					$this->err_cod = false;
					$this->err_extra = false;
					$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"alpha");
					return $this->sqlmaker_obj_sql->insert_id($res);
				} else {
					$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_INFO,"beta");
					$this->err_cod = SQL_MAKER_ERROR_NO_EXECUTE;
					$this->err_extra = $sql;
					return false;
				}//if
			}//if
		} else {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,$this->systaxis_failed_level,"[SqlMaker] Query rota: ". $this->error_text());
			return false;
		}//if

	}//method

	/**
	 * Genera y/o ejecuta una instruccion update
	 *
	 * @param string $tabla = Tabla
	 * @param array $datos = Array con los datos (campo => valor)
	 * @param string $where = Condicion
	 * @param boolean $only_query = Si solo presenta la query o la ejecuta
	 * @param boolean $no_update_all = No updatear sin condicion
	 * @return boolean/string = Si pudo hacer el update o la instruccion
	 */

	function get_update ($tabla,$datos,$where,$only_query = false,$no_update_all = true) {
		global $logear;

		$sql = "UPDATE $tabla";

		$campos = array();
		$valores = array();
		$campos_db = array();

		reset($datos);

		while (list($key, $val) = each($datos)) {
    			$campos[] = $key;
    			$valores[] = $val;
		}//while

		$tabla_data = $this->get_table_info($tabla);

		foreach ($tabla_data as $t_data) {
			$campos_db[$t_data['name']] = $t_data;
		}//fe

		$i=0;

		foreach ($campos as $campo) {
			if (!isset($campos_db[$campo])) {
				$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,3,"Campo desconocido: $campo");
				$this->err_cod = SQL_MAKER_ERROR_NO_FIELD;
				$this->err_extra = $campo;
				return false;
			}//if

			$valores_final[$i] = $this->valida_datos($campos_db[$campo],$valores[$i]);
			if ($valores_final[$i]===false) return false;
			$i++;
		}//fe

		$i=0;

		foreach ($campos as $campo) {
			$allSets[] = "$campo={$valores_final[$i]}";
			$i++;
		}//fe

		$sql .= " SET ". implode(",",$allSets);
		if ($where) $sql .= " WHERE $where";

		if ($this->verifica_query($sql,2,$no_update_all)) {
			if ($only_query) {
				return $sql;
			} else {
				if ($res=$this->sqlmaker_obj_sql->query($sql,__FILE__,__LINE__)) {
					$this->err_cod = false;
					$this->err_extra = false;
					return true;
				} else {
					$this->err_cod = SQL_MAKER_ERROR_NO_EXECUTE;
					$this->err_extra = $sql;
					return false;
				}//if
			}//if
		} else {
			$logear->log(0,0,__FILE__,__LINE__,$task,$subtask,$this->systaxis_failed_level,"[SqlMaker] Query rota: ". limpiar_sql($sql));
			return false;
		}//if

	}//method

	//////****************************** CLASES DE CACHE *************************************//////////
	
	
	function cache_exist_select ($table = "", $wath = "", $where = "", $orderby = "", $limit = "") {
		
	}
	
	/**
	 * *****************
	 * 
	 * 
	 */
	
	function save_var_file ($name, &$var,$file) {
		global $logear;
		
		if (!is_array($var)) {
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_ERR,"Error, el dato a guardar no es un array");
			return false;
		}//if
		
		$data = "<?php 
			function data_set_in_$name (&\$var) {
				 \$var=". var_export ($var,true) . " ;
			}//function
				?>";
		
		if ($fp = @fopen($file,"w+")) {
			fwrite($fp, $data,strlen($data));
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_DEBUG,"Variable guardada para cache en ($file) ");
			fclose($fp);
			return true;
		} else {
			$logear->log(0,0,__FILE__,__LINE__,$task,$acc,LOG_ERROR,"Error al intentar guardar archivo, no hay permisos");
			return false;
		}//if
		
	}//method::save_var_file
	
	function remove_table_model_cache ($table) {
		$this->cache_exep_model[$table] = true;
	}//function
	
	function remove_table_list_cache ($table) {
		$this->cache_exep_list[$table] = true;
	}//function
			
	function cache_table ($table, $data) {
		$file = $this->cache_dir ."/" . $this->get_actual_db() . "/model/info.$table.php";
		return $this->save_var_file("table_info_$table",$data,$file);
	}//function
	
	function load_cache_table ($tabla) {
		$file = $this->cache_dir ."/" . $this->get_actual_db() . "/model/info.$tabla.php";
		$var = null;		
		
		if (file_exists($file)) {
			require_once($file);
			eval ("data_set_in_table_info_$tabla (\$var);");
			return $val;
		} else {
			return false;
		}//if
		
	}//function
	
}//class

?>