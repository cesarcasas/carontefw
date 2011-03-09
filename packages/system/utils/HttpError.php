<?
	/**
	 * Clase para manejar errores http. La clase mas al pedo que existe.
	 * Utiliza el template http_{errno} si existe.
	 *
	 * @package system.utils
	 */
	class HttpError extends Main
	{
		/**
		 * Enviar error http 4**
		 *
		 * @param integer $errno
		 * @return void
		 * @throws InvalidArgumentException Si el código de error es invalido
		 */
		public static function send($errno)
		{
			$tpl = new Template();
			switch ($errno)
			{
				case 400:
					$desc = "Bad Request";
					break;
				case 401:
					$desc = "Unauthorized";
					break;
				case 402:
					$desc = "Payment Required";
					break;
				case 403:
					$desc = "Forbidden";
					break;
				case 404:
					$desc = "Not Found";
					break;
				case 405:
					$desc = "Method Not Allowed";
					break;
				case 406:
					$desc = "Not Acceptable";
					break;
				case 407:
					$desc = "Proxy Authentication Required";
					break;
				case 408:
					$desc = "Request Timeout";
					break;
				case 409:
					$desc = "Conflict";
					break;
				case 410:
					$desc = "Gone";
					break;
				default:
					throw new InvalidArgumentException("$errno is not a valid HTTP error code");
			}
				
			header("HTTP/1.0 $errno $desc");
			try {
				$tpl->header("http_$errno", $desc);
			} catch (FileNotFoundException $e) {
				$tpl->header("", $desc);
				echo "<p>HTTP Error: $errno $desc</p>";
			}
			$tpl->footer("");
			exit;
		}
	}
?>