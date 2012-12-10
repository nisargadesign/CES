<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  site_map.php                                             ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




	@set_time_limit(900);

	include_once("./includes/common.php");

	include_once("./messages/" . $language_code . "/cart_messages.php");

	include_once("./messages/" . $language_code . "/forum_messages.php");

	include_once("./includes/products_functions.php");

	include_once("./includes/shopping_cart.php");

	include_once("./includes/ads_functions.php");

	include_once("./includes/navigator.php");

	

	$cms_page_code = "site_map";

	$script_name   = "site_map.php";

	$current_page  = get_custom_friendly_url("site_map.php");

	$auto_meta_title = SITE_MAP_TITLE;



	include_once("./includes/page_layout.php");



?>