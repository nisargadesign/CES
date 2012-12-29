<?php


			
	$t->set_file("block_body", "header.html");


	// HEADER HOME
	
	if ($current_page == "index.php") 
				{
				$t->set_file("block_body", "header_home.html");
				}	

	// LOGOUT BUTTON
		
	$user_type_id = get_session("session_user_type_id");
		
	if ($user_type_id != '') {
		$t->parse("logout_button", false);
		$t->set_var("loginClass", "");
		$t->set_var("logoutClass", "hide");
	}else{
		$t->set_var("loginClass", "hide");
		$t->set_var("logoutClass", "");
	}
	
	$t->set_var("menu", "");

	$request_uri_path = get_request_path();
	$request_uri_base = basename($request_uri_path);
	// set site logo
	$logo_image = get_translation(get_setting_value($settings, "logo_image", "images/tr.gif"));
	$logo_image_alt = get_translation(get_setting_value($settings, "logo_image_alt", HOME_PAGE_TITLE));
	$logo_width = get_setting_value($settings, "logo_image_width", "");
	$logo_height = get_setting_value($settings, "logo_image_height", "");
	$logo_size = "";
	if ($logo_width || $logo_height) {
		if ($logo_width) { $logo_size = "width=\"".$logo_width."\""; }
		if ($logo_height) { $logo_size .= " height=\"".$logo_height."\""; }
	} elseif ($logo_image && !preg_match("/^http\:\/\//", $logo_image)) {
		//$logo_image = $absolute_url . $logo_image;
		$image_size = @GetImageSize($logo_image);
		if (is_array($image_size)) {
			$logo_size = $image_size[3];
		}
	}
	
	$t->set_var("logo_alt", htmlspecialchars($logo_image_alt));
	$t->set_var("logo_src", htmlspecialchars($logo_image));
	$t->set_var("logo_size", $logo_size);


	$active_menu_id = ""; $active_submenu_id = "";
	if (!isset($show_last_active_menu)) { $show_last_active_menu = false; }
	$request_page = get_request_page();
	if (!isset($current_page)) { $current_page = $request_page; }

	// check secondary items
	$sql  = " SELECT hs.* FROM (" . $table_prefix . "header_submenus hs ";
	$sql .= " INNER JOIN " . $table_prefix . "header_links hl ON hs.menu_id=hl.menu_id) ";
	$sql .= " WHERE (hl.layout_id=0 OR hl.layout_id=" . $db->tosql($layout_id, INTEGER) . ") ";
	$sql .= " AND (submenu_page=" . $db->tosql($request_page, TEXT); // check default page link page.php
	if ($current_page != $request_page) {
		$sql .= " OR submenu_page=" . $db->tosql($current_page, TEXT);
	}
	if ($request_uri_path != $request_page) {
		$sql .= " OR submenu_page=" . $db->tosql($request_uri_path, TEXT); // check absolute links /viart/page.php
	}
	$sql .= ")";
	$sql .= " AND hs.match_type>0 ";
	$db->query($sql);
	while ($db->next_record()) {
		$secondary_menu_matched = true;
		$secondary_menu_url = get_custom_friendly_url($db->f("submenu_url"));
		$match_type = $db->f("match_type");
		if ($match_type == 2) {
			$secondary_menu_url = preg_replace("/\#.*$/", "", $secondary_menu_url);
			$secondary_menu_url = preg_replace("/^.*\?/", "", $secondary_menu_url);
			if ($secondary_menu_url) {
				$secondary_menu_params = explode("&", $secondary_menu_url);
				for($s = 0; $s < sizeof($secondary_menu_params); $s++) {
					if (preg_match("/^(.+)=(.+)$/", $secondary_menu_params[$s], $matches)) {
						$param_name = $matches[1];
						$secondary_menu_param_value = $matches[2];
						$request_param_value = get_param($param_name);
						if (strval($secondary_menu_param_value) != strval($request_param_value)) {
							$secondary_menu_matched = false;
						}
					}
				}
			}
		}
		if ($secondary_menu_matched) {
			$submenu_path = $db->f("submenu_path");
			if (preg_match("/^(\d+)/", $submenu_path, $matches)) {
				$active_submenu_id = $matches[1];
				$sql = " SELECT menu_id FROM " . $table_prefix . "header_submenus WHERE submenu_id=" . $db->tosql($active_submenu_id, INTEGER);
				$active_menu_id = get_db_value($sql);
			} else {
				$active_menu_id = $db->f("menu_id");
			}
			break;
		}
	}

	$top_menu_type = get_setting_value($settings, "top_menu_type", 1);
	if (!$settings["layout_id"]) { $settings["layout_id"] = 1; }
	$header_where = get_session("session_user_id") ? " show_logged=1 " : " show_non_logged=1 ";
	$sql  = " SELECT * FROM " . $table_prefix . "header_links ";
	$sql .= " WHERE " . $header_where;
	$sql .= " AND (layout_id=0 OR layout_id=" . $db->tosql($settings["layout_id"], INTEGER) . ") ";
	$sql .= " ORDER BY menu_order ";
	$db->query($sql);
	
	$menus = array();
	while ($db->next_record())
	{
		$menu_id = $db->f("menu_id");
		$menu_url = $db->f("menu_url");
		$menu_page = $db->f("menu_page");
		$menu_friendly_url = get_custom_friendly_url($menu_url);
		$parent_menu_id = $db->f("parent_menu_id");
		$menu_title = get_translation($db->f("menu_title"));
		$menu_image = $db->f("menu_image");
		$menu_image_active = $db->f("menu_image_active");
		$match_type = $db->f("match_type");

		if ($menu_id == $parent_menu_id) {
			$parent_menu_id = 0;
		}
		$menu_basename = basename($menu_friendly_url);
		if ($parent_menu_id == 0 && !$active_menu_id) {
			$url_matched = false;
			if ($match_type > 0) {
				if ($menu_page == $request_page || $menu_page == $current_page || $menu_page == $request_uri_path) {
					$url_matched = true;
				}
				if ($url_matched && $match_type == 2) {
					$menu_request_uri = preg_replace("/\#.*$/", "", $menu_url);
					$menu_request_uri = preg_replace("/^.*\?/", "", $menu_request_uri);
					if ($menu_request_uri) {
						$menu_params = explode("&", $menu_request_uri);
						for($s = 0; $s < sizeof($menu_params); $s++) {
							if (preg_match("/^(.+)=(.+)$/", $menu_params[$s], $matches)) {
								$param_name = $matches[1];
								$menu_param_value = $matches[2];
								$request_param_value = get_param($param_name);
								if (strval($menu_param_value) != strval($request_param_value)) {
									$url_matched = false;
								}
							}
						}
					}
				}
			}
			if ($url_matched) {
				$active_menu_id = $menu_id;
			}
		}

		if ($menu_url == "index.php") {
			$menu_url = $site_url;
		} if (preg_match("/^\//", $menu_url)) {
			$menu_url = preg_replace("/^".preg_quote($site_path, "/")."/i", "", $menu_url);
			$menu_url = $site_url . get_custom_friendly_url($menu_url);
		} else if (!preg_match("/^http\:\/\//", $menu_url) && !preg_match("/^https\:\/\//", $menu_url) && !preg_match("/^javascript\:/", $menu_url)) {
			$menu_url = $site_url . $menu_friendly_url;
		}

		if (strlen($menu_title) || $menu_image || $menu_image_active) {
			$menus[$menu_id]["menu_url"] = $menu_url;
			$menus[$menu_id]["menu_title"] = $menu_title;
			$menus[$menu_id]["menu_target"] = $db->f("menu_target");
			$menus[$menu_id]["menu_image"] = $menu_image;
			$menus[$menu_id]["menu_image_active"] = $menu_image_active;
			$menus[$menu_id]["menu_path"] = $db->f("menu_path");
			$menus[$menu_id]["submenu_style_name"] = $db->f("submenu_style_name");
			$menus[$parent_menu_id]["subs"][] = $menu_id;
		}
	}

	if (!$active_menu_id && $show_last_active_menu) { // if there is no active menu use value from session
		$active_menu_id    = get_session("session_last_menu_id");
		$active_submenu_id = get_session("session_last_submenu_id");
	}
	
	set_menus($menus, 0, 0, $active_menu_id, $top_menu_type);
	

	// parse secondary menu if available
	if ($active_menu_id) {
		$sub_menus = array();
		$sql  = " SELECT * FROM " . $table_prefix . "header_submenus ";
		$sql .= " WHERE menu_id=" . $db->tosql($active_menu_id, INTEGER);
		$sql .= " AND (show_for_user=1 ";
		if (get_session("session_user_id")) {
			$sql .= " OR show_for_user=2 ";
		} else {
			$sql .= " OR show_for_user=3 ";
		}
		$sql .= " ) ";
		$sql .= " ORDER BY submenu_order, submenu_title ";
		$db->query($sql);
		
		while ($db->next_record())
		{
			$submenu_id = $db->f("submenu_id");
			$parent_menu_id = $db->f("parent_submenu_id");
			$submenu_url = get_custom_friendly_url($db->f("submenu_url"));
			$submenu_title = get_translation($db->f("submenu_title"));
			$submenu_image = $db->f("submenu_image");
			$submenu_image_active = $db->f("submenu_image_active");

			if (strlen($submenu_title) || $submenu_image || $submenu_image_active) {
				$sub_menus[$submenu_id]["menu_url"] = $submenu_url;
				$sub_menus[$submenu_id]["menu_page"] = $db->f("submenu_page");
				$sub_menus[$submenu_id]["menu_title"] = $submenu_title;
				$sub_menus[$submenu_id]["menu_target"] = $db->f("submenu_target");
				$sub_menus[$submenu_id]["menu_image"] = $submenu_image;
				$sub_menus[$submenu_id]["menu_image_active"] = $submenu_image_active;
				$sub_menus[$submenu_id]["menu_path"] = $db->f("submenu_path");
				$sub_menus[$submenu_id]["submenu_style_name"] = $db->f("submenu_style_name");
				$sub_menus[$submenu_id]["match_type"] = $db->f("match_type");
				
				$sub_menus[$parent_menu_id]["subs"][] = $submenu_id;
			}
		}
		
		set_session("session_last_menu_id", $active_menu_id);
		$submenu_style_name = "";
		if (isset($menus[$active_menu_id]))
			$submenu_style_name = $menus[$active_menu_id]["submenu_style_name"];
		if (!$submenu_style_name) 
			$submenu_style_name = "secondary";
		set_menus($sub_menus, 0, 0, $active_submenu_id, $top_menu_type, "secondary_", $submenu_style_name);
	}


	$t->set_var("index_href", get_custom_friendly_url("index.php"));
	$t->set_var("products_href", get_custom_friendly_url("products.php"));
	$t->set_var("basket_href", get_custom_friendly_url("basket.php"));
	$t->set_var("user_profile_href", get_custom_friendly_url("user_profile.php"));
	$t->set_var("admin_href", "admin.php");
	$t->set_var("help_href", get_custom_friendly_url("page.php") . "?page=help");
	$t->set_var("about_href", get_custom_friendly_url("page.php") . "?page=about");

	if (!isset($header_title)) { $header_title = ""; }
	$t->set_var("header_title", $header_title);

	$block_parsed = true;
	$t->parse("block_body", false); // clear previous block body content
	$t->set_var("block_body", get_currency_message($t->get_var("block_body"), $currency));

?>