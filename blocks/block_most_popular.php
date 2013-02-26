<?php

	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/table_view_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/filter_functions.php");
	include_once("./includes/previews_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	// in case block was added on different than products page check if all vars was set
	if (!isset($current_category)) { $current_category = PRODUCTS_TITLE; }
	if (!isset($show_sub_products)) { $show_sub_products = false; }

	// clear all block vars
	$t->set_var("products_sortings", "");
	$t->set_var("category_items", "");
	$t->set_var("items_category_name", "");
	$t->set_var("items_category_desc", "");
	$t->set_var("items_rows", "");
	$t->set_var("items_cols", "");

	$shopping_cart = get_session("shopping_cart");
	$records_per_page = 36;
	$columns = 3;
	$confirm_add  = get_setting_value($settings, "confirm_add", 1);
	
	$show_all = get_param("show_all");

	$html_template = get_setting_value($block, "html_template", "block_products_list_add.html"); 
	
	$hide_add_column = "hide_add_list";
	$options_type = "list";
	$shop_hide_add_button = get_setting_value($settings, "hide_add_list", 0);
	$shop_hide_view_list = get_setting_value($settings, "hide_view_list", 0);
	$shop_hide_checkout_list = get_setting_value($settings, "hide_checkout_list", 0);
	$shop_hide_wishlist_list = get_setting_value($settings, "hide_wishlist_list", 0);
	$show_item_code = get_setting_value($settings, "item_code_list", 0);
	$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_list", 0);
	$quantity_control = get_setting_value($settings, "quantity_control_list", "");
	$stock_level_list = get_setting_value($settings, "stock_level_list", 0);

	$t->set_file("block_body",      $html_template);
	$t->set_var("items_cols",       "");
	$t->set_var("items_rows",       "");
	$t->set_var("PRODUCT_OUT_STOCK_MSG", htmlspecialchars(PRODUCT_OUT_STOCK_MSG));
	$t->set_var("out_stock_alert",       str_replace("'", "\\'", htmlspecialchars(PRODUCT_OUT_STOCK_MSG)));
	$t->set_var("confirm_add", $confirm_add);

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_setting_value($user_info, "discount_type", "");
	$discount_amount = get_setting_value($user_info, "discount_amount", "");

	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$display_products = get_setting_value($settings, "display_products", 0);
	$php_in_short_desc = get_setting_value($settings, "php_in_products_short_desc", 0);
	$php_in_features = get_setting_value($settings, "php_in_products_features", 0);

	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$points_price_list = get_setting_value($settings, "points_price_list", 0);
	$reward_points_list = get_setting_value($settings, "reward_points_list", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);

	// credit settings
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
	$reward_credits_list = get_setting_value($settings, "reward_credits_list", 0);
	
	// new product settings	
	$new_product_enable = get_setting_value($settings, "new_product_enable", 0);	
	$new_product_order  = get_setting_value($settings, "new_product_order", 0);	
	
	// get products reviews settings
	$reviews_settings = get_settings("products_reviews");
	$reviews_allowed_view = get_setting_value($reviews_settings, "allowed_view", 0);
	$reviews_allowed_post = get_setting_value($reviews_settings, "allowed_post", 0);
	
	$all_products_page = get_setting_value($vars, "all_products_page", 0);

	$product_params = prepare_product_params();

	$user_id = get_session("session_user_id");
	$user_type_id = get_session("session_user_type_id");
	$price_type = get_session("session_price_type");
	if ($price_type == 1) {
		$price_field = "trade_price";
		$sales_field = "trade_sales";
		$properties_field = "trade_properties_price";
	} else {
		$price_field = "price";
		$sales_field = "sales_price";
		$properties_field = "properties_price";
	}

	$watermark = false;
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$price_matrix_list = get_setting_value($settings, "price_matrix_list", 0);
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$image_field = "small_image";
	$image_field_alt = "small_image_alt";
	$watermark = get_setting_value($settings, "watermark_small_image", 0);
	$image_type_name = "small";

	srand((double) microtime() * 1000000);
	$random_value = rand();
	$current_ts = va_timestamp();

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$products_page = $page_friendly_url . $friendly_extension;
	} else {
		$products_page = get_custom_friendly_url($script_name);
	}
	$products_form_url = $script_name;
	$t->set_var("products_href", $products_page);
	$t->set_var("products_form_url", $products_form_url);
	$t->set_var("product_details_href", get_custom_friendly_url("product_details.php"));
	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("reviews_href", get_custom_friendly_url("reviews.php"));
	$t->set_var("compare_href", get_custom_friendly_url("compare.php"));
	$t->set_var("cl", $currency["left"]);
	$t->set_var("cr", $currency["right"]);
	$t->set_var("category_id", "0");
	$t->set_var("tax_prices_type", $tax_prices_type);

	$pass_parameters = array();

	$t->set_var("current_category_name", $current_category);

	$pr_where = ""; $pr_brackets = ""; $pr_join = "";
	filter_sqls($pr_brackets, $pr_join, $pr_where);
	
	$sql_params = array();
	$sql_params["brackets"] = $pr_brackets . "((";		
	$sql_params["join"]     = " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id) ";		
	$sql_params["join"] .= ")";
	$sql_params["join"] .= $pr_join;

	$sql_where = "";
	
	if (strlen($sql_where)) $sql_where .= " AND ";
	//$sql_where .= " i.is_special_offer=1"; 1021
	$sql_where .= " ic.category_id=1024";
	
	if (strlen($sql_where) && strlen($pr_where))
		$sql_where .= " AND ";
	$sql_where .= $pr_where;
	$sql_params["where"] = $sql_where;
	$sql_params["distinct"] = " i.item_id";
	
	$total_records = VA_Products::count($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
	$sql_params["distinct"] = "";

	$details_parameters = $pass_parameters; // use all parameters for details page
	if ($friendly_urls && $page_friendly_url) {
		for ($fp = 0; $fp < sizeof($page_friendly_params); $fp++) {
			unset($pass_parameters[$page_friendly_params[$fp]]);
		}
	}

	$s = new VA_Sorter($settings["templates_dir"], "sorter_img.html", $products_page, "sort", "", $pass_parameters);
	
	$s->order_by = " ORDER BY ic.item_order ";	// ORDER FIX order by items_categories item_order instead of item item_order (i.item_order)


	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $products_page);

	$products_nav_type = get_setting_value($vars, "products_nav_type", 1);
	$products_nav_pages = get_setting_value($vars, "products_nav_pages", 5);
	$products_nav_first_last = get_setting_value($vars, "products_nav_first_last", 0);
	$products_nav_prev_next = get_setting_value($vars, "products_nav_prev_next", 1);
	$inactive_links = false;

	//Customization by Vital
	$t->set_var("all_products_style", "display: none;");
	//END customization
	if($show_all){
		$records_per_page = $total_records;
	}
	$n->set_parameters($products_nav_first_last, $products_nav_prev_next, $inactive_links);
	$page_number = $n->set_navigator("navigator", "pn_pr", $products_nav_type, $products_nav_pages, $records_per_page, $total_records, false, $pass_parameters);
	$total_pages = ceil($total_records / $records_per_page);

	// generate page link with query parameters
	$query_string = get_query_string($pass_parameters, "", "", false);
	$rp  = $products_page;
	$rp	.= $query_string;
	$cart_link  = $rp;
	$cart_link .= strlen($query_string) ? "&" : "?";
	$cart_link .= "rnd=" . $random_value . "&";

	// set hidden parameter with category_id parameter
	//$hidden_parameters = $pass_parameters;
	//$hidden_parameters["category_id"] = $category_id;
	//get_query_string($hidden_parameters, "", "", true);

	// remove page and sorting parameters from url
	$details_query = get_query_string($details_parameters, array("pn_pr", "sort_ord", "sort_dir"), "", false);
	$product_link  = get_custom_friendly_url("product_details.php") . $details_query;
	$product_link .= strlen($details_query) ? "&" : "?";
	$product_link .= "item_id=";
	$reviews_link  = get_custom_friendly_url("reviews.php") . $details_query;
	$reviews_link .= strlen($details_query) ? "&" : "?";
	$reviews_link .= "item_id=";

	$t->set_var("rnd", $random_value);
	$t->set_var("rp_url", urlencode($rp));
	$t->set_var("rp", htmlspecialchars($rp));
	$t->set_var("total_records", $total_records);

	if ($total_records)	{

		$order_columns = $s->order_columns;

		if ($order_columns) { 
			$group_by = $order_columns; 
		} else {
			if ($db_type == "postgre") {
				$group_by = "i.item_id, i.is_sales, i.sales_price, i.properties_price, i.price";
			} else {
				$group_by = "i.item_id";
			}
		}
		$sql_params["select"] = " i.item_id ";
		$sql_params["group"] = $group_by;
		$sql_params["order"] = $s->order_by;

		if (preg_match("/m\.manufacturer_name/", $s->order_by)) {
			// join manufacturer table to order by manufacturer_name
			$sql_params["brackets"] .= "(";		
			$sql_params["join"] .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
		}

		$ids = VA_Products::data($sql_params, VIEW_CATEGORIES_ITEMS_PERM, $records_per_page, $page_number);
		$all_list_products_ids = VA_Products::data($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
		$list_products_ids = array();
		for($id = 0; $id < sizeof($all_list_products_ids); $id++) {
			$list_products_ids[] = $all_list_products_ids[$id]["item_id"];
		}
		$allowed_list_products_ids = VA_Products::find_all_ids("i.item_id IN (" . $db->tosql($list_products_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
		$products_ids = array();
		for($id = 0; $id < sizeof($list_products_ids); $id++) {
			if(in_array($list_products_ids[$id], $allowed_list_products_ids)){
				$products_ids[] = $list_products_ids[$id];
			}
		}


		$items_where = ""; $items_ids = array(); 
		for($id = 0; $id < sizeof($ids); $id++) {
			$items_ids[] = $ids[$id]["item_id"];
		}

		$table_columns = array();

		$allowed_items_ids = VA_Products::find_all_ids("i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
		
		//Customization by Vital - second image mouseover
		$mouseover_images = array();
		$sql  = " SELECT item_id, image_small FROM ".$table_prefix."items_images WHERE item_id IN (" . $db->tosql($allowed_items_ids, INTEGERS_LIST).") ORDER BY item_id, image_title ";
		$db->query($sql);
		while ($db->next_record()) {
			$mouseover_images[$db->f("item_id")][] = $db->f("image_small");
		}
		
		//END customization

		$items_categories = array();
				
		$sql  = " SELECT i.item_id, i.item_type_id, i.item_code, i.item_name, i.a_title, i.friendly_url, i.short_description, i.features, i.is_compared, ";
		$sql .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, ";
		$sql .= " i.buying_price, i." . $price_field . ", i.is_price_edit, i." . $sales_field . ", i.discount_percent, ";
		$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
		$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
		$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
		$sql .= " i.tax_id, i.tax_free, i.weight, i.buy_link, i.total_views, i.votes, i.points, i.is_sales, ";
		$sql .= " i.manufacturer_code, m.manufacturer_name, m.affiliate_code, ";
		$sql .= " i.issue_date, i.stock_level, i.use_stock_level, i.disable_out_of_stock, i.min_quantity, i.max_quantity, quantity_increment, ";
		$sql .= " i.hide_out_of_stock, i." . $hide_add_column . ", ";
		$sql .= " st_in.shipping_time_desc AS in_stock_message, st_out.shipping_time_desc AS out_stock_message ";
		// new product db
		if ($new_product_enable) {
			switch ($new_product_order) {
				case 0:
					$sql .= ", i.issue_date AS new_product_date ";
				break;
				case 1:
					$sql .= ", i.date_added AS new_product_date ";
				break;
				case 2:
					$sql .= ", i.date_modified AS new_product_date ";
				break;
			}		
		}

		$sql .= " FROM (((((";	// ORDER FIX
		$sql .= $table_prefix . "items i ";
		$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "items_categories ic ON (i.item_id=ic.item_id AND ic.category_id=1024) )";	// ORDER FIX
 		$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
		if ($items_where) {
			$sql .= " WHERE (" . $items_where . ") ";
		} else {
			$sql .= " WHERE i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
		}
		$sql .= $s->order_by;

		$t->set_var("category_id", "");
		$db->query($sql);
		if ($db->next_record())
		{
			$last_category_id = $db->f("category_id");
			$last_category_name = $db->f("category_name");
			$t->set_var("item_column", (100 / $columns) . "%");
			$t->set_var("total_columns", $columns);
			$t->set_var("forms", "");
			$item_number = 0;
			
			// item previews 
			$previews = new VA_Previews();
			$previews->preview_type     = array(1,2);
			$previews->preview_position = 3;
			do
			{
				$item_number++;
				$item_id = $db->f("item_id");
				$item_category_id = $db->f("category_id");
				$item_category_name = get_translation($db->f("category_name"));
				$category_short_description = trim(get_translation($db->f("category_short_description")));
				$category_full_description = trim(get_translation($db->f("category_full_description")));
				$item_category_desc = "";
				$item_category_desc = $category_short_description;


				if (strval($item_category_name) == "") {
					$item_category_name = PRODUCTS_TITLE;
				}

				$item_type_id = $db->f("item_type_id");
				$item_code = $db->f("item_code");
				$form_id = $item_id;
				$product_params["form_id"] = $form_id;
				$item_name = get_translation($db->f("item_name"));
				$product_params["item_name"] = strip_tags($item_name);
				$a_title = get_translation($db->f("a_title"));
				$highlights = get_translation($db->f("features"));
				if ($php_in_features) {
					eval_php_code($highlights);
				}
				$friendly_url = $db->f("friendly_url");
				$is_compared = $db->f("is_compared");
				$manufacturer_code = $db->f("manufacturer_code");
				$manufacturer_name = $db->f("manufacturer_name");
				$issue_date_ts = 0;
				$issue_date = $db->f("issue_date", DATETIME);
				if (is_array($issue_date)) {
					$issue_date_ts = va_timestamp($issue_date);
				}

				$price = $db->f($price_field);
				$is_price_edit = $db->f("is_price_edit");
				$is_sales = $db->f("is_sales");
				$sales_price = $db->f($sales_field);
				$min_quantity = $db->f("min_quantity");
				$max_quantity = $db->f("max_quantity");
				$quantity_increment = $db->f("quantity_increment");
				
				// special prices
				$user_price        = false; 
				$user_price_action = 0;
				$initial_quantity = ($min_quantity) ? $min_quantity : 1;
				$q_prices   = get_quantity_price($item_id, 1);
				if ($q_prices) {
					$user_price        = $q_prices [0];
					$user_price_action = $q_prices [2];
				}
				
				$buying_price = $db->f("buying_price");					
				// points data
				$is_points_price = $db->f("is_points_price");
				$points_price = $db->f("points_price");
				$reward_type = $db->f("reward_type");
				$reward_amount = $db->f("reward_amount");
				$credit_reward_type = $db->f("credit_reward_type");
				$credit_reward_amount = $db->f("credit_reward_amount");
				if (!strlen($reward_type)) {
					$reward_type = $db->f("type_bonus_reward");
					$reward_amount = $db->f("type_bonus_amount");
				}
				if (!strlen($credit_reward_type)) {
					$credit_reward_type = $db->f("type_credit_reward");
					$credit_reward_amount = $db->f("type_credit_amount");
				}
				if (!strlen($is_points_price)) {
					$is_points_price = $points_prices;
				}

				$weight = $db->f("weight");
				$total_views = $db->f("total_views");
				$tax_id = $db->f("tax_id");
				$tax_free = $db->f("tax_free");
				if ($user_tax_free) { $tax_free = $user_tax_free; }
				$stock_level = $db->f("stock_level");
				$use_stock_level = $db->f("use_stock_level");
				$disable_out_of_stock = $db->f("disable_out_of_stock");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$hide_add_button = $db->f($hide_add_column);
				$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));

				if ($new_product_enable) {
					$new_product_date = $db->f("new_product_date");
					$is_new_product   = is_new_product ($new_product_date);
				} else {
					$is_new_product = false;
				}
				if ($is_new_product) {
					$t->set_var("product_new_class", " newProduct");
					$t->sparse("product_new_image", false);			
				} else {
					$t->set_var("product_new_class", "");
					$t->set_var("product_new_image", "");
				}
				if (!$allowed_items_ids || !in_array($item_id, $allowed_items_ids)) {
					$t->set_var("restricted_class", " restrictedItem");
					$t->sparse("restricted_image", false);
					$hide_add_button = true;
				} else {
					$t->set_var("restricted_class", "");
					$t->set_var("restricted_image", "");
				}
				
				// calcalutate price
				if ($user_price > 0 && ($user_price_action > 0 || !$discount_type)) {
					if ($is_sales) {
						$sales_price = $user_price;
					} else {
						$price = $user_price;
					}
				}

				if ($user_price_action != 1) {
					if ($discount_type == 1 || $discount_type == 3) {
						$price -= round(($price * $discount_amount) / 100, 2);
						$sales_price -= round(($sales_price * $discount_amount) / 100, 2);
					} elseif ($discount_type == 2) {
						$price -= round($discount_amount, 2);
						$sales_price -= round($discount_amount, 2);
					} elseif ($discount_type == 4) {
						$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
						$sales_price -= round((($sales_price - $buying_price) * $discount_amount) / 100, 2);
					}
				}
				$item_price = calculate_price($price, $is_sales, $sales_price);

				$parse_template = true;
				$data = show_items_properties($form_id, $item_id, $item_type_id, $item_price, $tax_id, $tax_free, $options_type, $product_params, $parse_template, $price_matrix_list);
				$is_properties  = $data["params"]["is_any"];
				$properties_ids = $data["params"]["ids"];
				$selected_price = $data["params"]["price"];
				$components_price = $data["params"]["components_price"];
				$components_tax_price = $data["params"]["components_tax_price"];
				$components_points_price = $data["params"]["components_points_price"];
				$components_reward_points = $data["params"]["components_reward_points"];
				$components_reward_credits = $data["params"]["components_reward_credits"];

				$t->set_var("item_id", $item_id);
				if ($friendly_urls && strlen($friendly_url)) {
					//$t->set_var("product_details_url", htmlspecialchars($friendly_url.$friendly_extension . $details_query));
					$t->set_var("product_details_url", htmlspecialchars($friendly_url.$friendly_extension));
				} else {
					$t->set_var("product_details_url", htmlspecialchars($product_link.$item_id));
				}
				$t->set_var("reviews_url", htmlspecialchars($reviews_link.$item_id));
				$t->set_var("found_in_category", "");
				$t->set_var("form_id", $form_id);
				$t->set_var("item_name", $item_name);
				$t->set_var("a_title", htmlspecialchars($a_title));
				$t->set_var("highlights", $highlights);
				$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
				$t->set_var("manufacturer_name", htmlspecialchars($manufacturer_name));
				$t->set_var("total_views", $total_views);
				
				$t->set_var("tax_price", "");
				$t->set_var("tax_sales", "");
				// show item code
				if ($show_item_code && $item_code) {
					$t->set_var("item_code", htmlspecialchars($item_code));
					$t->sparse("item_code_block", false);
				} else {
					$t->set_var("item_code_block", "");
				}
				// show manufacturer code
				if ($show_manufacturer_code && $manufacturer_code) {
					$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
					$t->sparse("manufacturer_code_block", false);
				} else {
					$t->set_var("manufacturer_code_block", "");
					$t->set_var("product_code", "");
				}

				$t->set_var("item_added", "");
				$t->set_var("sc_errors", "");
				if ($item_id == $sc_item_id) {
					if ($sc_errors) {
						$t->set_var("errors_list", $sc_errors);
						$t->parse("sc_errors", false);
					} elseif ($item_added) {
						$cart = get_param("cart");
						if ($cart == "WISHLIST") {
							$added_message = str_replace("{product_name}", $item_name, "{product_name} was added to your Wishlist.");
						} else {
							$added_message = str_replace("{product_name}", $item_name, ADDED_PRODUCT_MSG);
						}
						$t->set_var("added_message", $added_message);
						$t->parse("item_added", false);
					}
				}

				if (!$use_stock_level || $stock_level > 0) {
					$shipping_time_desc = $db->f("in_stock_message");
				} else {
					$shipping_time_desc = $db->f("out_stock_message");
				}
				if (strlen($shipping_time_desc)) {
					$t->set_var("shipping_time_desc", get_translation($shipping_time_desc));
					$t->sparse("availability", false);
				} else {
					$t->set_var("availability", "");
				}
				if ($stock_level_list && $use_stock_level) {
					$t->set_var("stock_level", $stock_level);
					$t->sparse("stock_level_block", false);
				} else {
					$t->set_var("stock_level_block", "");
				}

				$small_image = $db->f($image_field);
				$small_image_alt = get_translation($db->f($image_field_alt));
				if (!strlen($small_image)) {
					$image_exists = false;
					$small_image = $product_no_image;
				} elseif (!image_exists($small_image)) {
					$image_exists = false;
					$small_image = $product_no_image;
				} else {
					$image_exists = true;
				}
				if (strlen($small_image)) {
					if (preg_match("/^http(s)?:\/\//", $small_image)) {
						$image_size = "";
					} else {
						$image_size = @getimagesize($small_image);
						if ($image_exists && ($watermark || $restrict_products_images)) {
							$small_image = "image_show.php?item_id=".$item_id."&type=".$image_type_name."&vc=".md5($small_image);
						}
					}
					if (!strlen($small_image_alt)) {
						$small_image_alt = $item_name;
					}
					$t->set_var("alt", htmlspecialchars($small_image_alt));
					$t->set_var("src", htmlspecialchars($small_image));
					//Customization by Vital - second image mouseover
					$images = $mouseover_images[$item_id];
					$mouseover_image = ( isset($images[0]) && $images[0] != $small_image ) ? $images[0] : ( isset($images[1]) ? $images[1] : $small_image );
					
					$t->set_var("src2", htmlspecialchars($mouseover_image));
					//END customization
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
					$t->parse("small_image", false);
				} else {
					$t->set_var("small_image", "");
				}

				$short_description = get_translation($db->f("short_description"));
				if ($php_in_short_desc) {
					eval_php_code($short_description);
				}

				$t->set_var("short_description", $short_description);
				$t->sparse("description", false);

				if ($weight > 0) {
					if (strpos ($weight, ".") !== false) {
						while (substr($weight, strlen($weight) - 1) == "0")
							$weight = substr($weight, 0, strlen($weight) - 1);
					}
					if (substr($weight, strlen($weight) - 1) == ".")
						$weight = substr($weight, 0, strlen($weight) - 1);
					$t->set_var("weight", $weight . " " . $weight_measure);
					$t->global_parse("weight_block", false, false, true);
				}

				if ($is_compared) {
					$t->global_parse("compare", false, false, true);
					$t->parse("forms", true);
				} else {
					$t->set_var("compare", "");
				}
				
				// show products previews
				$previews->item_id = $item_id;
				$previews->showAll("product_previews");

				// show points price
				if ($points_system && $points_price_list) {
					if ($points_price <= 0) {
						$points_price = $item_price * $points_conversion_rate;
					}
					$points_price += $components_points_price;
					$selected_points_price = $selected_price * $points_conversion_rate;
					$product_params["base_points_price"] = $points_price;
					if ($is_points_price) {
						$t->set_var("points_rate", $points_conversion_rate);
						$t->set_var("points_decimals", $points_decimals);
						$t->set_var("points_price", number_format($points_price + $selected_points_price, $points_decimals));
						$t->sparse("points_price_block", false);
					} else {
						$t->set_var("points_price_block", "");
					}
				}

				// show reward points
				if ($points_system && $reward_points_list) {
					$reward_points = calculate_reward_points($reward_type, $reward_amount, $item_price, $buying_price, $points_conversion_rate, $points_decimals);
					$reward_points += $components_reward_points;

					$product_params["base_reward_points"] = $reward_points;
					if ($reward_type) {
						$t->set_var("reward_points", number_format($reward_points, $points_decimals));
						$t->sparse("reward_points_block", false);
					} else {
						$t->set_var("reward_points_block", "");
					}
				}

				// show reward credits
				if ($credit_system && $reward_credits_list && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
					$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $item_price, $buying_price);
					$reward_credits += $components_reward_credits;

					$product_params["base_reward_credits"] = $reward_credits;
					if ($credit_reward_type) {
						$t->set_var("reward_credits", currency_format($reward_credits));
						$t->sparse("reward_credits_block", false);
					} else {
						$t->set_var("reward_credits_block", "");
					}
				}

				$product_params["pe"] = 0;
				if ($display_products != 2 || strlen($user_id))
				{
					set_quantity_control($quantity_limit, $stock_level, $quantity_control, $min_quantity, $max_quantity, $quantity_increment);

					$base_price = calculate_price($price, $is_sales, $sales_price);
					$product_params["base_price"] = $base_price;
					if ($is_price_edit) {
						$product_params["pe"] = 1;
						$t->set_var("price_block_class", "priceBlockEdit");
						if ($price > 0) {
							$control_price = number_format($price, 2);
						} else {
							$control_price = "";
						}

						$t->set_var("price", $control_price);
						$t->set_var("price_control", "<input name=\"price\" type=\"text\" class=\"price\" value=\"" . $control_price . "\">");
						$t->sparse("price_block", false);
						$t->set_var("sales", "");
						$t->set_var("save", "");
					} elseif ($sales_price != $price && $is_sales) {
						$discount_percent = round($db->f("discount_percent"), 0);
						if (!$discount_percent && $price > 0) {
							$discount_percent = round(($price - $sales_price) / ($price / 100), 0);
						}

						$t->set_var("discount_percent", $discount_percent);
						$t->set_var("old_price", " priceBlockOld");	//Customization by Vital
						set_tax_price($item_id, $item_type_id, $price, 1, $sales_price + $selected_price, $tax_id, $tax_free, "price", "sales_price", "tax_sales", true, $components_price, $components_tax_price);

						$t->sparse("price_block", false);
						$t->sparse("sales", false);
						$t->sparse("save", false);
					} else {
						$product_params["pe"] = 0;
						set_tax_price($item_id, $item_type_id, $price + $selected_price, 1, 0, $tax_id, $tax_free, "price", "", "tax_price", true, $components_price, $components_tax_price);

						$t->sparse("price_block", false);
						$t->set_var("sales", "");
						$t->set_var("save", "");
					}
					$t->set_var("old_price", ""); //Customization by Vital

					$buy_link = $db->f("buy_link");
					if ($buy_link) {
						$t->set_var("buy_href", htmlspecialchars($db->f("buy_link") . $db->f("affiliate_code")));
					} elseif ($is_properties || $quantity_control == "LISTBOX" || $quantity_control == "TEXTBOX" || $is_price_edit) {
						$t->set_var("buy_href", "javascript:document.form_" . $form_id . ".submit();");
						$t->set_var("wishlist_href", "javascript:document.form_" . $form_id . ".submit();");
					} else {
						$t->set_var("buy_href", htmlspecialchars($cart_link."cart=ADD&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $item_id));
						$t->set_var("wishlist_href", htmlspecialchars($cart_link."cart=WISHLIST&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $item_id));
					}

					if ($hide_add_button || $shop_hide_add_button) {
						$t->set_var("add_button_disabled", "");
						$t->set_var("add_button", "");
					} else {
						if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
							$t->set_var("add_button", "");
							$t->sparse("add_button_disabled", false);
						} else {
							$t->set_var("add_button_disabled", "");
							if (($use_stock_level && $stock_level < 1) || $issue_date_ts > $current_ts) {
								$t->set_var("ADD_TO_CART_MSG", PRE_ORDER_MSG);
							} else {
								$t->set_var("ADD_TO_CART_MSG", ADD_TO_CART_MSG);
							}
							$t->sparse("add_button", false);
						}
					}
					if ($shop_hide_view_list) {
						$t->set_var("view_button", "");
					} else {
						$t->sparse("view_button", false);
					}
					if ($shop_hide_checkout_list || !is_array($shopping_cart)) {
						$t->set_var("checkout_button", "");
					} else {
						$t->sparse("checkout_button", false);
					}
					if (!$user_id || $buy_link || $shop_hide_wishlist_list) {
						$t->set_var("wishlist_button", "");
					} else {
						$t->sparse("wishlist_button", false);
					}
				}
				set_product_params($product_params);


				if ($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))
					|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id))) {
					$votes = $db->f("votes");
					$points = $db->f("points");

					$rating_float = $votes ? round($points / $votes, 2) : 0;
					$rating_int = round($rating_float, 0);
					if ($rating_int)
					{
						$rating_alt = $rating_float;
						$rating_image = "rating-" . $rating_int;
					}
					else
					{
						$rating_alt = RATE_IT_BUTTON;
						$rating_image = "not-rated";
					}

					$t->set_var("rating_image", $rating_image);
					$t->set_var("rating_alt", $rating_alt);
					$t->sparse("reviews", false);
				} 

				$is_next_record = $db->next_record();
				if ($item_number % $columns == 0 || (!$is_next_record && $item_number < $columns)) {
					$t->set_var("class_item_td", "");
				} else {
					$t->set_var("class_item_td", "vDelimiter");
				}
				$t->parse("items_cols");
				
				if ($is_next_record) {
					$new_category_id = $db->f("category_id");
				} else {
					$new_category_id = "";
				}
				if ($item_number % $columns == 0)
				{
					if ($is_next_record && $item_category_id == $new_category_id) {
						$t->parse("delimiter", false);
					} else {
						$t->set_var("delimiter", "");
					}
					$t->parse("items_rows");
					$t->set_var("items_cols", "");
				}
	
			} while ($is_next_record);
			$t->set_var("delimiter", "");
	
			if ($item_number % $columns != 0) {
				$t->parse("items_rows");
			}
 
			$t->parse("category_items", true);

			if ($total_pages > 1) {
				$t->parse("search_and_navigation", false);
			}

			$t->parse("block_body", false);
			$t->set_var("no_items", "");
		}
	} else {
		$t->set_var("forms", "");
		$t->set_var("items_rows", "");
		$t->parse("no_items", false);
		$t->parse("block_body", false);
	}

	if ($total_records > 0 ) {
		$block_parsed = true;
	}


	// check if we need to parse hidden block for wishlist types
	if ($user_id && !$shop_hide_wishlist_list) {
		include_once("./blocks/block_wishlist_types.php");
	}

?>