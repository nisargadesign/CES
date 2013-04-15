<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  products.php                                             ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




	$type = "list";

	include_once("./includes/common.php");

	include_once("./messages/" . $language_code . "/cart_messages.php");

	include_once("./messages/" . $language_code . "/reviews_messages.php");

	include_once("./messages/" . $language_code . "/download_messages.php");

	include_once("./includes/sorter.php");

	include_once("./includes/navigator.php");

	include_once("./includes/items_properties.php");

	include_once("./includes/products_functions.php");

	include_once("./includes/shopping_cart.php");

	include_once("./includes/filter_functions.php");

	include_once("./includes/previews_functions.php");



	$display_products = get_setting_value($settings, "display_products", 0);

	if ($display_products == 1) {

		// user need to be logged in before viewing products

		check_user_session();

	}



	$cms_page_code = "products_list";

	$script_name   = "products.php";

	$current_page  = get_custom_friendly_url("products.php");

	$tax_rates = get_tax_rates();

	

	$category_id        = get_param("category_id");

	$search_category_id = get_param("search_category_id");

	if (strlen($search_category_id)) {

		$category_id = $search_category_id;

	} elseif (!strlen($category_id)) {

		$category_id = 0;

	}



	if ($category_id) {

		if (VA_Categories::check_exists($category_id)) {

			if (!VA_Categories::check_permissions($category_id, VIEW_CATEGORIES_ITEMS_PERM)) {



				$site_url = get_setting_value($settings, "site_url", "");

				$secure_url = get_setting_value($settings, "secure_url", "");

				$secure_user_login = get_setting_value($settings, "secure_user_login", 0);

				if ($secure_user_login) {

					$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");

				} else {

					$user_login_url = $site_url . get_custom_friendly_url("user_login.php");

				}

				$return_page = get_request_uri();

				header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=2&ssl=".intval($is_ssl));

				exit;

			}

		} else {

			echo NO_RECORDS_MSG;

			exit;

		}

	}

		

	$manf = get_param("manf");



	$list_template = ""; $current_category = "";
	set_session("category_id", $current_category); //Customization by Vital

	$page_friendly_url = ""; $page_friendly_params = array();

	$show_sub_products = false; $category_path = "";



	// retrieve info about current category

	$sql  = " SELECT * FROM " . $table_prefix . "categories ";	

	$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);

	

	$db->query($sql);

	if ($db->next_record()) {

		$category_id = $db->f("category_id");

		$current_category = get_translation($db->f("category_name"));

		$page_friendly_url = $db->f("friendly_url");

		if ($page_friendly_url) {

			$page_friendly_params[] = "category_id";

			friendly_url_redirect($page_friendly_url, $page_friendly_params);

		}

		$short_description = get_translation($db->f("short_description"));

		$full_description = get_translation($db->f("full_description"));

		$show_sub_products = $db->f("show_sub_products");

		$category_path = $db->f("category_path") . $category_id . ",";



		$list_template = $db->f("list_template");

		if (!@file_exists($list_template)) { 

			if (!@file_exists($settings["templates_dir"]."/".$list_template) && !@file_exists("templates/user/".$list_template)) { $list_template = ""; }

		}



		$meta_title = get_translation($db->f("meta_title"));

		$meta_description = get_translation($db->f("meta_description"));

		$meta_keywords = get_translation($db->f("meta_keywords"));

		$total_views = $db->f("total_views");



		// check if we need to generate auto meta data 

		if (!strlen($meta_title)) { $auto_meta_title = $current_category; }

		if (!strlen($meta_description)) {

			if (strlen($short_description)) {

				$auto_meta_description = $short_description;

			} elseif (strlen($full_description)) {

				$auto_meta_description = $full_description;

			}		

		}



		// update total views for categories

		$products_cats_viewed = get_session("session_products_cats_viewed");

		if (!isset($products_cats_viewed[$category_id])) {

			$sql  = " UPDATE " . $table_prefix . "categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);

			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);

			$db->query($sql);



			$products_cats_viewed[$category_id] = true;

			set_session("session_products_cats_viewed", $products_cats_viewed);

		}

	} elseif (strlen($manf)) {

		$sql = "SELECT manufacturer_name, friendly_url FROM " . $table_prefix . "manufacturers WHERE manufacturer_id=" . $db->tosql($manf, INTEGER);

		$db->query($sql);

		if ($db->next_record()) {

			$manufacturer_name = $db->f("manufacturer_name");

			$manf_friendly_url = $db->f("friendly_url");

			if (!$page_friendly_url && $manf_friendly_url) {

				$page_friendly_url = $manf_friendly_url;

				$page_friendly_params[] = "manf";

				friendly_url_redirect($page_friendly_url, $page_friendly_params);

			}



			$current_category  = $manufacturer_name;

			$list_template     = "block_products_list.html";

			$auto_meta_title = $current_category; 

		}

	} else {

		$category_path = "0";

		$current_category = PRODUCTS_TITLE;

		$list_template    = "block_products_list.html";

		$auto_meta_title = $current_category; 

	}

	//Customization by Vital - canonical URL
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($page_friendly_url) {
		$canonical_url = $page_friendly_url.$friendly_extension;
	}
	else {
		$canonical_url ="wall-stencils.html";
	}
	//END customization

	// check individual page layout settings 

	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);

	include_once("./includes/page_layout.php");



?>
