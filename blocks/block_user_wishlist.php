<?php
	if (strlen(get_session("session_user_id"))) {
		$wishlist_user_id = 0;
		if (isset($_COOKIE['wishlist_user_id']) && is_numeric($_COOKIE['wishlist_user_id'])) {
			$wishlist_user_id = $_COOKIE['wishlist_user_id'];
		}
		
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		$product_no_image = get_setting_value($settings, "product_no_image", "");
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
		$image_field = "small_image";
		$image_field_alt = "small_image_alt";
		$watermark = get_setting_value($settings, "watermark_small_image", 0);
		$image_type_name = "small";
	
		$operation = get_param("operation");
		if ($operation == "add") {
			$cart_item_id = get_param("cart_item_id");
	
			// retrieve cart
			$sql  = " SELECT * FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
			$sql .= " AND user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
			$sql .= " ORDER BY cart_item_id ";
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$sc_errors = ""; $sc_message = "";
					$cart_item_id = $db->f("cart_item_id");
					$item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
					$quantity = $db->f("quantity");
					$price = $db->f("price");
	
					// add to cart
					add_to_cart($item_id, $price, $quantity, "db", "ADD", $new_cart_id, $second_page_options, $sc_errors, $sc_message, $cart_item_id, $item_name);
	
				} while ($db->next_record());
			}
			// check if any coupons can be added or removed
			check_coupons();
	
			header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode(get_custom_friendly_url("user_wishlist.php")));
			exit;
		} else if ($operation == "delete") {
			// delete an item
			$cart_item_id = get_param("cart_item_id");
			$sql  = " DELETE FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE (cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
			$sql .= " AND user_id=" . $db->tosql(get_session("session_user_id"), INTEGER).") OR (item_id=(SELECT item_id FROM (SELECT item_id FROM " . $table_prefix . "saved_items WHERE cart_item_id=". $db->tosql($cart_item_id, INTEGER).") AS tmptable ) AND (user_id=".$db->tosql($wishlist_user_id, INTEGER)." OR user_id=".$db->tosql(get_session("session_user_id"), INTEGER)."))";
			$db->query($sql);
			
		}
	
	
		$html_template = get_setting_value($block, "html_template", "block_user_wishlist.html"); 
		$t->set_file("block_body", $html_template);
		$t->set_var("user_wishlist_href", get_custom_friendly_url("user_wishlist.php"));
		$t->set_var("cart_retrieve_href", get_custom_friendly_url("cart_retrieve.php"));
		$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
	
		$s = new VA_Sorter($settings["templates_dir"], "sorter_img.html", get_custom_friendly_url("user_wishlist.php"));
		$s->set_parameters(false, true, true, false);
		$s->set_default_sorting(6, "desc");
		$s->set_sorter(PROD_NAME_MSG, "sorter_item_name", "1", "si.item_name");
		$s->set_sorter(PRICE_MSG, "sorter_price", "2", "si.price");
		$s->set_sorter(QTY_MSG, "sorter_quantity", "3", "si.quantity");
		$s->set_sorter(WISHLIST_BOUGHT_MSG, "sorter_quantity_bought", "4", "si.quantity_bought");
		$s->set_sorter(TYPE_MSG, "sorter_type", "5", "st.type_name");
		$s->set_sorter(CART_SAVED_DATE_COLUMN, "sorter_date", "6", "si.date_added");
	
		$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_wishlist.php"));
	
		// set up variables for navigator
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "saved_items si ";
		$sql .= " WHERE si.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
		$sql .= " AND si.cart_id=0 ";
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
		$records_per_page = 25;
		$pages_number = 5;
	
		$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber = $page_number;
		$sql  = " SELECT si.cart_item_id, si.item_id, si.item_name, si.price, st.type_name, si.quantity, si.quantity_bought, si.date_added, i.friendly_url, i.small_image, i.small_image_alt, i.a_title ";
		$sql .= " FROM ((" . $table_prefix . "saved_items si ";
		$sql .= " LEFT JOIN " . $table_prefix . "saved_types st ON st.type_id=si.type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=si.item_id) ";
		$sql .= " WHERE si.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
		$sql .= " AND si.cart_id=0 ";
		$sql .= $s->order_by;
		$db->query($sql);
		if($db->next_record())
		{
			$t->parse("sorters", false);
			$t->set_var("no_records", "");
			$t->set_var("wishlist_message", "<p>Here is a list of the items you have selected.</p>");
	
			$cart_url = new VA_URL("user_wishlist.php", false);
			$cart_url->add_parameter("cart_item_id", DB, "cart_item_id");
			$cart_url->add_parameter("operation", CONSTANT, "add");
	
			$delete_url = new VA_URL("user_wishlist.php", false);
			$delete_url->add_parameter("cart_item_id", DB, "cart_item_id");
			$delete_url->add_parameter("operation", CONSTANT, "delete");
	
			do
			{
				$cart_item_id = $db->f("cart_item_id");
				$item_id = $db->f("item_id");
				$price = $db->f("price");
				$quantity = $db->f("quantity");
				$quantity_bought = $db->f("quantity_bought");
				$item_name = $db->f("item_name");
				$type_name = $db->f("type_name");
				$friendly_url = $db->f("friendly_url");
				$date_added = $db->f("date_added", DATETIME);
				$a_title = get_translation($db->f("a_title"));
	
				$t->set_var("cart_item_id", $db->f("cart_item_id"));
				$t->set_var("date_added", va_date($datetime_show_format, $date_added));
	
				$t->set_var("item_id", get_translation($item_id));
				$t->set_var("a_title", htmlspecialchars($a_title));
	
				$t->set_var("item_name", get_translation($db->f("item_name")));
				$t->set_var("type_name", get_translation($db->f("type_name")));
				$t->set_var("price", currency_format($price));
				$t->set_var("quantity", $quantity);
				$t->set_var("quantity_bought", $quantity_bought);
	
				$t->set_var("cart_url", $cart_url->get_url());
				$t->set_var("delete_url", $delete_url->get_url());
				if ($friendly_urls && strlen($friendly_url)) {
					$t->set_var("product_details_url", htmlspecialchars($friendly_url.$friendly_extension));
				} else {
					$product_link  = get_custom_friendly_url("product_details.php")."?item_id=".$item_id;
					$t->set_var("product_details_url", htmlspecialchars($product_link));
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
	
				$t->parse("records", true);
			} while($db->next_record());
		}
		else
		{
			$t->set_var("sorters", "");
			$t->set_var("records", "");
			$t->set_var("navigator", "");
			$t->parse("no_records", false);
		}
	
		$block_parsed = true;
		$t->parse("block_body", false);
	}
	else if (isset($_COOKIE['wishlist_user_id']) && is_numeric($_COOKIE['wishlist_user_id'])) {
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		$product_no_image = get_setting_value($settings, "product_no_image", "");
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
		$image_field = "small_image";
		$image_field_alt = "small_image_alt";
		$watermark = get_setting_value($settings, "watermark_small_image", 0);
		$image_type_name = "small";
	
		$operation = get_param("operation");
		if ($operation == "add") {
			$cart_item_id = get_param("cart_item_id");
	
			// retrieve cart
			$sql  = " SELECT * FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($_COOKIE['wishlist_user_id'], INTEGER);
			$sql .= " ORDER BY cart_item_id ";
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$sc_errors = ""; $sc_message = "";
					$cart_item_id = $db->f("cart_item_id");
					$item_id = $db->f("item_id");
					$item_name = $db->f("item_name");
					$quantity = $db->f("quantity");
					$price = $db->f("price");
	
					// add to cart
					add_to_cart($item_id, $price, $quantity, "db", "ADD", $new_cart_id, $second_page_options, $sc_errors, $sc_message, $cart_item_id, $item_name);
	
				} while ($db->next_record());
			}
			// check if any coupons can be added or removed
			check_coupons();
	
			header("Location: " . get_custom_friendly_url("basket.php") . "?rp=" . urlencode(get_custom_friendly_url("user_wishlist.php")));
			exit;
		} else if ($operation == "delete") {
			// delete an item
			$cart_item_id = get_param("cart_item_id");
			$sql  = " DELETE FROM " . $table_prefix . "saved_items ";
			$sql .= " WHERE cart_item_id=" . $db->tosql($cart_item_id, INTEGER);
			$sql .= " AND user_id=" . $db->tosql($_COOKIE['wishlist_user_id'], INTEGER);
			$db->query($sql);
		}
	
	
		$html_template = get_setting_value($block, "html_template", "block_user_wishlist.html"); 
		$t->set_file("block_body", $html_template);
		$t->set_var("user_wishlist_href", get_custom_friendly_url("user_wishlist.php"));
		$t->set_var("cart_retrieve_href", get_custom_friendly_url("cart_retrieve.php"));
		$t->set_var("user_home_href", get_custom_friendly_url("user_home.php"));
		$t->set_var("wishlist_message", "<p>Here is a list of the items you have selected.</p>Your wishlist is saved on this browser on this computer only. If you want to see your wishlist from another computer please <a href=\"./user_login.php?return_page=" . urlencode( $site_url . get_custom_friendly_url('user_wishlist.php') ) . "\">login to save it</a>.<br /><br />");
	
		$s = new VA_Sorter($settings["templates_dir"], "sorter_img.html", get_custom_friendly_url("user_wishlist.php"));
		$s->set_parameters(false, true, true, false);
		$s->set_default_sorting(6, "desc");
		$s->set_sorter(PROD_NAME_MSG, "sorter_item_name", "1", "si.item_name");
		$s->set_sorter(PRICE_MSG, "sorter_price", "2", "si.price");
		$s->set_sorter(QTY_MSG, "sorter_quantity", "3", "si.quantity");
		$s->set_sorter(WISHLIST_BOUGHT_MSG, "sorter_quantity_bought", "4", "si.quantity_bought");
		$s->set_sorter(TYPE_MSG, "sorter_type", "5", "st.type_name");
		$s->set_sorter(CART_SAVED_DATE_COLUMN, "sorter_date", "6", "si.date_added");
	
		$n = new VA_Navigator($settings["templates_dir"], "navigator.html", get_custom_friendly_url("user_wishlist.php"));
	
		// set up variables for navigator
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "saved_items si ";
		$sql .= " WHERE si.user_id=" . $db->tosql($_COOKIE['wishlist_user_id'], INTEGER);
		$sql .= " AND si.cart_id=0 ";
		$db->query($sql);
		$db->next_record();
		$total_records = $db->f(0);
		$records_per_page = 25;
		$pages_number = 5;
	
		$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
		$db->RecordsPerPage = $records_per_page;
		$db->PageNumber = $page_number;
		$sql  = " SELECT si.cart_item_id, si.item_id, si.item_name, si.price, st.type_name, si.quantity, si.quantity_bought, si.date_added, i.friendly_url, i.small_image, i.small_image_alt, i.a_title ";
		$sql .= " FROM ((" . $table_prefix . "saved_items si ";
		$sql .= " LEFT JOIN " . $table_prefix . "saved_types st ON st.type_id=si.type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "items i ON i.item_id=si.item_id) ";
		$sql .= " WHERE si.user_id=" . $db->tosql($_COOKIE['wishlist_user_id'], INTEGER);
		$sql .= " AND si.cart_id=0 ";
		$sql .= $s->order_by;
		$db->query($sql);
		if($db->next_record())
		{
			$t->parse("sorters", false);
			$t->set_var("no_records", "");
			$t->set_var("wishlist_message", "<p>Here is a list of the items you have selected.</p>Your wishlist is saved on this browser on this computer only. If you want to see your wishlist from another computer please <a href=\"./user_login.php?return_page=" . urlencode( $site_url . get_custom_friendly_url('user_wishlist.php') ) . "\">login to save it</a>.<br /><br />");
	
			$cart_url = new VA_URL("user_wishlist.php", false);
			$cart_url->add_parameter("cart_item_id", DB, "cart_item_id");
			$cart_url->add_parameter("operation", CONSTANT, "add");
	
			$delete_url = new VA_URL("user_wishlist.php", false);
			$delete_url->add_parameter("cart_item_id", DB, "cart_item_id");
			$delete_url->add_parameter("operation", CONSTANT, "delete");
	
			do
			{
				$cart_item_id = $db->f("cart_item_id");
				$item_id = $db->f("item_id");
				$price = $db->f("price");
				$quantity = $db->f("quantity");
				$quantity_bought = $db->f("quantity_bought");
				$item_name = $db->f("item_name");
				$type_name = $db->f("type_name");
				$friendly_url = $db->f("friendly_url");
				$date_added = $db->f("date_added", DATETIME);
				$a_title = get_translation($db->f("a_title"));
	
				$t->set_var("cart_item_id", $db->f("cart_item_id"));
				$t->set_var("date_added", va_date($datetime_show_format, $date_added));
	
				$t->set_var("item_id", get_translation($item_id));
				$t->set_var("a_title", htmlspecialchars($a_title));
	
				$t->set_var("item_name", get_translation($db->f("item_name")));
				$t->set_var("type_name", get_translation($db->f("type_name")));
				$t->set_var("price", currency_format($price));
				$t->set_var("quantity", $quantity);
				$t->set_var("quantity_bought", $quantity_bought);
	
				$t->set_var("cart_url", $cart_url->get_url());
				$t->set_var("delete_url", $delete_url->get_url());
				if ($friendly_urls && strlen($friendly_url)) {
					$t->set_var("product_details_url", htmlspecialchars($friendly_url.$friendly_extension));
				} else {
					$product_link  = get_custom_friendly_url("product_details.php")."?item_id=".$item_id;
					$t->set_var("product_details_url", htmlspecialchars($product_link));
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
	
				$t->parse("records", true);
			} while($db->next_record());
		}
		else
		{
			$t->set_var("sorters", "");
			$t->set_var("records", "");
			$t->set_var("navigator", "");
			$t->parse("no_records", false);
			$t->set_var("wishlist_message", "<p>Here is a list of the items you have selected.</p>Your wishlist is saved on this browser on this computer only. If you want to see your wishlist from another computer please <a href=\"./user_login.php?return_page=" . urlencode( $site_url . get_custom_friendly_url('user_wishlist.php') ) . "\">login to save it</a>.<br /><br />");
		}
	
		$block_parsed = true;
		$t->parse("block_body", false);		
	}
	else {
	check_user_security("my_wishlist");
	}
?>
