<?php
	
	import("system.lang.NoSuchFieldException", "system.lang.NoSuchMethodException");
	
	/**
	 * Clase global. Todas las clases deben extender de ésta.
	 * Posee metodos para evitar posibles estupideces como establecer nuevas propiedades o llamar metodos inexistentes.
	 * 
	 * @package system.lang
	 */
	abstract class Main
	{
		/**
		 * Este metodo se llama cuando se intenta obtener una propiedad inexistente
		 *
		 * @param string $prop
		 * @throws NoSuchFieldException
		 */
		public function __get($prop)
		{
			if (!property_exists($this, $prop))
				throw new NoSuchFieldException("Field \"<em><strong>$prop</strong></em>\" does not exist!");
		}
		/**
		 * Este metodo se llama cuando se intenta establecer una propiedad inexistente
		 *
		 * @param string $prop
		 * @param mixed $value
		 * @throws NoSuchFieldException
		 */
		public function __set($prop, $value)
		{
			if (!property_exists($this, $prop))
				throw new NoSuchFieldException("Field \"<em><strong>$prop</strong></em>\" does not exist!");
		}
		/**
		 * Este metodo se llama cuando se intenta llamar a un metodo inexistente
		 *
		 * @param string $method
		 * @param mixed[] $args
		 */
		public function __call($method, $args)
		{
			if (!method_exists($this, $method))
				throw new NoSuchMethodException("Method \"<em><strong>$method</strong></em>\" does not exist!");
		}
		/**
		 * Obtener objeto como una cadena.
		 *
		 * @return string
		 */
		public function __toString()
		{
			return "Object ".get_class($this);
		}
	}
?>