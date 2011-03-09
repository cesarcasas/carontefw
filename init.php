<?php 
/**
 * Load basic files for Caronte Framework.
 */

define('FW_TEMPLATEBLOCKS_PATH', PATH_FRAMEWORK."/templates/Blocks");

require_once(PATH_FRAMEWORK."/Application.php");

global $_AppCaronte;


require_once(PATH_FRAMEWORK."/functions/import.php");
require_once(PATH_FRAMEWORK."/functions/importJS.php");
require_once(PATH_FRAMEWORK."/functions/importCSS.php");
require_once(PATH_FRAMEWORK."/functions/_.php");
require_once(PATH_FRAMEWORK."/functions/_n.php");
require_once(PATH_FRAMEWORK."/functions/_replace.php");
require_once(PATH_FRAMEWORK."/functions/array_collect.php");  


