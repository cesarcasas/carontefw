<?php 

class TemplateMail extends Main{
	
	function __construct(){
		
	}
	
	
	function __destruct(){
		
	}
	/**
	 * Agregar un template
	 *
	 * @param string $html
	 * @param string $subject
	 * @param string $name
	 */
	public static function add($html, $subject, $name){
		global $_AppCaronte;
		TemplateMail::create();
		$template = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$ar = $template->ActiveRecord;
		
		$ar->insert();
		
		$ar->TemplateName = $name;
		$ar->TemplateSubject = $subject;
		$ar->TemplateContent = $html;
		
		
		$ar->save();
	}
	
	/**
	 * editar un template
	 *
	 * @param int $idTemplate
	 * @param string $html
	 * @param string $subject
	 * @param string $name
	 */
	public static function update($idTemplate, $html, $subject, $name){
		global $_AppCaronte;
		TemplateMail::create();
		$template = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$ar = $template->ActiveRecord;
		
		$ar->edit($idTemplate);
		
		$ar->TemplateName = $name;
		$ar->TemplateSubject = $subject;
		$ar->TemplateContent = $html;
		
		
		$ar->save();
	}
	 
	/**
	 * eliminar un template
	 *
	 * @param int $idTemplate

	 */
	public static function delete($idTemplate){
		global $_AppCaronte;
		TemplateMail::create();
		$template = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$ar = $template->ActiveRecord;
		
		$ar->edit($idTemplate);
	
		
		
		$ar->delete();
	}
	
	
	/**
	 * Obtener datos  del template
	 *
	 * @param integer $userID
	 * @return mixed[]
	 * @throws QueryException
	 */
	public static function getTemplateData($templateID)
	{
		global $_AppCaronte;
		TemplateMail::create();
		$user = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$search = $user->ActiveSearch;

		$result = $search->get($templateID);
		return $result[0];
	}
	
	/**
	 * Devuelve la lista de templates
	 * @return ResultSet
	 * @throws QueryException
	 */
	public static function getAllTemplates()
	{
		global $_AppCaronte;
		TemplateMail::create();
		$user = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$search = $user->ActiveSearch;	
		
		$result = $search->find('all');
		
		return $result;
	}
	
	/**
	 * Devuelve la lista de templates
	 * @return ResultSet
	 * @throws QueryException
	 */
	public static function getAllTemplatesByName($templateName)
	{
		global $_AppCaronte;
		TemplateMail::create();
		$user = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$search = $user->ActiveSearch;	
		$parametros = array(
 			'conditions' => array(
  			'=CONCAT(template_name) LIKE' => "'%$templateName%'"
 			)
		);
		$result = $search->find('all',$parametros);
		
		return $result;
	}
	
	
	/**
	 * Devuelve true si existe un template con el nombreTemplate pasado como parametro
	 *
	 * @param string $templateName
	 * @return boolean
	 */
	public static function exists($templateName){
		global $_AppCaronte;
		TemplateMail::create();
		$user = new DataObject('Template', 'template_id', $_AppCaronte->getDBObject());
		$search = $user->ActiveSearch;	
		$parametros = array(
 			'conditions' => array(
  			'=CONCAT(template_name) ' => "'$templateName'"
 			)
		);

		
		$result = $search->find('all',$parametros);
		
		return count($result)>0;
	}
	
	
	
	public static function create(){
		
		global $_AppCaronte;
		$_AppCaronte->getDBObject();
		$dbo = $_AppCaronte->getDBObject();
		$dbo->executeUpdate("CREATE TABLE if not exists `Template` (
		  `template_id` int(11) NOT NULL AUTO_INCREMENT,
		  `template_subject` varchar(30) DEFAULT NULL,
		  `template_name` varchar(20) DEFAULT NULL,
		  `template_content` text,
		  `template_delete` int(1) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`template_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1");
		
		$dbo->executeUpdate("insert  ignore into `Template`(`template_id`,`template_subject`,`template_name`,`template_content`,`template_delete`) values (8,'Registracion','Login','<p>\r\n	&nbsp;</p>\r\n<table cellpadding=\"0\" cellspacing=\"0\" width=\"640\">\r\n	<tbody>\r\n		<tr>\r\n		</tr>\r\n		<tr>\r\n			<td>\r\n				<a href=\"{url}\"><img alt=\"{proyecto}\" border=\"0\" src=\"{imagen}\" /></a></td>\r\n			<td style=\"padding-top: 8px; padding-bottom: 20px;\" valign=\"top\">\r\n				<h3>\r\n					<font color=\"#ea6a00\" face=\"Arial\" size=\"4\">Hola <strong>{nombre},</strong></font></h3>\r\n				<h3>\r\n					&nbsp;</h3>\r\n				<p>\r\n					<font color=\"#000000\" face=\"Arial\" size=\"4\">Muchas Gracias por ser parte de nuestra comunidad.<br />\r\n					<br />\r\n					Para confirmar tu registracion has click en el siguiente link <a href=\"{link}\">{link}</a> <br />\r\n					Guarda los siguientes datos para poder ingresar al sitio como usuario: </font></p>\r\n				<p>\r\n					<b><font color=\"#ea6a00\" face=\"Arial\" size=\"2\">Usuario: {nombre} <br />\r\n					Clave: {pass} </font></b></p>\r\n				<p>\r\n					<font color=\"#000000\" face=\"Arial\" size=\"4\"><br />\r\n					Nos vemos pronto en {proyecto}!</font></p>\r\n				<p>\r\n					<font color=\"#000000\" face=\"Arial\" size=\"4\">Un gran saludo!<br />\r\n					Del Staff de {proyecto}</font></p>\r\n			</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n',0),(10,'Respuesta','Respuesta Contacto','<p>\r\n	<strong><span style=\"font-size: 14px;\">Esta es la respueta a su email enviado desde {proyecto}:<br />\r\n	</span></strong></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">{body}<br />\r\n	</span></strong></span></p>\r\n',0),(9,'contacto','Contacto','<p>\r\n	<strong><span style=\"font-size: 14px;\">Email de contacto enviado desde {proyecto}<br />\r\n	</span></strong></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">Nombre:{nombre}<br />\r\n	</span></strong></span></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">Apellido:{Apellido}<br />\r\n	</span></strong></span></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">Email:{email}<br />\r\n	</span></strong></span></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">Asunto:{asunto}<br />\r\n	</span></strong></span></p>\r\n<p>\r\n	<span style=\"color: rgb(105, 105, 105);\"><strong><span style=\"font-size: 14px;\">Mensaje:{msj}<br />\r\n	</span></strong></span></p>',0),(14,'Novedades','Newsletter','<h2>\r\n	<span style=\"color: rgb(0, 0, 0);\"><strong><span style=\"color: rgb(0, 0, 128);\">Estas son las novedades en</span> {proyecto} :</strong></span></h2>\r\n<p>\r\n	{body}</p>\r\n',0),(15,'recuperar contraseña','recoverpass','Hola<br />\r\n	<br />\r\n	Te enviamos tu nueva contraseña para poder ingresar a {proyecto}!<br />\r\n	<br />\r\n	Nombre de usuario: {username} <br />\r\n	Password: {pass} <br />\r\n	<br />\r\n	Para hacer efectiva esta contraseña, haz <a href=\"{link}/admin/recoverpass/change/{pass}/{hash}\">click aquí</a>\r\n	<br />\r\n	Saludos,<br />\r\n	El equipo de {proyecto}!<br />\r\n	<br />\r\n	<br />\r\n	',0);");
		
	}		
		


	
	
}