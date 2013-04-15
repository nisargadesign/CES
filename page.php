<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  page.php                                                 ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




	include_once("./includes/common.php");

	include_once("./messages/" . $language_code . "/cart_messages.php");

	include_once("./messages/" . $language_code . "/forum_messages.php");

	include_once("./includes/products_functions.php");

	include_once("./includes/shopping_cart.php");

	include_once("./includes/ads_functions.php");

	include_once("./includes/navigator.php");



	$tax_rates = get_tax_rates();



	$cms_page_code = "custom_page";

	$script_name   = "page.php";

	$current_page  = get_custom_friendly_url("page.php");



	$custom_page_id = "";

	$custom_page_code = get_param("page");

	$user_id = get_session("session_user_id");		

	$user_info = get_session("session_user_info");

	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	

	$page_friendly_url = ""; $page_friendly_params = array();

	if (strlen($custom_page_code)) 

	{

		$sql  = " SELECT p.page_id, p.friendly_url, p.meta_title,p.meta_description,p.meta_keywords,p.is_html,p.page_type,p.page_url,p.page_path,";

		$sql .= " p.page_title,p.page_body FROM ";

		if (isset($site_id)) {

			$sql .= "(";

		}

		if (strlen($user_id)) {

			$sql .= "(";

		}

		$sql .= $table_prefix . "pages p ";

		if (isset($site_id)) {

			$sql .= " LEFT JOIN " . $table_prefix . "pages_sites ps ON ps.page_id=p.page_id) ";

		}

		if (strlen($user_id)) {

			$sql .= " LEFT JOIN " . $table_prefix . "pages_user_types ut ON ut.page_id=p.page_id) ";

		}

		$sql .= " WHERE p.is_showing=1 AND p.page_code=" . $db->tosql($custom_page_code, TEXT);

		if (isset($site_id)) {

			$sql .= " AND (p.sites_all=1 OR ps.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";

		} else {

			$sql .= " AND p.sites_all=1 ";					

		}		

		if (strlen($user_id)) {

			$sql .= " AND ( p.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id , INTEGER) . " )";

		} else {

			$sql .= " AND p.user_types_all=1 ";

		}

		$db->query($sql);

		if ($db->next_record())

		{

			$page_friendly_url = $db->f("friendly_url");

			if ($page_friendly_url) {

				$page_friendly_params[] = "page";

				friendly_url_redirect($page_friendly_url, $page_friendly_params);

			}

			// meta data

			$meta_title = get_translation($db->f("meta_title"));

			$meta_description = get_translation($db->f("meta_description"));

			$meta_keywords = get_translation($db->f("meta_keywords"));



			$is_html = $db->f("is_html");

			$custom_page_id = $db->f("page_id");

			$page_type = $db->f("page_type");

			$page_url = $db->f("page_url");

			$page_path = $db->f("page_path");

			if (strlen($page_url))

			{

				header("HTTP/1.0 302 OK");

				header("Status: 302 OK");

				header("Location: " . $page_url);

				exit;

			}

			$page_title = get_translation($db->f("page_title"));

			$page_title = get_currency_message($page_title, $currency);

			$page_body = get_translation($db->f("page_body"));

			$page_body = strlen($page_path) ? @join("", file($page_path)) : $page_body;

			$page_body = get_currency_message($page_body, $currency);

			if (get_setting_value($settings, "php_in_custom_pages", 0)) {

				eval_php_code($page_body);

			}

			$page_body = $is_html ? $page_body : "<div align=\"justify\">" . nl2br(htmlspecialchars($page_body)) . "</div>";



		}

		else

		{

			//$page_title = "Page Error";

			//$page_body = "<div align=\"center\"><font color=\"red\"><b>Page '" . htmlspecialchars($custom_page_code) . "' was not found</b></font></div>";

			header ("Location: index.php");

			exit;

		}

	}

	else

	{

		header ("Location: index.php");

		exit;

	}



	if ($page_type == 2) { 

		$t = new VA_Template($settings["templates_dir"]);

		$t->set_file("main", "page_popup.html");

		include_once("./header.php");

		$t->set_var("page_title", $page_title);

		$t->set_var("page_body", $page_body);			

		$t->pparse("main");

		return;

	}



	$sql  = " SELECT ps_id FROM " . $table_prefix . "cms_pages_settings ";

	$sql .= " WHERE key_code=" . $db->tosql($custom_page_id, TEXT);

	$sql .= " AND key_rule='custom'";

	if (isset($site_id) && $site_id != 1) {

		$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ") ";

	} else {

		$sql .= " AND site_id=1 ";

	}

	$cms_ps_id = get_db_value($sql);
	
		//Customization by Vital - canonical URL
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	}
	//END customization

	include_once("./includes/page_layout.php");



?>
