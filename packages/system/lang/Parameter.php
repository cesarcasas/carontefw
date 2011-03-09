<?
import("system.lang.Main", "system.lang.IllegalArgumentException"); 
		
/**
	 * Clase para validar los parametros de los metodos. Intenta hacer que PHP sea un lenguaje serio.
	 * 
	 * @package	system.lang
	 */
	class Parameter extends Main
	{
		private function __construct() {}
		
		/**
		 * Revisa los parametros y checkea si son del tipo correcto.
		 * Permite especificar array de una clase específica. Ej: User[]
		 * 
		 * Tipos de datos:<br />
		 * <ul>
		 * 	<li>int / integer</li>
		 * 	<li>float</li>
		 * 	<li>bool / boolean</li>
		 * 	<li>string</li>
		 * 	<li>array / mixed[]</li>
		 * 	<li>object</li>
		 * 	<li>resource</li>
		 * 	<li>int[] / integer[]</li>
		 * 	<li>float[]</li>
		 * 	<li>bool[] / boolean[]</li>
		 * 	<li>string[]</li>
		 * 	<li>object[]</li>
		 * </ul>
		 * 
		 * @param 	mixed[]	$args las claves son el tipo de dato y los valores son los datos a evaluar, pueden ser arrays de valores.
		 * @param 	string	$function funcion/metodo que recibio el parametro
		 * @return void
		 * @example
		  
		   Parameter::check(array(
					'string' 	=> $name,
  					'int' 		=> array($crazyNumber, $otherNumber),
  					'boolean'	=> $showMyAss,
  					'HTMLTable'	=> $table // Acepta una referencia a un objeto de tipo HTMLTable o null
				), __METHOD__);
		 * @throws 	InvalidArgumentException Si el parametro no es del tipo correcto.
		 */
		public static function check(array $args, $function)
		{
			if (!CHECK_METHOD_PARAMETERS) return;
			
			$errorMsg = "Illegal argument passed to function '$function'.";
			foreach ($args as $type => $value)
			{
				$value = (array)$value;
				switch ($type)
				{
					case "int":
					case "integer":
						foreach ($value as $v)
						if (!STRICT_PARAMETER_CHECK && !is_numeric($v))	throw new InvalidArgumentException("$errorMsg Numeric value expected. Values: $v");
						else if (STRICT_PARAMETER_CHECK && !is_int($v))	throw new InvalidArgumentException("$errorMsg Integer expected. Values: $v");
						break;
					case "float":
						foreach ($value as $v)
							if (!is_float($v)) throw new InvalidArgumentException("$errorMsg Float expected. Values: $v");
						break;
					case "boolean":
					case "bool":
						foreach ($value as $v)
							if (!is_bool($v)) throw new InvalidArgumentException("$errorMsg Boolean expected. Values: $v");
						break;
					case "string":
						foreach ($value as $v)
							if (!is_string($v) && $v !== null) throw new InvalidArgumentException("$errorMsg String expected. Values: $v");
						break;
					case "mixed[]":
					case "array":
						foreach ($value as $v)
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected. Values: $v");
						break;
					case "object":
						foreach ($value as $v)
							if (!is_object($v) && $v !== null) throw new InvalidArgumentException("$errorMsg Object expected. Values: $v");
						break;
					case "resource":
						foreach ($value as $v)
							if (!is_resource($v)) throw new InvalidArgumentException("$errorMsg Resource expected. Values: $v");
						break;
					case "int[]":
					case "integer[]":
						foreach ($value as $v) {
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected.");
							foreach ($v as $vv) if (!is_int($vv)) throw new InvalidArgumentException("$errorMsg Integer array expected. Values: $v");
						}
						break;
					case "float[]":
						foreach ($value as $v) {
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected.");
							foreach ($v as $vv) if (!is_float($vv)) throw new InvalidArgumentException("$errorMsg Float array expected. Values: $v");
						}
						break;
					case "boolean[]":
					case "bool[]":
						foreach ($value as $v) {
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected.");
							foreach ($v as $vv) if (!is_bool($vv)) throw new InvalidArgumentException("$errorMsg Boolean array expected. Values: $v");
						}
						break;
					case "string[]":
						foreach ($value as $v) {
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected.");
							foreach ($v as $vv) if (!is_string($vv)) throw new InvalidArgumentException("$errorMsg String array expected. Values: $v");
						}
						break;
					case "object[]":
						foreach ($value as $v) {
							if (!is_array($v)) throw new InvalidArgumentException("$errorMsg Array expected.");
							foreach ($v as $vv) if (!is_object($vv)) throw new InvalidArgumentException("$errorMsg String array expected. Values: $v");
						}
						break;
					default:
						preg_match("#(\w+)\[\]#i", $type, $match);
						if ($match) {
							foreach ($value as $v) if (!($v instanceof $match[1]) && $v !== null) throw new InvalidArgumentException("$errorMsg {$match[1]} Array expected.");
						}
						else {
							foreach ($value as $v) if (!($v instanceof $type) && $v !== null) throw new InvalidArgumentException("$errorMsg $type expected.");
						}
				}
			}
		}
	}
?>