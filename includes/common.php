<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  common.php                                               ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	error_reporting(E_ALL);
	@ini_set("magic_quotes_runtime", 0);

	// version information
	define("VA_PRODUCT","shop");
	define("VA_TYPE","enterprise");
	define("VA_RELEASE","4.0.5");

	$root_folder_path = (isset($is_admin_path) && $is_admin_path) ? "../" : "./";

	@include_once($root_folder_path . "includes/var_definition.php");
	if (!defined("INSTALLED") || !INSTALLED) {
		header("Location: " . $root_folder_path . "install.php");
		exit;	
	}
	include_once($root_folder_path . "includes/constants.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");
	include_once($root_folder_path . "includes/cms_functions.php");
	include_once($root_folder_path . "includes/sms_functions.php");
	$language_code = get_language("messages.php");
	include_once($root_folder_path ."messages/" . $language_code . "/messages.php");
	include_once($root_folder_path . "includes/date_functions.php");
	include_once($root_folder_path . "includes/url.php");
	include_once($root_folder_path . "includes/template.php");
	include_once($root_folder_path . "includes/db_$db_lib.php");
	include_once($root_folder_path . "includes/tree.php");
	if (file_exists($root_folder_path . "includes/license.php") ) {
		include_once($root_folder_path . "includes/license.php");
	}

	// start session
	session_start();
	
	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	// get site properties
	$settings = va_settings();
	$custom_friendly_urls = prepare_custom_friendly_urls();

	// check ssl connection
	$is_ssl = (strtoupper(get_var("HTTPS")) == "ON" || get_var("SERVER_PORT") == 443 || get_var("SSL") == 1);

	// make currency available from any page
	$currency = get_currency();

?>