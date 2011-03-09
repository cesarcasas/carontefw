<?
	/**
	 * Métodos para obtener información util del usuario.
	 *
	 * @package system.utils
	 */
	class User extends Main
	{
		private function __construct() {}
		
		/**
		 * Devolver array con lenguajes del usuario segun navegador.
		 *
		 * @return boolean $getCountryCode Obtener codigo de país. Ej: ar, us, fr
		 * @return string[] 
		 */
		public static function getLanguages($getCountryCode = false)
		{
			if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return array();
			
			$langs = explode(",", strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
			foreach ($langs as &$lang) $lang = preg_replace("#;q=[\d.]+#", "", $lang);
			if (!$getCountryCode)
			{
				foreach ($langs as &$lang) $lang = preg_replace("#[_-][a-z]+$#", "", $lang);
				$langs = array_unique($langs);
			}
			
			return $langs;
		}
		/**
		 * Obtener localizacion del usuario segun navegador.
		 *
		 * @return string codigo de lenguaje y codigo de país separados mediante guion bajo. Ej: es_AR, en_US
		 */
		public static function getLocale()
		{
			$defaultCountries = array(
				'es'	=> 'AR',
				'en'	=> 'US',
				'fr'	=> 'FR',
				'it'	=> 'IT'
			);
			$defaultLang = APP_LANGUAGE;
			$defaultCountry = isset($defaultCountries[$defaultLang]) ? $defaultCountries[$defaultLang] : "US";
			$defaultLocale = "en_US.utf8";
			$browserLangs = self::getLanguages(true);
			if (!empty($browserLangs)) {
				$tmp = explode("-", $browserLangs[0]);
				$lang = strtolower($tmp[0]);
				$countryCode =  count($tmp) < 2 ? $defaultCountry : strtoupper($tmp[1]);
			}
			else {
				$lang = $defaultLang;
				$countryCode = $defaultCountry;
			}
			$locale = $lang."_".$countryCode.".utf8";
			@exec('locale -a', $systemLocales);
			if (!in_array($locale, (array)$systemLocales) && isset($defaultCountries[$lang])) {
				$countryCode = $defaultCountries[$lang];
				$locale = $lang."_".$countryCode.".utf8";
			}
				
			return in_array($locale, (array)$systemLocales) ? $locale : $defaultLocale;
		}
		/**
		 * Obtener navegador
		 *
		 * @return string
		 */
		public static function getBrowser()
		{
			return $_SERVER['HTTP_USER_AGENT'];
		}
		/**
		 * Obtener sistema operativo
		 *
		 * @return string
		 */
		public static function getPlatform()
		{
			return "";
		}
		/**
		 * Obtener lenguaje principal como código de 2 letras en minuscula. Ej: es, en, fr
		 *
		 * @return string
		 */
		public static function getLanguage()
		{
			$langs = self::getLanguages();
			return empty($langs) ? "" : $langs[0];
		}
	}
?>