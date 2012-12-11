<?php

	$site_map_tree = array();
	
	$sitemap_settings           = get_settings("site_map");
	$site_map_custom_pages      = get_setting_value($sitemap_settings, "site_map_custom_pages");
	$site_map_categories        = get_setting_value($sitemap_settings, "site_map_categories");
	$site_map_items             = get_setting_value($sitemap_settings, "site_map_items");
	$site_map_forum_categories  = get_setting_value($sitemap_settings, "site_map_forum_categories");
	$site_map_forums            = get_setting_value($sitemap_settings, "site_map_forums");		
	$site_map_ad_categories     = get_setting_value($sitemap_settings, "site_map_ad_categories");
	$site_map_ads               = get_setting_value($sitemap_settings, "site_map_ads");
	$site_map_manual_categories = get_setting_value($sitemap_settings, "site_map_manual_categories");
	$site_map_manual_articles   = get_setting_value($sitemap_settings, "site_map_manual_articles");
	$site_map_manuals           = get_setting_value($sitemap_settings, "site_map_manuals");
	
	$friendly_urls      = get_setting_value($settings, "friendly_urls");
	$friendly_extension = get_setting_value($settings, "friendly_extension");
	
	include_once("./messages/" . $language_code . "/manuals_messages.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/articles_functions.php");
	include_once("./includes/forums_functions.php");
	include_once("./includes/ads_functions.php");
	include_once("./includes/manuals_functions.php");
	
	add_custom_pages_to_site_map_tree($site_map_custom_pages);		
	
	add_root_categories_to_site_map_tree("products", PRODUCTS_TITLE, get_custom_friendly_url("products.php"), $site_map_categories, $site_map_items);

	$articles_total_records = array();
	$articles_top_categories =  VA_Articles_Categories::find_all("c.category_id", 
		array("c.category_name", "c.friendly_url"),
		array(
			"where" => " c.parent_category_id=0 ",
			"order" => " ORDER BY c.category_order, c.category_name"
		)
	);
	if ($articles_top_categories) {
		foreach ($articles_top_categories AS $article_top_category_id => $articles_top_category) {		
			$show_categories = get_setting_value($sitemap_settings, "site_map_articles_categories_" . $article_top_category_id);
			$show_items      = get_setting_value($sitemap_settings, "site_map_articles_" . $article_top_category_id);						

			if ($articles_top_category["c.friendly_url"] && $friendly_urls) {
				$category_url =  $articles_top_category["c.friendly_url"] . $friendly_extension;
			} else {
				$category_url = "articles.php?category_id=" . $article_top_category_id;
			}
			add_root_categories_to_site_map_tree("articles_" . $article_top_category_id,  $articles_top_category["c.category_name"], $category_url, $show_categories, $show_items);
		}
	}		
	
	add_root_categories_to_site_map_tree("forums", FORUM_TITLE, get_custom_friendly_url("forums.php"), $site_map_forum_categories, $site_map_forums);
	
	add_root_categories_to_site_map_tree("ads", ADS_TITLE, get_custom_friendly_url("ads.php"), $site_map_ad_categories, $site_map_ads);
	
	add_root_categories_to_site_map_tree("manuals", MANUALS_TITLE, get_custom_friendly_url("manuals.php"), $site_map_manual_categories, $site_map_manuals, $site_map_manual_articles);

	$t->set_file("block_body", "block_site_map.html");
	
	$t->set_var("item", "");
	$t->set_var("items_rows", "");
	$t->set_var("navigator_block", "");

	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $current_page);

	$current_record   = 0;
	$pages_number     = 1;
	$records_per_page = get_setting_value($sitemap_settings, "site_map_records_per_page", "");
	if ($records_per_page) {
		$page_number          = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
		$first_record_on_page = ($page_number - 1) * $records_per_page;
		$last_record_on_page  = $page_number * $records_per_page;			
	} else {
		$first_record_on_page = 0;
		$last_record_on_page  = 0;
	}
				
	if ($site_map_tree) {
		array_walk($site_map_tree, 'show_site_map_tree');
	}
	
	$block_parsed = true;
	$t->parse("block_body", false);

	function add_root_categories_to_site_map_tree($type = "products", $first_title = "", $first_url = "", $show_categories = 0, $show_items = 0, $show_subitems = 0) {
		global $db, $table_prefix, $settings;
		global $site_map_tree, $total_records;
		if (!$show_categories && !$show_items) return;
		
		$site_map_tree[$type] = array(
			SITEMAP_TITLE_INDEX => $first_title,
			SITEMAP_URL_INDEX   => $first_url
		);
		
		$friendly_urls      = get_setting_value($settings, "friendly_urls");
		$friendly_extension = get_setting_value($settings, "friendly_extension");
		
		$items_categories_ids = array();
		$article_top_category_id = 0;
		if ($show_categories) {
			if ($type == "products") {
				$found_categories = VA_Categories::find_all("c.category_id", 
					array("c.category_name", "c.parent_category_id", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name")
				);
				$category_url_prefix = "products.php?category_id=";
			} elseif (strpos($type, "articles_") === 0) {
				$article_top_category_id = (int) substr($type,9);
				$found_categories = VA_Articles_Categories::find_all("c.category_id", 
					array("c.category_name", "c.parent_category_id", "c.friendly_url"),
					array(
						"order" => " ORDER BY c.category_order, c.category_name",
						"where" => " c.category_path LIKE '%" . $article_top_category_id . ",%' "
					)
				);
				$category_url_prefix = "articles.php?category_id=";
			} elseif ($type == "forums") {
				$found_categories = VA_Forum_Categories::find_all("c.category_id", 
					array("c.category_name", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name")
				);
				$category_url_prefix = "forums.php?category_id=";
			} elseif ($type == "ads") {
				$found_categories = VA_Ads_Categories::find_all("c.category_id", 
					array("c.category_name", "c.parent_category_id", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name")
				);
				$category_url_prefix = "ads.php?category_id=";
			} elseif ($type == "manuals") {
				$found_categories = VA_Manuals_Categories::find_all("c.category_id", 
					array("c.category_name", "c.friendly_url"),
					array("order" => " ORDER BY c.category_order, c.category_name")
				);
				$category_url_prefix = "manuals.php?category_id=";
			}
			
			if ($found_categories) {
				foreach ($found_categories AS $cur_category_id => $cur_category){
					$items_categories_ids[] = $cur_category_id;
					$parent_category_id = isset($cur_category["c.parent_category_id"]) ? $cur_category["c.parent_category_id"] : 0;
					$category_name      = $cur_category["c.category_name"];
					if ($cur_category["c.friendly_url"] && $friendly_urls) {
						$category_url = $cur_category["c.friendly_url"] . $friendly_extension;
					} else{
						$category_url = $category_url_prefix . $cur_category_id;
					}
					if ($parent_category_id <= 0 || $parent_category_id == $article_top_category_id) {
						$site_map_tree[$type][SITEMAP_SUBS_INDEX][] = $cur_category_id;
					} else {
						$site_map_tree[$type][$parent_category_id][SITEMAP_SUBS_INDEX][] = $cur_category_id;						
					}
					$site_map_tree[$type][$cur_category_id][SITEMAP_TITLE_INDEX]     = $category_name;
					$site_map_tree[$type][$cur_category_id][SITEMAP_URL_INDEX]       = $category_url;			
				}
			}
		}
		
		$items_ids = array();
		if ($show_items) {
			if ($type == "products") {
				$item_url_prefix    = "product_details.php?item_id=";
				$friendly_url_field = "i.friendly_url";
				$item_id            = "i.item_id";
				$item_name_field    = "i.item_name";
				$category_id_field  = "ic.category_id";
			} elseif (strpos($type, "articles_") === 0) {
				$article_top_category_id = (int) substr($type, 9);	
				$item_url_prefix    = "article.php?article_id=";
				$friendly_url_field = "a.friendly_url";
				$item_id            = "a.article_id";
				$item_name_field    = "a.article_title";
				$category_id_field  = "ac.category_id";
			} elseif ($type == "forums") {
				$item_url_prefix    = "forum.php?forum_id=";
				$friendly_url_field = "fl.friendly_url";
				$item_id            = "fl.forum_id";
				$item_name_field    = "fl.forum_name";
				$category_id_field  = "fl.category_id";
			} elseif ($type == "ads") {
				$item_url_prefix    = "ads_details.php?item_id=";
				$friendly_url_field = "i.friendly_url";
				$item_id            = "i.item_id";
				$item_name_field    = "i.item_title";
				$category_id_field  = "c.category_id";
			} elseif ($type == "manuals") {
				$item_url_prefix    = "manuals_articles.php?manual_id=";
				$friendly_url_field = "ml.friendly_url";
				$item_id            = "ml.manual_id";
				$item_name_field    = "ml.manual_title";
				$category_id_field  = "c.category_id";
			}
			$found_items = array();
			if ($show_categories && $items_categories_ids) {
				if ($type == "forums") {	
					$found_items = VA_Forums::find_all("", 
						array("fl.forum_id", "fl.forum_name", "fl.friendly_url", "fl.category_id"),
						array(
							"where" => " fl.category_id IN (" . $db->tosql($items_categories_ids, INTEGERS_LIST) . ")",
							"order" => " ORDER BY fl.forum_order, fl.forum_name"
						)
					);
				} elseif ($type == "manuals") {	
					$found_items = VA_Manuals::find_all("", 
						array("ml.manual_id", "ml.manual_title", "ml.friendly_url", "c.category_id"),
						array(
							"where" => " c.category_id IN (" . $db->tosql($items_categories_ids, INTEGERS_LIST) . ")",
							"order" => " ORDER BY ml.manual_order, ml.manual_title"
						)
					);
				} else {
					array_unshift($items_categories_ids, 0);
					foreach ($items_categories_ids AS $items_categories_id) {
						if ($type == "products") {
							$sql  = " SELECT * FROM " . $table_prefix . "categories ";	
							$sql .= " WHERE category_id=" . $db->tosql($items_categories_id, INTEGER);
							$db->query($sql);
							if ($db->next_record()) {
								$show_sub_products = $db->f("show_sub_products");
								$category_path = $db->f("category_path") . $items_categories_id . ",";
							}else{
								$show_sub_products = false;
								$category_path = "";
							}
							$sql_params = array();
							$sql_fields = array();
							$sql_params["brackets"] = "((";		
							$sql_params["join"]     = " LEFT JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";		
							if ($show_sub_products && $items_categories_id != 0) {
								$sql_params["join"] .= "LEFT JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id)";
								$sql_params["where"]  = " (ic.category_id = " . $db->tosql($items_categories_id, INTEGER);
								$sql_params["where"] .= " OR c.category_path LIKE '" . $db->tosql($category_path, TEXT, false) . "%')";
								$sql_fields = array("i.item_id", "i.item_name", "i.friendly_url", $db->tosql($items_categories_id, INTEGER)." 'ic.category_id'");
							} else {
								$sql_params["join"] .= ")";
								$sql_params["where"]  = " ic.category_id = " . $db->tosql($items_categories_id, INTEGER);
								$sql_fields = array("i.item_id", "i.item_name", "i.friendly_url", "ic.category_id");
							}
							$sql_params["order"] = " ORDER BY i.item_order, i.item_name";		

							$found_items_tmp = VA_Products::find_all("", 
								array("i.item_id", "i.item_name", "i.friendly_url", "ic.category_id"),
								$sql_params
							);	
							if ($show_sub_products && $items_categories_id != 0) {
								foreach ($found_items_tmp AS $index_tmp => $items_tmp) {
									$found_items_tmp[$index_tmp]["ic.category_id"] = $db->tosql($items_categories_id, INTEGER);
								}
							}
							$found_items = array_merge ($found_items, $found_items_tmp);
						} elseif (strpos($type, "articles_") === 0) {						
							$found_items_tmp = VA_Articles::find_all("", 
								array("a.article_id", "a.article_title", "a.friendly_url", "ac.category_id"),
								array(
									"where" => " ac.category_id=" . $db->tosql($items_categories_id, INTEGER),
									"order" => " ORDER BY a.article_order, a.article_id"
								)
							);	
							$found_items = array_merge ($found_items, $found_items_tmp);
						} elseif ($type == "ads") {
							$found_items_tmp = VA_Ads::find_all("", 
								array("i.item_id", "i.item_title", "i.friendly_url", "c.category_id"),
								array(
									"where" => " c.category_id=" . $db->tosql($items_categories_id, INTEGER),
									"order" => " ORDER BY i.item_order, i.item_title"
								)
							);
							$found_items = array_merge ($found_items, $found_items_tmp);
						}
					}
				}
			} else {
				// dont show categories - only items
				if ($type == "products") {
					$found_items = VA_Products::find_all("", 
						array("i.item_id", "i.item_name", "i.friendly_url"),
						array("order" => " ORDER BY i.item_order, i.item_name")
					);
				} elseif (strpos($type, "articles_") === 0) {
					$found_items = VA_Articles::find_all("", 
						array("a.article_id", "a.article_title", "a.friendly_url"),
						array(
							"where" => " a.status_id IN ( 1, 2 ) AND ( c.category_path LIKE '0," . $db->tosql($article_top_category_id, INTEGER) . ",%' OR c.category_id=" . $db->tosql($article_top_category_id, INTEGER). " )", //Customization by Vital
							"order" => " ORDER BY a.article_order, a.article_id"
						)
					);
				} elseif ($type == "forums") {						
					$found_items = VA_Forums::find_all("", 
						array("fl.forum_id", "fl.forum_name", "fl.friendly_url"),
						array("order" => " ORDER BY fl.forum_order, fl.forum_name")
					);
				} elseif ($type == "ads") {
					$found_items = VA_Ads::find_all("", 
						array("i.item_id", "i.item_title", "i.friendly_url"),
						array("order" => " ORDER BY i.item_order, i.item_title")
					);
				} elseif ($type == "manuals") {
					$found_items = VA_Manuals::find_all("", 
						array("ml.manual_id", "ml.manual_title", "ml.friendly_url"),
						array("order" => " ORDER BY ml.manual_order, ml.manual_title")
					);
				}
			}
				
			if ($found_items) {
				$parent_items = array();
				foreach ($found_items AS $cur_item){
					$cur_item_id = isset($cur_item[$item_id]) ? $cur_item[$item_id] : 0;
					$parent_category_id = isset($cur_item[$category_id_field]) ? $cur_item[$category_id_field] : 0;
					if ($cur_item[$friendly_url_field] && $friendly_urls) {
						$item_url = $cur_item[$friendly_url_field] . $friendly_extension;
					} else {
						$item_url = $item_url_prefix . $cur_item_id;
						if ($parent_category_id) $item_url .= "&category_id=" . $parent_category_id;
					}
					$items_ids[] = $cur_item_id;
					$cur_item_id = "i_" . $cur_item_id;		
					if ($parent_category_id > 0) {
						$site_map_tree[$type][$parent_category_id][SITEMAP_SUBS_INDEX][] = $cur_item_id;
						
					} else {
						$parent_items[] = $cur_item_id;
//						$site_map_tree[$type][SITEMAP_SUBS_INDEX][] = $cur_item_id;
					}
					$site_map_tree[$type][$cur_item_id][SITEMAP_TITLE_INDEX] = $cur_item[$item_name_field];
					$site_map_tree[$type][$cur_item_id][SITEMAP_URL_INDEX]   = $item_url;
				}
				if(sizeof($parent_items)){
					if(isset($site_map_tree[$type][SITEMAP_SUBS_INDEX])){
						$subs = array_merge ($parent_items, $site_map_tree[$type][SITEMAP_SUBS_INDEX]);
					}else{
						$subs = $parent_items;
					}
					$site_map_tree[$type][SITEMAP_SUBS_INDEX] = $subs;
				}
			}
		}
		
		if ($show_subitems && (!$show_items || $items_ids)) {
			if ($type == "manuals") {
				$sql_manual  = " SELECT article_id, article_title, parent_article_id, friendly_url, manual_id";
				$sql_manual .= " FROM " . $table_prefix . "manuals_articles ";
				$sql_manual .= " WHERE allowed_view=1";
				if ($show_items) {
					$sql_manual .= " AND manual_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
				}
				$sql_manual .= " ORDER BY article_order, article_title ";
				$db->query($sql_manual);
				while ($db->next_record()) {
					$article_id         = $db->f("article_id");
					$manual_id          = $show_items ? $db->f("manual_id") : 0;
					$parent_article_id  = $db->f("parent_article_id");
					$friendly_url       = $db->f("friendly_url");
					if ($friendly_url && $friendly_urls) {
						$item_url = $friendly_url . $friendly_extension;
					} else {
						$item_url = "manuals_article_details.php?article_id=" . $article_id;
					}
					$cur_item_id = "a_" .$article_id;
					if ($parent_article_id > 0) {
						$site_map_tree[$type]["a_" . $parent_article_id][SITEMAP_SUBS_INDEX][] = $cur_item_id;
					} elseif ($manual_id > 0) {
						$site_map_tree[$type]["i_" . $manual_id][SITEMAP_SUBS_INDEX][] = $cur_item_id;
					} else {
						$site_map_tree[$type][SITEMAP_SUBS_INDEX][] = $cur_item_id;
					}
					$site_map_tree[$type][$cur_item_id][SITEMAP_TITLE_INDEX] = $db->f("article_title");
					$site_map_tree[$type][$cur_item_id][SITEMAP_URL_INDEX]   = $item_url;
				}
			}
		}
		
		$ic = count($site_map_tree[$type]);
		if ($ic > 3) {
			$total_records += $ic - 2;
		} else {
			//unset($site_map_tree[$type]);
		}
	}
	
	function add_custom_pages_to_site_map_tree($site_map_custom_pages) {
		global $db, $table_prefix, $site_id, $settings;
		global $site_map_tree, $total_records;
		
		if (!$site_map_custom_pages) return;
		
		$user_id      = get_session("session_user_id");
		$user_type_id = get_session("session_user_type_id");
		
		$friendly_urls      = get_setting_value($settings, "friendly_urls");
		$friendly_extension = get_setting_value($settings, "friendly_extension");
		
		$sql  = " SELECT p.page_id, p.page_code, p.page_title, p.page_url, p.friendly_url FROM ";
		if (isset($site_id)) {
			$sql .= "(";
		}
		if (strlen($user_id)) {
			$sql .= "(";
		}
		$sql .= $table_prefix . "pages p ";
		if (isset($site_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "pages_sites s ON (s.page_id=p.page_id AND p.sites_all=0)) ";
		}
		if (strlen($user_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "pages_user_types ut ON (ut.page_id=p.page_id AND p.user_types_all=0)) ";
		}
		$sql .= " WHERE p.is_showing=1 AND p.is_site_map=1 ";
		if (isset($site_id)) {
			$sql .= " AND (p.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
		} else {
			$sql .= " AND p.sites_all=1";
		}
		if (strlen($user_id)) {
			$sql .= " AND (p.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id, INTEGER) . ") ";
		} else {
			$sql .= " AND p.user_types_all=1 ";
		}
		$sql .= " ORDER BY p.page_order, p.page_title ";			
		$db->query($sql);
		while ($db->next_record()) {
			$item_id   = $db->f('page_id');
			$item_name = $db->f('page_title');
			if ($db->f("friendly_url") && $friendly_urls) {
				$item_url = $db->f("friendly_url") . $friendly_extension;
			} elseif ($db->f('page_url')) {
				$item_url = $db->f("page_url");
			} else {
				$item_url = "page.php?page=" . $db->f("page_code");
			}	
			$site_map_tree["custom_page_" . $item_id] = array(
				SITEMAP_TITLE_INDEX => $item_name,
				SITEMAP_URL_INDEX   => $item_url
			);
			$total_records++;
		}
	}
	
	
	function show_site_map_tree($item, $key, $parent_key = -1, $type = '') {
		global $t, $settings, $site_map_tree;
		global $records_per_page, $first_record_on_page, $last_record_on_page, $current_record;
		
		$current_record++;
		if ($records_per_page && $current_record > $last_record_on_page) return;

		if ($parent_key < 0) {
			$type = $key;
		}
		if (!is_array($item)) {
			$key  = $item;
			$item = $site_map_tree[$key];
		}

		$current_item_record = $current_record;
		if (isset($item[SITEMAP_SUBS_INDEX])) {
			foreach ($item[SITEMAP_SUBS_INDEX] AS $sub_item_key) {
				show_site_map_tree($type ? $site_map_tree[$type][$sub_item_key] : $site_map_tree[$sub_item_key], $sub_item_key, $key, $type);
			}
		}		

		if (!$records_per_page || ($current_item_record > $first_record_on_page && $current_item_record <= $last_record_on_page)) {
			$t->set_var("item_url",  $settings["site_url"] . $item[SITEMAP_URL_INDEX]);
			$t->set_var("item_name", get_translation($item[SITEMAP_TITLE_INDEX]));
			$show_item = true;
		} else {
			$t->set_var("item_url",  "");
			$t->set_var("item_name", "");
			$show_item = false;
		}	
		
		$subitems = $t->get_var("subitems_" . $key);
		if ($subitems) {
			$t->set_var("subitems", "<ul>" . $subitems . "</ul>");
		} else {
			$t->set_var("subitems", "");
		}	
		
		if ($subitems || $show_item) {
			if ($parent_key >= 0) {
				$t->parse_to("item", "subitems_" . $parent_key);
			} else {
				$t->parse("item");
			}
		}
	}
	
	function array_merge_with_keys($array1, $array2) {
		foreach ($array2 AS $key => $val) {
			$array1[$key] = $val;
		}
		return $array1;
	}
?>