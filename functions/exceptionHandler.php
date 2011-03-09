<?
/**
 * Manejador de excepciones no capturadas. En modo desarrollo se muestran todos los detalles.
 * Detecta request mediante ajax y devuelve error mediante JSON con la propiedad "error"
 *
 * @param Exception $e
 * @return void
 */
function exceptionHandler($e)
{
	$request = Request::init();
	Logger::init()->log(Logger::EXCEPTION, $e->getMessage(), $e->getFile(), $e->getLine());
	@ob_clean();
	$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html><head><title>'.get_class($e).' not captured!</title>
	<meta http-equiv="Content-Type" content="text/html; charset='.APP_CHARSET.'" /></head>
	<body style="background-color:blue; color:white; font-family:Lucida Console,monospace; font-size:14px;">';
	$footer = '</body></html>';
	
	if (APP_MODE == 'LIVE') {
		$msg = "A system error ocurred. Whe are already working on that!";
		if ($request->isAjax())	header('X-JSON: '.json_encode(array('error' => $msg)));
		else echo $header.$msg.$footer;
		exit;
	}
	else if ($request->isAjax()) {
		header('X-JSON: '.json_encode(array('error' => get_class($e).' not captured!')));
	}
	
	$sql = (APP_MODE == 'DEV' && $e instanceof QueryException) ? "\n<em><strong>Query:</strong></em>\n".preg_replace("#[ \t]+#", " ", $e->getQuery())."\n" : '';
	
	$msg = '
<div style="background:gray;padding:5px;">'.get_class($e).' not captured</div>
<pre>

<em><strong>Message:</strong></em> '.$e->getMessage().'
'.$sql.'
<em><strong>File:</strong></em> '.$e->getFile().'

<em><strong>Line:</strong></em> '.$e->getLine().'

<em><strong>Stack trace:</strong></em>
'.$e->getTraceAsString().'</pre>';

	die($header.$msg.$footer);
}
?>