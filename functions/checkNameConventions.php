<?
	import("system.phpdoc.DocComment");
	
	/**
	 * Chequear nomenclatura y documentación de clases declaradas e incluidas.
	 * 
	 * @param boolean $checkClassDoc Chequear documentación de clases
	 * @param boolean $checkMethodDoc Chequear documentación de métodos publicos
	 * @param boolean $checkMethodParams Chequear nomeclatura de parametros de los metodos
	 * @param boolean $checkClassFieldsNames Chequear nomenclatura de las propiedades publicas/protegidas de las clases
	 * @param boolean $checkClassFieldsTypes Chequear tipos de dato en propiedades de clase public/protected
	 * @return void
	 * @throws Exception If errors ocurred, this contains info for each of them
	 */
	function checkNameConventions($checkClassDoc = true, $checkMethodDoc = true, $checkMethodParams = false, 
									$checkClassFieldsNames = false, $checkClassFieldsTypes = false) {
		$errors = array();
		$classes = get_declared_classes();
		foreach ($classes as $c) {
			$r = new ReflectionClass($c);
			if (!$r->isUserDefined()) continue;
			if ($checkClassDoc && $r->getDocComment() == "") $errors[] = "Class \"".$r->getName()."\" is not documented!";
			
			if (!preg_match("#^[A-Z][a-zA-Z0-9]+$#", $r->getName())) {
				$errors[] = "Class \"".$r->getName()."\" does not follow framework naming conventions!";
			}
			
			$methods = $r->getMethods();
			foreach ($methods as $method) {
				$methodName = $method->getName();
				
				// check method name
				if ($method->isInternal() || $method->isConstructor() || $method->isDestructor() 
						|| strpos($methodName, "__") === 0) continue;
				$methodDocComment = new DocComment($method->getDocComment());
				$methodInfo = "[ Line " . $method->getStartLine() . " ]";
				$methodReturn = $methodDocComment->getTag('return');
				if (!preg_match("#^[a-z][a-zA-Z0-9]+$#", $methodName)) {
					$errors[] = "Method \"$methodName\" from class \"$c\" does not follow framework naming conventions! $methodInfo";
				}
				
				// check if method is public/protected and is documented
				if ($checkMethodDoc && ($method->isPublic() || $method->isProtected())) {
					if ($methodDocComment->isEmpty()) {
						$errors[] = "Method \"$c::$methodName\" is not documented! $methodInfo";
					}
					else if ($methodReturn == null || preg_match("#^unknown#", $methodReturn['type'])) {
						$errors[] = "Method \"$c::$methodName\" does not have a valid return type! $methodInfo";
					}
					else if ($methodDocComment->getTag('param') != null && count($methodDocComment->getTag('param')) != $method->getNumberOfParameters()) {
						$errors[] = "The number of documented parameters does not match real parameters in method \"$c::$methodName\". $methodInfo";
					}
				}
					
				// check params
				if ($checkMethodParams) {
					$params = $method->getParameters();
					foreach ($params as $param) {
						$paramName = $param->getName();
						if (strpos($paramName, "_") !== false || preg_match("#^[A-Z][a-z]+$#", $paramName)) {
							$errors[] = "Parameter \"$paramName\" from method \"$c::$methodName\" does not follow framework naming conventions! $methodInfo";
						}
					}
				}
			}
			
			// revisar propiedades de la clase
			$properties = $r->getProperties();
			foreach ($properties as $property) {
				$name = $property->getName();
				$propertyDocComment = new DocComment($property->getDocComment());
				if ($checkClassFieldsNames && !preg_match("#^[a-z][a-zA-Z0-9]+$#", $name)) {
					$errors[] = "Property \"$name\" from class \"$c\" does not follow framework naming conventions!";
				}
				if ($checkClassFieldsTypes && $propertyDocComment->getTag('var') == null && ($property->isPublic() || $property->isProtected())) {
					$errors[] = "Property \"$name\" from class \"$c\" does not have a valid data type!";
				}
			}
		}
		
		if (!empty($errors)) throw new Exception(implode("<br />", $errors));
	}
?>