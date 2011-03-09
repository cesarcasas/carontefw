<?
	/**
	 * This class represents a system module.
	 * Each module is a folder under /modules or a subfolder under another module.
	 * For the sake of simplicity, the module name needs to be the same as the folder name.
	 *
	 * @package system.lang
	 */
	class Module extends Main {
		/**
		 * @var string
		 */
		protected $name;
		/**
		 * @var string
		 */
		protected $root;
		/**
		 * @var string
		 */
		protected $URL;
		/**
		 * @var string
		 */
		protected $friendlyURL;
		
		/**
		 * Constructor
		 *
		 * @param string $file File path where this class is instantiated.
		 */
		public function __construct($file) {
			$this->root = dirname($file);
			$this->name = basename($this->root);

			$parent = $this->getParent();
			$parentURL = ($parent != null) ? $parent->getURL() : APP_MODULES_URL;
			$this->URL = $parentURL.'/'.$this->name;
			
			$parentFriendlyURL = ($parent != null) ? $parent->getFriendlyURL() : APP_URL;
			$this->friendlyURL = $parentFriendlyURL.'/'.$this->name;
		}
		/**
		 * Get module name
		 *
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
		/**
		 * Get module root path.
		 *
		 * @return string
		 */
		public function getRoot() {
			return $this->root;
		}
		/**
		 * Get module URL.
		 *
		 * @return string
		 */
		public function getURL() {
			return $this->URL;
		}
		/**
		 * Get module friendly URL. ModRwrite is needed.
		 *
		 * @return string
		 */
		public function getFriendlyURL() {
			return $this->friendlyURL;
		}
		/**
		 * Get parent module. Returns null if no parent exists.
		 *
		 * @return Module
		 */
		public function getParent() {
			return dirname($this->root) == APP_MODULES ? null : new Module($this->root);
		}
		/**
		 * Get ancestor modules
		 *
		 * @return Modules[]
		 */
		public function getAncestors() {
			$p = $this->getParent();
			$ancestors = array();
			while ($p) {
				$ancestors[] = $p;
				$p = $p->getParent();
			}
			return $ancestors;
		}
	}
?>