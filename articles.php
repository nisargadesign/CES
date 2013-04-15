<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  articles.php                                             ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/articles_functions.php");
	include_once("./includes/cms_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$va_version_code = va_version_code();

	if ($va_version_code & 1) {
		include_once("./includes/products_functions.php");
		include_once("./includes/shopping_cart.php");
		$tax_rates = get_tax_rates();
	}

	$cms_page_code = "articles_list";
	$script_name   = "articles.php";
	$current_page  = get_custom_friendly_url("articles.php");

	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}
	$current_category_id = $category_id;
	
	if ($category_id) {
		if (VA_Articles_Categories::check_exists($category_id)) {
			if (!VA_Articles_Categories::check_permissions($category_id, VIEW_CATEGORIES_ITEMS_PERM)) {
				header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
				exit;
			}
		} else {
			echo NO_RECORDS_MSG;
			exit;
		}
	} else {
		header ("Location: " . get_custom_friendly_url("index.php"));
		exit;
	}

	$page_friendly_url = "";
	$page_friendly_params = array("category_id");
	
	
	// retrieve info about current category
	$sql  = " SELECT category_name,friendly_url,short_description, full_description, category_path, parent_category_id, ";
	$sql .= " articles_order_column, articles_order_direction, article_list_fields, ";
	$sql .= " image_small, image_small_alt, image_large, image_large_alt, ";
	$sql .= " meta_title, meta_keywords, meta_description, total_views, ";
	$sql .= " is_rss, rss_on_breadcrumb, is_remote_rss, remote_rss_url, remote_rss_date_updated, remote_rss_ttl, remote_rss_refresh_rate";
	$sql .= " FROM " . $table_prefix . "articles_categories";
	$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$category_info = $db->Record;
		$current_category = get_translation($db->f("category_name"));
		$page_friendly_url = $db->f("friendly_url");
		friendly_url_redirect($page_friendly_url, $page_friendly_params);
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$image_small = $db->f("image_small");
		$image_small_alt = $db->f("image_small_alt");
		$image_large = $db->f("image_large");
		$image_large_alt = $db->f("image_large_alt");
		$parent_category_id = $db->f("parent_category_id");
		$category_path = $db->f("category_path") . $category_id;
		$total_views = $db->f("total_views");
		$is_remote_rss = $db->f("is_remote_rss");
		$remote_rss_url = $db->f("remote_rss_url");
		$remote_rss_date_updated = $db->f("remote_rss_date_updated", DATETIME);
		$remote_rss_refresh_rate = $db->f("remote_rss_refresh_rate");
		$remote_rss_ttl = $db->f("remote_rss_ttl");

		if ($db->f("is_rss") && $db->f("rss_on_breadcrumb")){
			$rss_on_breadcrumb = true;
		} else {
			$rss_on_breadcrumb = false;
		}
		// meta data
		$meta_title = get_translation($db->f("meta_title"));
		$meta_description = get_translation($db->f("meta_description"));
		$meta_keywords = get_translation($db->f("meta_keywords"));

		// check if we need to generate auto meta data 
		if (!strlen($meta_title)) { $auto_meta_title = $current_category; }
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}		
		}
		
		if ($parent_category_id == 0) {
			$top_id   = $category_id;
			$top_name = $current_category;
			$articles_order_column = $db->f("articles_order_column");
			$articles_order_direction = $db->f("articles_order_direction");
			$list_fields = $db->f("article_list_fields");
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT category_name, articles_order_column,articles_order_direction, article_list_fields ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$top_name = get_translation($db->f("category_name"));
				$articles_order_column = $db->f("articles_order_column");
				$articles_order_direction = $db->f("articles_order_direction");
				$list_fields = $db->f("article_list_fields");
			}
		}
				
		// check for remote RSS links
		if ($is_remote_rss == 1) {
			$articles_imported = articles_import_rss($is_remote_rss, $remote_rss_url, $remote_rss_date_updated, $remote_rss_refresh_rate, $remote_rss_ttl);
		}

		// update total views for articles categories
		$articles_cats_viewed = get_session("session_articles_cats_viewed");
		if (!isset($articles_cats_viewed[$category_id])) {
			$sql  = " UPDATE " . $table_prefix . "articles_categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);

			$articles_cats_viewed[$category_id] = true;
			set_session("session_articles_cats_viewed", $articles_cats_viewed);
		}
	}
	
	//Customization by Vital - canonical URL
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	if ($page_friendly_url) $canonical_url = $page_friendly_url.$friendly_extension;
	//END customization

	// check individual page layout settings 
	$cms_ps_id = check_category_layout($cms_page_code, $category_path, $category_id);
	include_once("./includes/page_layout.php");

?>
