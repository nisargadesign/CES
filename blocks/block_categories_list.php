<?php

	include_once("./includes/products_functions.php");

	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$columns = get_setting_value($vars, "categories_columns", 1);
	$categories_type = get_setting_value($vars, "categories_type");

	$category_id = 0;
	// check category_id parameter only for product pages
	if ($cms_page_code == "products_list" || $cms_page_code == "product_details" 
		|| $cms_page_code == "product_options" || $cms_page_code == "product_reviews") {
		$category_id = get_param("category_id");
		$search_category_id = get_param("search_category_id");
		$search_string = get_param("search_string");
		$is_search = strlen($search_string);
		if ($is_search && $search_category_id) { $category_id = $search_category_id; }
	}


	$item_id = get_param("item_id"); 
	if (!strlen($category_id)) {
		if (strlen($item_id)) {
			$category_id = get_db_value("SELECT category_id FROM " . $table_prefix . "items_categories where item_id=".$db->tosql($item_id, INTEGER));
			//Customization by Vital
			$session_category_id = get_session("category_id");
			if ( $session_category_id && get_db_value("SELECT COUNT(*) FROM " . $table_prefix . "items_categories where item_id=".$db->tosql($item_id, INTEGER)." AND category_id=".$session_category_id) ) {
				$category_id = $session_category_id;
			}
			//END customization
		} else {
			$category_id = "0";
		}
	}

	$category_id = intval($category_id);
	
	$t->set_var("products_href",    get_custom_friendly_url("products.php"));
	$t->set_var("list_href",        get_custom_friendly_url("products.php"));
	$t->set_var("details_href",     get_custom_friendly_url("product_details.php"));
	$t->set_var("top_category_name",PRODUCTS_TITLE);
	$t->set_var("category_rss", "");

	$list_page = get_custom_friendly_url("products.php");
	$list_url = new VA_URL($list_page);

	$categories_image = get_setting_value($vars, "categories_image");
	
	if ($categories_type == 1 || $categories_type == 2) {
		if (file_exists("images/".$settings["style_name"]."/category_image.gif")) {
			$image_tree_top = "images/".$settings["style_name"]."/category_image.gif";
		} else {
			$image_tree_top = "images/category_image.gif";
		}
		$html_template = get_setting_value($block, "html_template", "block_categories_catalog.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("catalog_sub",         "");
		$t->set_var("catalog_sub_more",    "");
		$t->set_var("catalog_rows",        "");
		$t->set_var("catalog_top",         "");
		$t->set_var("catalog_description", "");

		$categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_PERM);
		if (!$categories_ids) return;
		$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id=" . $db->tosql($category_id, INTEGER), VIEW_CATEGORIES_ITEMS_PERM);
				
		if ($categories_type == 2) {
			$sub_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			if (!$sub_categories_ids)
				$categories_type = 1;
		}

		if ($categories_type == 1) {
			$sql  = " SELECT category_id AS top_category_id, category_name AS top_category_name, a_title AS top_a_title, ";
			$sql .= " short_description, friendly_url AS top_friendly_url, ";
			$sql .= " image, image_alt, image_large, image_large_alt ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";	
			$sql .= " ORDER BY category_order, category_name ";
		} else {
			// show categories as catalog
			$allowed_sub_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);						
			$sql  = " SELECT c.category_id AS top_category_id,c.category_name AS top_category_name, ";
			$sql .= " c.friendly_url AS top_friendly_url, c.a_title AS top_a_title, ";
			$sql .= " c.image, c.image_alt, c.image_large, c.image_large_alt, ";
			$sql .= " s.category_id AS sub_category_id,s.category_name AS sub_category_name, ";
			$sql .= " s.friendly_url AS sub_friendly_url, s.a_title AS sub_a_title ";
			$sql .= " FROM (" . $table_prefix . "categories c ";
			$sql .= " LEFT JOIN " . $table_prefix . "categories s ";			
			if ($sub_categories_ids) {
				$sql .= " ON (c.category_id=s.parent_category_id ";			
				$sql .= " AND s.category_id IN (" . $db->tosql($sub_categories_ids, INTEGERS_LIST) . ")))";
			} else {
				$sql .= " ON c.category_id=s.parent_category_id) ";			
			}
			$sql .= " WHERE c.category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ")";
			$sql .= " ORDER BY c.category_order, c.category_name, c.category_id, s.category_order, s.category_name ";
		}
		$db->query($sql);
		if ($db->next_record()) {
			$category_number = 0;
			$is_subcategories = true;
			$shown_sub_categories = get_setting_value($vars, "categories_subs");
			$catalog_top_number = 0;
			$catalog_sub_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");
			do {
				$category_number++;
				$catalog_sub_number++;
				$top_category_id = $db->f("top_category_id");
				$top_category_name  =  get_translation($db->f("top_category_name"));
				$top_a_title =  get_translation($db->f("top_a_title"));
				$top_friendly_url = $db->f("top_friendly_url");
				$sub_category_id = $db->f("sub_category_id");
				$sub_category_name = get_translation($db->f("sub_category_name"));
				$sub_a_title =  get_translation($db->f("sub_a_title"));
				$sub_friendly_url = $db->f("sub_friendly_url");
				if ($categories_image == 2) {
					$category_image = $db->f("image");
					$image_alt = get_translation($db->f("image_alt"));
				} else if ($categories_image == 3) {
					$category_image = $db->f("image_large");
					$image_alt = get_translation($db->f("image_large_alt"));
				} else {
					$category_image =  $image_tree_top;
					$image_alt = get_translation($db->f("image_alt"));
				}
	
				$t->set_var("catalog_top_id", $top_category_id);
				$t->set_var("catalog_top_name", htmlspecialchars($top_category_name));
				$t->set_var("top_a_title", htmlspecialchars($top_a_title));
				if ($categories_type == 2){
					$t->set_var("catalog_sub_id",   $sub_category_id);
					$t->set_var("catalog_sub_name", htmlspecialchars($sub_category_name));
					$t->set_var("sub_a_title", htmlspecialchars($sub_a_title));
				} else {
		  			if (strlen($db->f("short_description"))) {
						$t->set_var("short_description", get_translation($db->f("short_description")));
						$t->parse("catalog_description", false);
					} else {
						$t->set_var("catalog_description", "");
					}
				}
	
				$is_next_record = $db->next_record();
				$is_new_top = ($top_category_id != $db->f("top_category_id"));
	
				if ($categories_type == 2){
					if (intval($shown_sub_categories) >= $catalog_sub_number || $shown_sub_categories == 0)
					{
						if ($sub_category_id && (!$allowed_sub_categories_ids || !in_array($sub_category_id, $allowed_sub_categories_ids))) {
							$t->set_var("restricted_sub_class", " restrictedSubCategory");
							$t->sparse("restricted_sub_image", false);
						} else {
							$t->set_var("restricted_sub_class", "");
							$t->set_var("restricted_sub_image", "");
						}
						if ($friendly_urls && $sub_friendly_url) {
							$list_url->remove_parameter("category_id");
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($sub_friendly_url. $friendly_extension)));
						} else {
							$list_url->add_parameter("category_id", CONSTANT, $sub_category_id);
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
						}
	
						if ($category_id == $sub_category_id) {
							$t->set_var("class_sub_selected", "selectedsubCategory");
						} else {
							$t->set_var("class_sub_selected", "subCategory");
						}
							
						if ($is_next_record && !$is_new_top) {
							$t->parse("catalog_sub_separator", false);
						} else {
							$t->set_var("catalog_sub_separator", "");
						}
						$t->parse("catalog_sub", true);
					} elseif (($shown_sub_categories + 1) == $catalog_sub_number) {
						if ($friendly_urls && $top_friendly_url) {
							$list_url->remove_parameter("category_id");
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($top_friendly_url . $friendly_extension)));
						} else {
							$list_url->add_parameter("category_id", CONSTANT, $top_category_id);
							$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
						}
	
						$t->parse("catalog_sub_more", false);
					}
				}
	
				if ($is_new_top) {
					$catalog_top_number++;
	
					if ($friendly_urls && $top_friendly_url) {
						$list_url->remove_parameter("category_id");
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($top_friendly_url . $friendly_extension)));
					} else {
						$list_url->add_parameter("category_id", CONSTANT, $top_category_id);
						$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
					}
	
					if ($category_image)
					{
						if (preg_match("/^http\:\/\//", $category_image)) {
							$image_size = "";
						} else {
							$image_size = @GetImageSize($category_image);
							if (isset($restrict_categories_images) && $restrict_categories_images) { 
								$category_image = "image_show.php?category_id=".$top_category_id; 
							}
						}
						if (!strlen($image_alt)) { $image_alt = $top_category_name; }
							$t->set_var("alt", htmlspecialchars($image_alt));
							$t->set_var("src", htmlspecialchars($category_image));
						if (is_array($image_size)) {
							$t->set_var("width", "width=\"" . $image_size[0] . "\"");
							$t->set_var("height", "height=\"" . $image_size[1] . "\"");
						} else {
							$t->set_var("width", "");
							$t->set_var("height", "");
						}
						$t->parse("catalog_image", false);
					} else {
						$t->set_var("catalog_image", "");
					}
	
					if (!$allowed_categories_ids || !in_array($top_category_id, $allowed_categories_ids)) {
						$t->set_var("restricted_class", " restrictedCategory");
						$t->sparse("restricted_image", false);
					} else {
						$t->set_var("restricted_class", "");
						$t->set_var("restricted_image", "");
					}
					
					if ($category_id == $top_category_id) {
						$t->set_var("class_top_selected", "selectedtopCategory");
					} else {
						$t->set_var("class_top_selected", "topCategory");
					}						
						
					$t->parse("catalog_top");
					$t->set_var("catalog_sub", "");
					$t->set_var("catalog_sub_more", "");
					if ($catalog_top_number % $columns == 0) {
						$t->parse("catalog_rows");
						$t->set_var("catalog_top", "");
					}
					$catalog_sub_number = 0;
				}
	
			} while ($is_next_record);
	
			if ($catalog_top_number % $columns != 0) {
				$t->parse("catalog_rows");
			}
	
			$block_parsed = true;
			$t->parse("block_body", false);
		}
	} else if ($categories_type == 5) {
		// chained list type
		$is_ajax = get_param("is_ajax");
		$level = get_param("level");
		$pcategory = get_param("pcategory");
		if (!$pcategory) { $pcategory = 0; }

		$categories = array();

		$sql  = " SELECT category_id, category_name FROM " . $table_prefix . "categories ";
		$sql .= " WHERE parent_category_id=" . $db->tosql($pcategory, INTEGER);
		$sql .= " ORDER BY category_order ";
		$db->query($sql);
		if ($db->next_record())	{
			do {
				$category_id = $db->f("category_id");
				$categories[$category_id] = get_translation($db->f("category_name"));
			} while ($db->next_record());
		}
		
		if ($is_ajax) {
			// json_encode for PHP4
			if (sizeof($categories) > 0) {
				echo "{";
				foreach($categories as $category_id => $category_name) {
					echo '"' . $category_id . '":';
					echo '"' . str_replace('"', '\\"', $category_name) . '",';
				}
				echo "}";
			}
			exit;
		} else {
		
			$html_template = get_setting_value($block, "html_template", "block_categories_chained_menu.html"); 
		  $t->set_file("block_body", $html_template);
			$t->set_var("products_href",  get_custom_friendly_url("products.php"));
		
			foreach($categories as $category_id => $category_name) {
				$t->set_var("category_id", $category_id);	
				$t->set_var("category_name", htmlspecialchars($category_name));
				$t->parse("category_option");
			}
  
			$block_parsed = true;
			$t->parse("block_body", false);
		}

	} else {// list type
		$html_template = get_setting_value($block, "html_template", "block_categories_list.html"); 
	  $t->set_file("block_body", $html_template);
		$t->set_var("categories_rows", "");
		$t->set_var("categories",      "");

		$active_category_path = "0";
		$not = false;
		if ($categories_type == 4) { // Tree-type structure
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$active_category_path  = $db->f("category_path");
				$active_category_path .= $category_id;
			}
			$categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($active_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids("c.parent_category_id IN (" . $db->tosql($active_category_path, INTEGERS_LIST) . ")", VIEW_CATEGORIES_ITEMS_PERM);
		} else {
			$not = true;
			$categories_ids         = VA_Categories::find_all_ids(array('not' => 1), VIEW_CATEGORIES_PERM);
			$allowed_categories_ids = VA_Categories::find_all_ids(array('not' => 1), VIEW_CATEGORIES_ITEMS_PERM);
		}

		if (!$not && !$categories_ids) return;
		
		$categories = array();
		$sql  = " SELECT category_id, category_name, a_title, friendly_url, ";
		$sql .= " short_description, image, image_alt, image_large, image_large_alt, parent_category_id ";		
		$sql .= " FROM " . $table_prefix . "categories ";
		if ($not) {
			if ($categories_ids) {
				$sql .= " WHERE category_id NOT IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			}
			$sql .= " ORDER BY parent_category_id, category_order, category_name ";
			$db->RecordsPerPage = 1000;
		} else {
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			$sql .= " ORDER BY category_order, category_name ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$cur_category_id = $db->f("category_id");
			$category_name = get_translation($db->f("category_name"));
			$a_title = get_translation($db->f("a_title"));
			$friendly_url = $db->f("friendly_url");
			$short_description = get_translation($db->f("short_description"));

			$image = $db->f("image");
			$image_alt = get_translation($db->f("image_alt"));
			$parent_category_id = $db->f("parent_category_id");
			$categories[$cur_category_id]["parent_id"] = $parent_category_id;
			$categories[$cur_category_id]["category_name"] = $category_name;
			$categories[$cur_category_id]["a_title"] = $a_title;
			$categories[$cur_category_id]["friendly_url"] = $friendly_url;
			$categories[$cur_category_id]["short_description"] = $short_description;
			$categories[$cur_category_id]["image"] = $image;
			$categories[$cur_category_id]["image_alt"] = $image_alt;
			$categories[$cur_category_id]["image_large"] = $db->f("image_large");
			$categories[$cur_category_id]["image_large_alt"] = get_translation($db->f("image_large_alt"));
			if ($not) {
				if ($allowed_categories_ids && in_array($cur_category_id, $allowed_categories_ids)) {
					$categories[$cur_category_id]["allowed"] = false;
				} else {
					$categories[$cur_category_id]["allowed"] = true;
				}
			} else {
				if (!$allowed_categories_ids || !in_array($cur_category_id, $allowed_categories_ids)) {
					$categories[$cur_category_id]["allowed"] = false;
				} else {
					$categories[$cur_category_id]["allowed"] = true;
				}
			}
			$categories[$parent_category_id]["subs"][] = $cur_category_id;
		}

		if (sizeof($categories) > 0 && isset($categories[0]))
		{
			$category_number = 0;
			$column_width = intval(100 / $columns);
			$t->set_var("column_width", $column_width . "%");

			set_categories(0, 0, $columns, 0, $categories_image);

			if ($category_number % $columns != 0) {
				$t->parse("categories_rows");
			}

			$block_parsed = true;
			$t->parse("block_body", false);
		}
	}


?>