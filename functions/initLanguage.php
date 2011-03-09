<?
	/**
	 * Inicializar lenguage segun selección del usuario.
	 *
	 * @return void
	 */
	function initLanguage()
	{
		$userLangs = User::getLanguages();
		$lang = isset($_GET['lang']) ? $_GET['lang'] : $userLangs[0];
		
		$defaultLanguage = 'en';
		$availableLanguages = array('en', 'es');
		$defaultCountries = array('en' => 'US', 'es' => 'ES');
		
		$lang = isset($lang) && in_array($lang, $availableLanguages) ? $lang : $defaultLanguage;
		$lang .= "_".$defaultCountries[$lang];
		
		$locale = $lang.".utf8";
		putenv("LC_ALL=$lang");
		setlocale(LC_ALL, $locale);
		bindtextdomain("global", "./locale");
		bind_textdomain_codeset("global", APP_CHARSET);
		textdomain("global");
	}
?>