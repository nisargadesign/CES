<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  common_functions.php                                     ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
	function get_query_string($variables, $remove_parameters, $query_string = "", $set_hidden_parameters = false)
	{
		global $t;

		if (is_array($variables))
		{
			$hidden_parameters = "";
			if (!is_array($remove_parameters)) {
				$remove_parameters = array($remove_parameters);
			}
			foreach($variables as $key => $value) {
				if (strlen($value) && !in_array($key, $remove_parameters)) {
					$query_string .= strlen($query_string) ? "&" : "";
					$query_string .= urlencode($key) . "=" . urlencode($value);
					if ($set_hidden_parameters) {
						$hidden_parameters .= "<input type=\"hidden\" name=\"" . htmlspecialchars($key) . "\" value=\"";
						$hidden_parameters .= htmlspecialchars($value) . "\" />";
					}
				}
			}
			if ($set_hidden_parameters) {
				$t->set_var("hidden_parameters", $hidden_parameters);
			}
		}

		if ($query_string) {$query_string = "?" . $query_string; }
		return $query_string;
	}

	function get_transfer_params($remove_parameters = "")
	{
		$pass_parameters = array();
		$available_params = array(
			"search_string", "category_id", "search_category_id", "item_id", "article_id",
			"s_tit", "s_cod", "s_sds", "s_fds", "manf", "user", "lprice", "hprice",
			"lweight", "hweight", "page", "sw", "sf", "forum_id", "thread_id",
			"sort_ord", "sort_dir", "filter", "country", "state", "zip",
			"pn_pr", "pn_ar", "pn_ad", "pn_th", "pn_pr_sp", 
		);

		for ($si = 0; $si < sizeof($available_params); $si++) {
			$param_name  = $available_params[$si];
			$param_value = get_param($param_name);
			if (strlen($param_value)) {
				$pass_parameters[$param_name] = $param_value;
			}
		}

		$pq = get_param("pq");
		$fq = get_param("fq");
		if ($pq > 0) {
			for ($pi = 1; $pi <= $pq; $pi++) {
				$property_name = get_param("pn_" . $pi);
				$property_value = get_param("pv_" . $pi);
				if (strlen($property_name) && strlen($property_value)) {
					$pass_parameters["pq"] = $pq;
					$pass_parameters["pn_" . $pi] = $property_name;
					$pass_parameters["pv_" . $pi] = $property_value;
				}
			}
		}
		if ($fq > 0) {
			for ($fi = 1; $fi <= $fq; $fi++) {
				$feature_name = get_param("fn_" . $fi);
				$feature_value = get_param("fv_" . $fi);
				if (strlen($feature_name) && strlen($feature_value)) {
					$pass_parameters["fq"] = $fq;
					$pass_parameters["fn_" . $fi] = $feature_name;
					$pass_parameters["fv_" . $fi] = $feature_value;
				}
			}
		}
		// check parameters to be removed
		if (is_array($remove_parameters)) {
			for ($rp = 0; $rp < sizeof($remove_parameters); $rp++) {
				$param_name = $remove_parameters[$rp];
				if (isset($pass_parameters[$param_name])) {
					unset($pass_parameters[$param_name]);
				}
			}
		}
		return $pass_parameters;
	}

	function transfer_params($remove_parameters, $set_hidden_parameters = false)
	{
		$pass_parameters = get_transfer_params($remove_parameters);
		return get_query_string($pass_parameters, "", "", $set_hidden_parameters);
	}

	function get_param($param_name, $param_type = 0)
	{
	  global $HTTP_POST_VARS, $HTTP_GET_VARS;

	  $param_value = "";
		if (isset($_POST)) {
		  if (isset($_POST[$param_name]) && $param_type != GET)
	  	  $param_value = $_POST[$param_name];
		  elseif (isset($_GET[$param_name]) && $param_type != POST)
	  	  $param_value = $_GET[$param_name];
		} else {
		  if (isset($HTTP_POST_VARS[$param_name]) && $param_type != GET)
	  	  $param_value = $HTTP_POST_VARS[$param_name];
		  elseif (isset($HTTP_GET_VARS[$param_name]) && $param_type != POST)
	  	  $param_value = $HTTP_GET_VARS[$param_name];
		}

		return strip($param_value);
	}

	function get_cookie($parameter_name)
	{
		global $HTTP_COOKIE_VARS;
		if (isset($_COOKIE)) {
			return isset($_COOKIE[$parameter_name]) ? $_COOKIE[$parameter_name] : "";
		} else {
			return isset($HTTP_COOKIE_VARS[$parameter_name]) ? $HTTP_COOKIE_VARS[$parameter_name] : "";
		}
	}

	function get_session($parameter_name)
	{
		global $session_prefix;
		$parameter_name = $session_prefix . $parameter_name;
		return isset($_SESSION[$parameter_name]) ? $_SESSION[$parameter_name] : "";
	}

	function set_session($parameter_name, $parameter_value)
	{
		global $session_prefix;
		$parameter_name = $session_prefix . $parameter_name;
		$_SESSION[$parameter_name] = $parameter_value;
	}

	function get_options($values, $selected_value)
	{
		$eol = get_eol();
		$options = "";
		if (is_array($values))
		{
			for ($i = 0; $i < sizeof($values); $i++)
			{
				if ($values[$i][0] == $selected_value && strlen($values[$i][0]) == strlen($selected_value)) {
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}
				$options .= "<option " . $selected;
				$options .= " value=\"" . htmlspecialchars($values[$i][0]) . "\">";
				$options .= htmlspecialchars($values[$i][1]) . "</option>". $eol;
			}
		}
		return $options;
	}

	function set_options($values, $value, $block_name, $events = "")
	{
		global $t;
		$t->set_var($block_name, "");
		if (is_array($values))
		{
			for ($i = 0; $i < sizeof($values); $i++)
			{
				$cur_val = $values[$i][0];
				call_event($events, BEFORE_SHOW_VALUE, array("current_value" => $cur_val));
				$checked = ""; $selected = "";
				if (is_array($value)) {
					for ($j = 0; $j < sizeof($value); $j++) {
						if (strval($cur_val) == strval($value[$j])) {
							$checked = "checked=\"checked\""; $selected = "selected=\"selected\"";
							break;
						}
					}
				} elseif (strval($cur_val) == strval($value)) {
					$checked = "checked=\"checked\""; $selected = "selected=\"selected\"";
				}
				$t->set_var($block_name . "_index", ($i + 1));
				$t->set_var($block_name . "_checked", $checked);
				$t->set_var($block_name . "_selected", $selected);
				$t->set_var($block_name . "_value", htmlspecialchars($cur_val));
				$t->set_var($block_name . "_description", htmlspecialchars($values[$i][1]));

				$t->parse($block_name);
				call_event($events, AFTER_SHOW_VALUE, array("current_value" => $cur_val));
			}
		}
	}

	function get_ip()
	{
		return get_var("REMOTE_ADDR");
	}

	function prepare_regexp($regexp)
	{
		$escape_symbols = array("\\","/","^","\$",".","[","]","|","(",")","?","*","+","-","{","}");
		for ($i = 0; $i < sizeof($escape_symbols); $i++) {
			$regexp = str_replace($escape_symbols[$i], "\\" . $escape_symbols[$i], $regexp);
		}
		return $regexp;
	}

	function get_array_value($value_ids, $values, $glue = "")
	{
		$value_desc = "";
		if (is_array($values) && (is_array($value_ids) || strlen($value_ids))) {
			for ($i = 0; $i < sizeof($values); $i++) {
				if (is_array($value_ids)) {
					if (in_array($values[$i][0], $value_ids)) {
						if (strlen($value_desc)) { $value_desc .= $glue; }
						$value_desc .= $values[$i][1];
					}
				} elseif ($values[$i][0] == $value_ids) {
					$value_desc = $values[$i][1];
					break;
				}
			}
		}
		return $value_desc;
	}

	function get_array_id($value_desc, $values)
	{
		$value_id = "";
		if (is_array($values) && strlen($value_desc)) {
			for ($i = 0; $i < sizeof($values); $i++) {
				if ($values[$i][1] == $value_desc) {
					$value_id = $values[$i][0];
					break;
				}
			}
		}
		return $value_id;
	}

	function parse_value(&$value)
	{
		global $t;
		if ($value) {
			if (preg_match("/^\w+$/", $value) && defined($value)) { 
				$value = constant($value); 
			} else if (preg_match_all("/\{(\w+)\}/is", $value, $matches)) {
				for ($m = 0; $m < sizeof($matches[1]); $m++) {
					$tag = $matches[1][$m];
					if (defined($tag)) { 
						$value = str_replace("{".$tag."}", constant($tag), $value);
					} else if (isset($t)) {
						$value = str_replace("{".$tag."}", $t->get_var($tag), $value);
					}
				}
			} 
		}
		return $value;
	}

	function get_db_values($sql, $values_before, $shown_symbols = 0)
	{
		global $db;
		$values = array();

		$db_list = new VA_SQL();
		$db_list->DBType       = $db->DBType;
		$db_list->DBDatabase   = $db->DBDatabase;
		$db_list->DBUser       = $db->DBUser;
		$db_list->DBPassword   = $db->DBPassword;
		$db_list->DBHost       = $db->DBHost;
		$db_list->DBPort       = $db->DBPort;
		$db_list->DBPersistent = $db->DBPersistent;

		$i = 0;
		if (is_array($values_before))
		{
			for ($j = 0; $j < sizeof($values_before); $j++)
			{
				$value_desciption = get_translation($values_before[$j][1]);
				$value_desciption = parse_value($value_desciption);
				if ($shown_symbols > 0 && strlen($value_desciption) > $shown_symbols) {
					$value_desciption = substr($value_desciption, 0, $shown_symbols) . "...";
				}
				$values[$i][0] = $values_before[$j][0];
				$values[$i][1] = $value_desciption;
				$i++;
			}
		}

		$db_list->query($sql);
		if ($db_list->next_record())
		{
			do {
				$value_desciption = get_translation($db_list->f(1));
				$value_desciption = parse_value($value_desciption);
				if ($shown_symbols > 0 && strlen($value_desciption) > $shown_symbols) {
					$value_desciption = substr($value_desciption, 0, $shown_symbols) . "...";
				}
				$values[$i][0] = $db_list->f(0);
				$values[$i][1] = $value_desciption;
				$i++;
			} while ($db_list->next_record());
		}

		return $values;
	}

	function get_setting_value($settings_array, $setting_name, $default_value = "")
	{
		return (is_array($settings_array) && isset($settings_array[$setting_name]) && strlen($settings_array[$setting_name])) ? $settings_array[$setting_name] : $default_value;
	}

	function get_settings($setting_type)
	{
		global $db, $table_prefix, $site_id;

		$settings = array();
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		if (isset($site_id) && $site_id) {
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
			$sql .= " ORDER BY site_id ASC ";
		} else {
			$sql .= " AND site_id=1 ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$settings[$db->f("setting_name")] = $db->f("setting_value");
		}

		return $settings;
	}

	function get_meta_desc($meta_description)
	{
		$meta_description = preg_replace("/<script.*<\\/script>/isU", "", $meta_description); // remove JS from the text
		$meta_description = preg_replace("/[\r\n\t]/", " ", $meta_description); // replace big space symbols
		$meta_description = preg_replace("/\s{2,}/", " ", $meta_description); // leave only one space between words
		$meta_description = trim(strip_tags($meta_description));
		if (strlen($meta_description) > 255) {
			$meta_description = substr($meta_description, 0, 250) . " ...";
		}
		$meta_description = str_replace("\"", "&quot;", $meta_description);
		return $meta_description;
	}

	function get_eol()
	{
		if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
			$eol = "\r\n";
		} elseif (strtoupper(substr(PHP_OS, 0, 3) == 'MAC')) {
			$eol = "\r";
		} else {
			$eol = "\n";
		}
		return $eol;
	}

	function get_email_headers($mail_from, $mail_cc, $mail_bcc, $mail_reply_to, $mail_return_path, $mail_type, $eol = "")
	{
		$mail_headers  = "";

		if (!$eol) {
			$eol = get_eol();
		}

		$mail_headers .= "Date: " . date("r") . $eol; // RFC 2822 formatted date
		if ($mail_from) { $mail_headers .= "From: " . $mail_from . $eol; }
		if ($mail_cc)  {
			$mail_cc = str_replace(";", ",", $mail_cc);
			$mail_headers .= "cc: " . $mail_cc . $eol;
		}
		if ($mail_bcc)  {
			$mail_bcc = str_replace(";", ",", $mail_bcc);
			$mail_headers .= "Bcc: " . $mail_bcc . $eol;
		}
		if ($mail_reply_to) { $mail_headers .= "Reply-To: " . $mail_reply_to . $eol; }
		if ($mail_return_path)  { $mail_headers .= "Return-path: " . $mail_return_path . $eol; }
		$mail_headers .= "MIME-Version: 1.0";
		if (strlen($mail_type)) {
			if ($mail_type) {
				$mail_headers .= $eol . "Content-Type: text/html;" . $eol;
			} else {
				$mail_headers .= $eol . "Content-Type: text/plain;" . $eol;
			}
			$mail_headers .= "\tcharset=\"" . CHARSET . "\"";
		}

		return $mail_headers;
	}

	function email_headers_string($mail_headers, $eol = "")
	{
		$headers_string  = "";

		if (!$eol) {
			$eol = get_eol();
		}
		if (!isset($mail_headers["Date"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$headers_string .= "Date: " . date("r"); // RFC 2822 formatted date
		}
		foreach ($mail_headers as $header_type => $header_value) {
			if ($header_type == "to") {
				$header_type = "To";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "from") {
				$header_type = "From";
			} elseif ($header_type == "cc") {
				$header_type  = "Cc";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "bcc") {
				$header_type  = "Bcc";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "reply_to") {
				$header_type  = "Reply-To";
			} elseif ($header_type == "return_path") {
				$header_type  = "Return-path";
			} elseif ($header_type == "mail_type") {
				if (isset($mail_headers["Content-Type"])) {
					$header_type = ""; $header_value = "";
				} else {
					$header_type  = "Content-Type";
					if ($header_value == 1 || $header_value == "text/html") {
						$header_value = "text/html;" . $eol;
					} else {
						$header_value = "text/plain;" . $eol;
					}
					$header_value .= "\tcharset=\"" . CHARSET . "\"";
				} 
			}
			if ($header_type && strlen($header_value)) {
				if ($headers_string) { $headers_string .= $eol; }
				$headers_string .= $header_type . ": " . $header_value;
			}
		}
		if (!isset($mail_headers["Message-ID"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$server_name = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "localhost";
			$message_id = uniqid(time().mt_rand());
			$headers_string .= "Message-ID: <".$message_id."@".$server_name.">"; // RFC 2822 formatted date
		}
		if (!isset($mail_headers["MIME-Version"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$headers_string .= "MIME-Version: 1.0";
		}

		return $headers_string;
	}

	function strip($value)
	{
		if (get_magic_quotes_gpc() == 0) {
	    	return $value;
		} else {
			return stripslashes($value);
		}
	}

	function call_event($events, $event_name, $additional_parameters = "")
	{
		if (is_array($events) && isset($events[$event_name]) && function_exists($events[$event_name])) {
			if (isset($events[$event_name . "_params"]) && is_array($events[$event_name . "_params"])) {
				$event_parameters = $events[$event_name . "_params"];
			} else {
				$event_parameters = array();
			}
			if (is_array($additional_parameters)) {
				foreach ($additional_parameters as $key => $value) {
					$event_parameters[$key] = $value;
				}
			}
			$event_parameters["event"] = $event_name;
			call_user_func($events[$event_name], $event_parameters);
		}
	}

	function get_db_value($sql)
	{
		global $db;

		$db->query($sql);
		if ($db->next_record()) {
			return $db->f(0);
		} else  {
			return "";
		}
	}

	function get_page_url()
	{
		$server_name = getenv("SERVER_NAME");
		$request_uri = getenv("REQUEST_URI");
	}

	function get_script_name()
	{
		global $current_page;
		if (isset($current_page) && $current_page) {
			$script_name = $current_page;
		} elseif (get_var("PHP_SELF")) {
			$script_name = get_var("PHP_SELF");
		} elseif (get_var("SCRIPT_NAME")) {
			$script_name = get_var("SCRIPT_NAME");
		} elseif (get_var("SCRIPT_FILENAME")) {
			$script_name = get_var("SCRIPT_FILENAME");
		} elseif (get_var("REQUEST_URI")) {
			$script_name = get_var("REQUEST_URI");
		} else {
			$script_name = get_var("SCRIPT_URL");
		}

		return basename($script_name);
	}

	function get_request_page()
	{
		global $current_page;
		$request_page = get_var("REQUEST_URI");
		if (!strlen($request_page)) { $request_page = get_var("URL"); }
		if (!strlen($request_page)) { $request_page = get_var("HTTP_X_REWRITE_URL"); }
		if (!strlen($request_page) 
			&& isset($_SERVER["SERVER_SOFTWARE"]) && preg_match("/IIS/i", $_SERVER["SERVER_SOFTWARE"]) 
			&& isset($_SERVER["QUERY_STRING"]) && preg_match("/^404;/i", $_SERVER["QUERY_STRING"])
		) { 
			// IIS 404 Error
			$request_page = preg_replace("/^404;/", "", $_SERVER["QUERY_STRING"]); 
		}
		$request_page = preg_replace("/\?.*$/", "", $request_page);
		if (!$request_page || substr($request_page, -1) == "/") {
			if (get_var("SCRIPT_URL")) {
				$request_page = get_var("SCRIPT_URL");
			} elseif (isset($current_page) && $current_page) {
				$request_page = $current_page;
			} elseif (get_var("SCRIPT_NAME")) {
				$request_page = get_var("SCRIPT_NAME");
			} elseif (get_var("PHP_SELF")) {
				$request_page = get_var("PHP_SELF");
			}
		}

		return basename($request_page);
	}

	function get_request_uri()
	{
		$server = get_var("SERVER_SOFTWARE");
		$request_uri = get_var("REQUEST_URI");
		if (!strlen($request_uri)) { $request_uri = get_var("URL"); }
		if (!strlen($request_uri)) { $request_uri = get_var("HTTP_X_REWRITE_URL"); }
		if (!strlen($request_uri) 
			&& isset($_SERVER["SERVER_SOFTWARE"]) && preg_match("/IIS/i", $_SERVER["SERVER_SOFTWARE"]) 
			&& isset($_SERVER["QUERY_STRING"]) && preg_match("/^404;/i", $_SERVER["QUERY_STRING"]))
		{ 
			// IIS 404 Error
			$request_uri = preg_replace("/^404;/", "", $_SERVER["QUERY_STRING"]); 
		}

		if (!strlen($request_uri)) {
			$request_uri = get_var("SCRIPT_NAME");
			if (!$request_uri) { $request_uri = get_var("SCRIPT_URL"); }
			if (!$request_uri) { $request_uri = get_var("PHP_SELF"); }
			$query_string = get_var("QUERY_STRING");
			if (strlen($query_string)) {
				$request_uri .= "?" . $query_string;
			}
		}
		return $request_uri;
	}

	function get_request_path($request_uri = "")
	{
		if ($request_uri === "") { $request_uri = get_request_uri(); }
		if (preg_match("/^https?:\\/\\/[^\\/]+([^\\?]+)/i", $request_uri, $match)) {
			$request_uri_path = $match[1];
		} else if (preg_match("/^([^\\?]+)/i", $request_uri, $match)) {
			$request_uri_path = $match[1];
		} else {
			$request_uri_path = "/";
		}
		return $request_uri_path;
	}

	function check_user_session()
	{
		global $is_ssl, $settings;
		if (!strlen(get_session("session_user_id"))) {
			$site_url = get_setting_value($settings, "site_url", "");
			$secure_url = get_setting_value($settings, "secure_url", "");
			$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
			if ($secure_user_login) {
				$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
			} else {
				$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
			}
			if ($is_ssl) {
				$page_site_url = $secure_url;
			} else {
				$page_site_url = $site_url;
			}
			$return_page = get_request_uri();
			if (preg_match("/^https?:\\/\\/[^\\/]+(\\/.*)$/i", $page_site_url, $matches)) {
				$page_path_regexp = prepare_regexp($matches[1]);
				if (preg_match("/^" .$page_path_regexp. "/i", $return_page)) {
					$return_page = $page_site_url . preg_replace("/^" .$page_path_regexp. "/i", "", $return_page);
				} 
			}

			header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=1");
			exit;
		}
	}

	function check_user_security($setting_name = "")
	{
		global $db, $settings, $table_prefix;
		check_user_session();

		if ($setting_name) {
			$sql  = " SELECT setting_value ";
			$sql .= " FROM " . $table_prefix . "user_types_settings ";
			$sql .= " WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
			$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT);
			$allow_access = get_db_value($sql);
			if (!$allow_access) {
				$site_url = get_setting_value($settings, "site_url", "");
				$user_home_url = $site_url . get_custom_friendly_url("user_home.php");
				header ("Location: " . $user_home_url);
				exit;
			}
		}
	}


	function check_black_ip($ip_address = "", $address_action = 1)
	{
		global $db, $table_prefix;

		if (!$ip_address) {
			$ip_address = get_ip();
		}
		$ip_parts = explode(".", $ip_address);
		$where = " WHERE address_action=" . $db->tosql($address_action, INTEGER) . " AND (";
		$ip_where = "";
		for ($i = 0; $i < sizeof($ip_parts); $i++) {
			if ($i) {
				$ip_where .= ".";
				$where .= " OR ";
			}
			$ip_where .= $ip_parts[$i];
			$where .= " ip_address=" . $db->tosql($ip_where, TEXT);
		}
		$where .= ") ";
		$sql = " SELECT COUNT(*) FROM ".$table_prefix."black_ips " . $where;

		$black_ips = get_db_value($sql);

		return $black_ips;
	}

	function check_banned_content($message)
	{
		global $db, $table_prefix;

		$is_banned = false; $banned_regexp = "";
		$sql = " SELECT content_text FROM ".$table_prefix."banned_contents ";
		$db->query($sql);
		while ($db->next_record()) {
			if ($banned_regexp) { $banned_regexp .= "|"; }
			$content_text = $db->f("content_text");
			$banned_regexp .= "(" . prepare_regexp($content_text) . ")";
		}

		if ($banned_regexp) {
			$is_banned = preg_match("/".$banned_regexp."/is", $message);
		}

		return $is_banned;
	}

	function hmac_md5 ($data, $key)
	{
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

	function set_menus(&$menus, $parent_id, $level, $active_menu_id, $top_menu_type = 0, $template_prefix = "", $submenu_style_name = "")
	{
		global $t, $current_page, $language_code;
		
		$subs = (isset($menus[$parent_id]) && isset($menus[$parent_id]["subs"])) ? $menus[$parent_id]["subs"] : array();
		$request_uri_path = get_request_path();
		$request_page     = get_request_page();
		if (!isset($current_page)) { $current_page = $request_page; }
		
		$t->set_var("secondary_table_class", $submenu_style_name . "Menu");
		$t->set_var("secondary_begin_id",    $submenu_style_name . "Begin");
		$t->set_var("secondary_end_id",      $submenu_style_name . "End");
		for ($i = 0, $ic = count($subs); $i < $ic; $i++)
		{
			$show_menu_id       = $subs[$i];
			$menu_url           = $menus[$show_menu_id]["menu_url"];
			$menu_target        = $menus[$show_menu_id]["menu_target"];
			$menu_title         = $menus[$show_menu_id]["menu_title"];
			$menu_image         = $menus[$show_menu_id]["menu_image"];
			$menu_image_active  = $menus[$show_menu_id]["menu_image_active"];
			$menu_submenu_style = $menus[$show_menu_id]["submenu_style_name"];
			$menu_path          = $menus[$show_menu_id]["menu_path"];
			$menu_path_id       = str_replace(",", "_", $menu_path) . $show_menu_id;

			$has_nested    = isset($menus[$show_menu_id]["subs"]) ? is_array($menus[$show_menu_id]["subs"]) : false;
			$is_last       = ($i == $ic - 1);
			$is_first      = ($i == 0);
			
			if ($has_nested) {
				set_menus($menus, $show_menu_id, $level + 1, $active_menu_id, $top_menu_type, $template_prefix, $submenu_style_name);
			}

			$t->set_var($template_prefix . "menu_path_id", $menu_path_id);

			$is_active = false;
			if ($show_menu_id == $active_menu_id) {
				$is_active = true;
			} elseif (isset($menus[$show_menu_id]["match_type"])) {
				$menu_page  = $menus[$show_menu_id]["menu_page"];
				$match_type = $menus[$show_menu_id]["match_type"];
				if ($menu_page == $request_page || $menu_page == $current_page || $menu_page == $request_uri_path) {
					if ($match_type == 1) {
						$is_active = true;
					} else if ($match_type == 2) {
						$is_active = true;
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
										$is_active = false;
									}
								}
							}
						}
					}
				}			
			}
			
			$menu_style = $submenu_style_name ? ($submenu_style_name . "Menu") : "menu";
			if ($is_active) {				
				$menu_style .="Active";
				$menu_image = $menu_image_active;
				if ($is_active && $template_prefix) {
					set_session("session_last_submenu_id", $show_menu_id);
				}
			}			
			
			$t->set_var($template_prefix . "menu_href",  htmlspecialchars($menu_url));
			if ($menu_target) {
				$t->set_var($template_prefix."menu_target", "target=\"".htmlspecialchars($menu_target)."\"");
			} else {
				$t->set_var($template_prefix."menu_target", "");
			}
			$t->set_var($template_prefix . "menu_style", $menu_style);
			$t->set_var($template_prefix . "menu_title", $menu_title);

			if ($top_menu_type) {
				if($menu_image && file_exists($menu_image) && ($top_menu_type != 2 || !strlen($menu_title)))
				{
					$is_menu_image = true;
					// check image translation
					$slash_pos = strrpos($menu_image, "/");
					if ($slash_pos === false) {
						$menu_image_translation = $language_code . "/" . $menu_image;
					} else {
						$menu_image_translation = substr($menu_image, 0, $slash_pos) . "/" . $language_code . substr($menu_image, $slash_pos);
					}
					if (file_exists($menu_image_translation)) {
						$menu_image = $menu_image_translation;
					}

					$image_size = @GetImageSize($menu_image);
					$t->set_var("alt", htmlspecialchars($menu_title));
					$t->set_var("src", htmlspecialchars($menu_image));
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
					$t->sparse($template_prefix . "menu_image", false);
				} else {
					$is_menu_image = false;
					$t->set_var($template_prefix . "menu_image", "");
				}

				if ($top_menu_type != 1 || !$is_menu_image) {
					$t->sparse($template_prefix . "menu_text", false);
				} else {
					$t->set_var($template_prefix . "menu_text", "");
				}
			} else {
				$t->set_var($template_prefix . "menu_image", "");
				$t->set_var($template_prefix . "menu_text", "");
			}
			
			if ($has_nested) {
				$t->set_var($template_prefix . "submenus", $t->get_var($template_prefix . "submenus_" . ($level + 1)));
				$t->parse($template_prefix . "submenus_rows");
				$t->set_var($template_prefix . "submenus_" . ($level + 1), "");
				$t->set_var($template_prefix . "submenus", "");
			} else {
				$t->set_var($template_prefix . "submenus_rows", "");
			}
			
			if ($level > 0) {
				$t->parse_to($template_prefix . "menus", $template_prefix . "submenus_" . $level);
			} else {
				$t->parse($template_prefix . "menus");
			}
			
			if ($has_nested) {
				$t->set_var($template_prefix . "submenus_rows", "");
			}
			
			if ($is_last && $level == 0) {
				$t->parse($template_prefix . "menus_rows");
				$t->set_var($template_prefix . "menus", "");
			}
		}
	}
	
	function set_categories($parent_id, $level, $columns, $top_id, $image_type = 1)
	{
		global $t, $categories, $category_number, $restrict_categories_images, $settings, $category_id;
		global $list_url, $list_page;

		$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		
		$subs = (isset($categories[$parent_id]) && isset($categories[$parent_id]["subs"])) ? $categories[$parent_id]["subs"] : array();
		for ($i = 0, $ic = count($subs); $i < $ic; $i++)
		{
			$show_category_id = $subs[$i];
			$category_name    = $categories[$show_category_id]["category_name"];
			$a_title          = $categories[$show_category_id]["a_title"];
			$friendly_url     = $categories[$show_category_id]["friendly_url"];
			
			
			$has_nested    = isset($categories[$show_category_id]["subs"]) ? is_array($categories[$show_category_id]["subs"]) : false;
			$is_rss        = isset($categories[$show_category_id]["is_rss"]) ? $categories[$show_category_id]["is_rss"] : false;
			$is_last       = ($i == $ic - 1);
			$is_first      = ($i == 0);
			$is_restricted = isset($categories[$show_category_id]["allowed"]) ? !$categories[$show_category_id]["allowed"] : false;
			
			if ($has_nested) {
				set_categories($show_category_id, $level + 1, $columns, $top_id, $image_type);
			}
			
			if ($friendly_urls && $friendly_url) {
				$list_url->remove_parameter("category_id");
				$t->set_var("list_url", htmlspecialchars($list_url->get_url($friendly_url . $friendly_extension)));
			} else {
				$list_url->add_parameter("category_id", CONSTANT, $show_category_id);
				$t->set_var("list_url", htmlspecialchars($list_url->get_url($list_page)));
			}
			if ($image_type == 2) {
				$category_image = $categories[$show_category_id]["image"];
				$image_alt = $categories[$show_category_id]["image_alt"];
			} else if ($image_type == 3) {
				$category_image = $categories[$show_category_id]["image_large"];
				$image_alt = $categories[$show_category_id]["image_large_alt"];
			} else {
				$category_image = false;
			}
			$short_description = $categories[$show_category_id]["short_description"];
			

			$t->set_var("category_id", $show_category_id);
			$t->set_var("category_name", htmlspecialchars($category_name));
			$t->set_var("a_title", htmlspecialchars($a_title));
			$t->set_var("level", $level);
			
			if ($show_category_id == $category_id) {
				if ($level > 0) {
					$category_class = "selectedsubCategory";
				} else {
					$category_class = "selectedtopCategory";
				}
			} else {
				if ($level > 0) {
					$category_class = "subCategory";
				} else {
					$category_class = "topCategory";
				}
			}
			if ($is_first) {
				$category_class .= " firstCategory";
			}
			if ($is_last) {
				$category_class .= " lastCategory";
			}
			
			$t->set_var("category_class", $category_class);		
			if ($is_restricted) {
				$t->set_var("restricted_class", " restrictedCategory");
				$t->sparse("restricted_image", false);
			} else {
				$t->set_var("restricted_class", "");
				$t->set_var("restricted_image", "");
			}
			if ($is_rss) {
				$t->parse("category_rss", false);
			} else {
				$t->set_var("category_rss", "");
			}
			
			if ($category_image) 
			{
				if (preg_match("/^(http|https|ftp|ftps)\:\/\//", $category_image)) {
					$image_size = "";
				} else {
					$image_size = @GetImageSize($category_image);
					if (isset($restrict_categories_images) && $restrict_categories_images) {
						$category_image = "image_show.php?category_id=".$show_category_id;
					}
				}
				if (!strlen($image_alt)) { $image_alt = $category_name; }
				$t->set_var("alt", htmlspecialchars($image_alt));
				$t->set_var("src", htmlspecialchars($category_image));
				if (is_array($image_size))
				{
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				}
				else
				{
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				$t->parse("category_image", false);
			} else {
				$t->set_var("category_image", "");
			}
			
			if ($has_nested) {
				$t->set_var("subcategories", $t->get_var("subcategories_" . ($level + 1)));
				$t->parse("subcategories_rows");
				$t->set_var("subcategories_" . ($level + 1), "");
				$t->set_var("subcategories", "");
			} else {
				$t->set_var("subcategories_rows", "");
			}		
			
			if ($level > 0) {
				$t->parse_to("categories", "subcategories_" . $level);
			} else {
				$t->parse("categories");
			}	
			
			if ($has_nested) {
				$t->set_var("subcategories_rows", "");
			}		
			
			if ($is_last && $level == 0) {
				$t->parse("categories_rows");
				$t->set_var("categories", "");
			}			
		}		
	}

	function process_level_colors($message, $bbcoded = false) {
				
		// set level colors
		$level_colors = array(
			"0"=>"black", "1"=>"blue", "2"=>"red", "3"=>"green", "4"=>"gray", "5"=>"navy", "6"=>"olive", "7"=>"brown", "8"=>"purple"
		);
		
		$msg_strings = explode("\n", $message);
		$message     = "";
		$last_level  = 0;
		$ln = 0;

		$message .= "<div style='color:".$level_colors[0].";'>";
		foreach ($msg_strings as $line) {
			$ln++;
			//-- get current level
			if (preg_match("/^>+/", $line, $match)) {
				$cur_level = strlen($match[0]);
			} else {
				$cur_level = 0;
			}
			$line = preg_replace("/^>+/", "", $line);
			if (!trim($line)) { $line = "&nbsp;"; }
			if ($bbcoded) { $line = str_replace(array("<", ">"), array("&lt;", "&gt;"), $line); }
			$level_diff = $last_level - $cur_level;
			if ($level_diff > 0) {
				$tags = "";
				for ($t = 1; $t <= $level_diff; $t++) {
					$tags .="</div>";
				}
				$line = $tags . $line;
			} elseif ($level_diff < 0) {
				$tags = "";
				for ($t = $last_level; $t < $cur_level; $t++) {
					$tags .="<div style='color:". $level_colors[($t + 1) % 9] ."; margin-left: 5pt; padding-left: 5pt; border-left-style:solid; border-left-width:thin;'>";
				}
				$line = $tags . $line;
			} else {
				if ($ln > 1) {
					if (!$bbcoded) {
						$line = "<br>" . $line;
					}
				}
			}
			$last_level = $cur_level;
			$message .= $line;
		}

		//-- add end tags
		$tags = "";
		for ($t = 1; $t <= $last_level; $t++) {
			$tags .= "</div>";
		}
		$message .= $tags . "</div>";
		
		return $message;
	}
	function process_message($message, $icons_enable = 0, $allow_bbcode = false, $convert_links = 1, $convert_long_words = 1, $symbols = 128)
	{
		global $icons_codes, $icons_tags;

		$eol = get_eol();
		
		if ($convert_long_words) {
			split_long_words($message, $convert_links, $symbols);
		}

		$message = preg_replace("/</", "&lt;", $message);
		$message = preg_replace("/!^>/", "&gt;", $message);
		
		if ($allow_bbcode) {
			$message = preg_replace(
				"/\[url\]((http|https|ftp|ftps)[^\[]*)\[\/url\]/i", 
				"<a rel=\"nofollow\" target=\"_blank\" href=\"$1\">$1</a>", 
				$message
			);
			$message = preg_replace(
				"/\[url=('?\"?(http|https|ftp|ftps)[^\]]*)\]([^\[]*)\[\/url\]/e",
				"'<a rel=\"nofollow\" target=\"_blank\" href=\"' . str_replace(array('\\'', '\\\"'), array('',''), '$1') . '\">$3</a>'", 
				$message
			);
			$bbcode = array(
				"[list]", "[*]", "[/list]", 
				"[img]", "[/img]", 
				"[b]", "[/b]", 
				"[u]", "[/u]", 
				"[i]", "[/i]",
				'[color="', "[/color]",
				"[size=\"", "[/size]",
				'[mail="', "[/mail]",
				"[code]", "[/code]",
				"[quote]", "[/quote]",
				'"]'
			);
			
			$htmlcode = array(
				"<ul>", "<li>", "</ul>", 
				"<img src=\"", "\">", 
				"<b>", "</b>", 
				"<u>", "</u>", 
				"<i>", "</i>",
				"<span style=\"color:", "</span>",
				"<span style=\"font-size:", "</span>",
				"<a href=\"mailto:", "</a>",
				"<code>", "</code>",
				"<table width=\"100%\" bgcolor=\"lightgray\"><tr><td bgcolor=\"white\">", "</td></tr></table>",
				'">'
			);
			$message = str_replace($bbcode, $htmlcode, $message);
		} 
		if ($convert_links) {
			if (preg_match_all("/(.*)(https?:\\/\\/[\w\d_\-\+\\%\.\?\&\#\~\=\\/]+)(.*)/i", $message, $matches)) {
				$links = array();
				for ($p = 0; $p < sizeof($matches[0]); $p++) {
					$before_link = $matches[1][$p];
					$link = $matches[2][$p];
					$after_link = $matches[3][$p];
					if ((!strlen($before_link) || preg_match("/\s$/", $before_link)) && 
						(!strlen($after_link) || preg_match("/^\s/", $after_link))) {
						if ($convert_long_words && strlen($link) > $symbols) {
							$link_name = substr($link, 0, $symbols) . "...";
						} else {
							$link_name = $link;
						}
						$message = str_replace($before_link.$link.$after_link, $before_link."<a rel=\"nofollow\" href=\"" . $link . "\" target=\"_blank\">" . $link_name . "</a>".$after_link, $message);
					}
				}
			}
		}
		
		$message = str_replace("\r","", $message);
		if ($icons_enable && is_array($icons_codes)) { // replace emotion icons
			$message = str_replace($icons_codes, $icons_tags, $message);
		}		
		$message = process_level_colors($message, false);		

		return $message;
	}

	function get_tax_amount($tax_rates, $item_type, &$price, $quantity, $item_tax_id, $tax_free, &$tax_percent, $default_tax_rates = "", $return_type = 1, $tax_prices_type = "", $tax_round = "")
	{
		global $settings, $currency;

		if ($quantity <= 0) { $quantity = 1; } // used to calculated fixed tax
		$taxes_values = array();
		$item_tax_amount = 0; 
		if (!strlen($tax_prices_type)) {
			$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		}
		if (!strlen($tax_round)) {
			$tax_round = get_setting_value($settings, "tax_round", 1);
		}
		$decimals = get_setting_value($currency, "decimals", 2);

		if (is_array($default_tax_rates) && $tax_prices_type == 1 && !$tax_free) {
			// if price includes tax check if default taxes different from user taxes
			$tax_rates_identical = true;
			if (is_array($tax_rates)) {
				$tax_rates_identical = ($tax_rates == $default_tax_rates);
			} else {
				$tax_rates_identical = false;
			}

			if (!$tax_rates_identical) {
				// calculate price without tax to apply different tax rates
				$default_tax_percent = 0; $default_fixed_tax = 0;
				foreach ($default_tax_rates as $id => $tax_rate) {
					// check if tax should be used for current item
					$tax_id = $tax_rate["tax_id"];
					$tax_type = $tax_rate["tax_type"]; 
					if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
						// check default tax percent
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
							&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
							$default_tax_percent += $tax_rate["types"][$item_type]["tax_percent"];
						} else {
							$default_tax_percent += $tax_rate["tax_percent"];
						}
						// check default tax amount
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
							&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
							$default_fixed_tax += $tax_rate["types"][$item_type]["fixed_amount"] * $quantity;
						} else {
							$default_fixed_tax += $tax_rate["fixed_amount"] * $quantity;
						}
					}
				}
				// deduct default tax
				$price_excl_tax = (($price * 100) / ($default_tax_percent + 100)) - $default_fixed_tax;

				$tax_percent = 0; $fixed_tax = 0;
				if (is_array($tax_rates)) {
					foreach ($tax_rates as $id => $tax_rate) {
						// check if tax should be used for current item
						$tax_id = $tax_rate["tax_id"];
						$tax_type = $tax_rate["tax_type"]; 
						if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
							// check tax percent
							if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
								&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
								$tax_percent += $tax_rate["types"][$item_type]["tax_percent"];
							} else {
								$tax_percent += $tax_rate["tax_percent"];
							}
					  
							// check tax amount 
							if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
								&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
								$fixed_tax += $tax_rate["types"][$item_type]["fixed_amount"] * $quantity;
							} else {
								$fixed_tax += $tax_rate["fixed_amount"] * $quantity;
							}
						}
					}
				}
				// calculate price with current tax
				$price = va_round($price_excl_tax + (($price_excl_tax * $tax_percent) / 100) + $fixed_tax, $decimals);
			}
		}

		if (!isset($tax_percent)) { $tax_percent = 0; }
		if (!$tax_free) {
			// calculate summary tax
			$tax_percent = 0; $fixed_tax = 0; $item_tax_amount = 0;
			if (is_array($tax_rates)) {
				foreach ($tax_rates as $id => $tax_rate) {
					// check if tax should be used for current item
					$tax_id = $tax_rate["tax_id"];
					$tax_type = $tax_rate["tax_type"]; 
					if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
						$current_tax_percent = 0; $current_fixed_tax = 0; $current_item_tax = 0;
						// check tax percent
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
							&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
							$current_tax_percent = $tax_rate["types"][$item_type]["tax_percent"];
						} else {
							$current_tax_percent = $tax_rate["tax_percent"];
						}
						// check tax amount 
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
							&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
							$current_fixed_tax = $tax_rate["types"][$item_type]["fixed_amount"] * $quantity;
						} else {
							$current_fixed_tax = $tax_rate["fixed_amount"] * $quantity;
						}
						// calculate tax amount for each tax
						if ($tax_prices_type == 1) { // prices includes tax
							$current_item_tax = $price - (($price * 100) / ($current_tax_percent + 100)) - $current_fixed_tax;
						} else {
							$current_item_tax = (($price * $current_tax_percent) / 100) + $current_fixed_tax;
						}
						if ($tax_round == 1) {
							$current_item_tax = va_round($current_item_tax, $decimals);
						}
						$taxes_values[$tax_id] = array(
							"tax_name" => $tax_rate["tax_name"], "show_type" => isset($tax_rate["show_type"])? $tax_rate["show_type"] : 0, 
							"tax_percent" => $current_tax_percent, "fixed_value" => $current_fixed_tax, "tax_amount" => $current_item_tax,
						);
						$tax_percent += $current_tax_percent;
						$fixed_tax += $current_fixed_tax;
						$item_tax_amount += $current_item_tax;
					}
				}
			}
		} else {
			$tax_percent = 0; $fixed_tax = 0;
		}

		if ($return_type == 2) {
			return $taxes_values;
		} else {
			return $item_tax_amount;
		}
	}

	function va_round($number, $precision)
	{
		global $settings;
		$round_type = get_setting_value($settings, "round_type", 1); // 1 - Common, 2 - Round to even
		if ($round_type == 1) {
			return round($number + pow(0.1, ($precision + 2)), $precision); // small correction to get common rounding
		} else {
			return round($number, $precision); // round to even method
		}
	}

	function add_tax_values(&$tax_rates, $tax_values, $item_type, $tax_round = "")
	{
		global $settings, $currency;

		if (!strlen($tax_round)) {
			$tax_round = get_setting_value($settings, "tax_round", 1);
		}
		$decimals = get_setting_value($currency, "decimals", 2);
		$total_tax = 0;
		if (is_array($tax_values)) {
			foreach($tax_values as $tax_id => $tax_info) {
				$tax_amount = $tax_info["tax_amount"];
				if ($tax_round == 1) {
					$tax_amount = round($tax_amount, $decimals);
				}
				$total_tax += $tax_amount;
				if (!isset($tax_rates[$tax_id][$item_type])) {
					$tax_rates[$tax_id][$item_type] = 0;
				}
				if (!isset($tax_rates[$tax_id]["tax_total"])) {
					$tax_rates[$tax_id]["tax_total"] = 0;
				}
				$tax_rates[$tax_id][$item_type] += $tax_amount;
				$tax_rates[$tax_id]["tax_total"] += $tax_amount;
			}
		}
		return $total_tax;
	}

	function check_image_validation($validation_number)
	{
		$session_random_arr = get_session("session_random_arr");
		if (strlen($validation_number) == 0 || !in_array($validation_number, $session_random_arr)) {
			return false;
		} else {
			unset($session_random_arr[array_search($validation_number, $session_random_arr)]);
			set_session("session_random_arr", $session_random_arr);
			return $validation_number;
		}
	}

	function get_nice_bytes($bytes)
	{
	  if ($bytes >= 1024 && $bytes < 1048576) {
			return round($bytes / 1024) . "Kb";
		} elseif ($bytes >= 1048576) {
			return round($bytes / 1048576, 1) . "Mb";
		} else {
			return $bytes." bytes";
		}
	}

	function get_currency($currency_code = "", $update_session = true)
	{
		global $db, $table_prefix, $is_admin_path, $site_id;
  
		if ($currency_code == "") {
			$currency_code = get_param("currency_code");
		}
		$currency = get_session("session_currency");
		if (strlen($currency_code) || !is_array($currency)) {
			if ($is_admin_path) {
				$version = va_version();
				$sql  = " SELECT c.* ";
				if (comp_vers($version , "3.6.19") == 1) {
					$sql .= " FROM (" . $table_prefix . "currencies c ";
					$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				} else {
					$sql .= " FROM " . $table_prefix . "currencies c";
				}
				$sql_where = "";
				if (comp_vers($version , "3.5.20") == 1) {
					$sql_where .= " c.show_for_user=1 ";
				}
				if (strlen($currency_code)) {
					if ($sql_where) $sql_where .= " AND ";
					$sql_where .= " ( c.currency_code=" . $db->tosql($currency_code, TEXT);
					$sql_where .= " OR c.currency_value=" . $db->tosql($currency_code, TEXT) . " ) ";
				} elseif (comp_vers($version , "3.5.22") == 1)  {
					if ($sql_where) $sql_where .= " AND ";
					$sql_where .= " c.is_default_show=1 ";
				}				
				if (comp_vers($version , "3.6.19") == 1) {
					if ($sql_where) $sql_where .= " AND ";
					$sql_where .= " (c.sites_all=1 OR cs.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				}
				if ($sql_where)
					$sql .= " WHERE " . $sql_where;
			} else {			
				$sql  = " SELECT c.* ";
				$sql .= " FROM (" . $table_prefix . "currencies c ";
				$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				$sql .= " WHERE c.show_for_user=1 ";			
				$sql .= " AND (c.sites_all=1 OR cs.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " AND ( c.currency_code=" . $db->tosql($currency_code, TEXT);
				$sql .= " OR c.currency_value=" . $db->tosql($currency_code, TEXT) . " ) ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$currency["code"] = $db->f("currency_code");
				$currency["value"] = $db->f("currency_value");
				$currency["left"] = $db->f("symbol_left");
				$currency["right"] = $db->f("symbol_right");
				$currency["rate"] = $db->f("exchange_rate");
				$currency["decimals"] = $db->f("decimals_number");
				$currency["point"] = $db->f("decimal_point");
				$currency["separator"] = $db->f("thousands_separator");
			} else {
				$sql  = " SELECT c.* ";
				$sql .= " FROM (" . $table_prefix . "currencies c ";
				$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				$sql .= " WHERE c.show_for_user=1 ";			
				$sql .= " AND (c.sites_all=1 OR cs.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " AND c.is_default_show=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$currency["code"] = $db->f("currency_code");
					$currency["value"] = $db->f("currency_value");
					$currency["left"] = $db->f("symbol_left");
					$currency["right"] = $db->f("symbol_right");
					$currency["rate"] = $db->f("exchange_rate");
					$currency["decimals"] = $db->f("decimals_number");
					$currency["point"] = $db->f("decimal_point");
					$currency["separator"] = $db->f("thousands_separator");
				} else {
					$currency["left"] = ""; $currency["right"] = "";
					$currency["code"] = ""; 
					$currency["value"] = "";
					$currency["rate"] = 1;
					$currency["decimals"] = 2;
					$currency["point"] = ".";
					$currency["separator"] = ",";
				}
			}
			if ($update_session) {
				set_session("session_currency", $currency);
			}
		}
  
		return $currency;
	}

	function currency_format($price, $price_currency = "", $tax_amount = 0)
	{
		global $settings, $currency;
		if (!is_array($price_currency)) {
			$price_currency = $currency;
		}
		if ($tax_amount) {
			$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
			if ($tax_prices_type == 1) {
				$price_incl = $price;
				$price_excl = $price - $tax_amount;
			} else {
				$price_incl = $price + $tax_amount;
				$price_excl = $price;
			}
			$tax_prices = get_setting_value($settings, "tax_prices", 0);
			if ($tax_prices == 2 || $tax_prices == 3) {
				$price = $price_incl;
			} else {
				$price = $price_excl;
			}
		}
		return $price_currency["left"] . number_format($price * $price_currency["rate"], $price_currency["decimals"], $price_currency["point"], $price_currency["separator"]) . $price_currency["right"];
	}

	function set_curl_options(&$ch, $curl_options)
	{
		if (isset($curl_options["CURLOPT_PROXY"]) && strlen($curl_options["CURLOPT_PROXY"])) {
			curl_setopt($ch, CURLOPT_PROXY, $curl_options["CURLOPT_PROXY"]);
			if (isset($curl_options["CURLOPT_PROXYUSERPWD"]) && strlen($curl_options["CURLOPT_PROXYUSERPWD"])) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $curl_options["CURLOPT_PROXYUSERPWD"]);
			}
		}

	}

	/**
	 * Return file size of remote file
	 *
	 * @param string $url
	 * @return integer
	 */
	function remote_filesize($url)
	{
		$size = 0;
		$parsed_url = parse_url($url);
		$sch = isset($parsed_url["scheme"]) ? $parsed_url["scheme"] : "";
		$host = isset($parsed_url["host"]) ? $parsed_url["host"] : "";
		$port = isset($parsed_url["port"]) ? $parsed_url["port"] : "";
		$user = isset($parsed_url["user"]) ? $parsed_url["user"] : "";
		$pass = isset($parsed_url["pass"]) ? $parsed_url["pass"] : "";
		$path = isset($parsed_url["path"]) ? $parsed_url["path"] : "";
		$query = isset($parsed_url["query"]) ? $parsed_url["query"] : "";
		if (in_array($sch, array("http", "https", "ftp", "ftps"))) {
			if (($sch == "http") || ($sch == "https")) {
				switch ($sch) {
					case "http":
						if (!$port) { $port = 80; } break;
					case "https";
						if (!$port) { $port = 443; } break;
				}
				$socket = @fsockopen($host, $port);
				if ($socket) {
					$out  = "HEAD $path?$query HTTP/1.0\r\n";
					$out .= "Host: $host\r\n";
					$out .= "UserAgent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
					$out .= "Connection: Close\r\n\r\n";
					fwrite($socket, $out);
					// read the response header
					$header = "";
					while (!feof($socket)){
						$header .= fread($socket, 1024*8);
					}
					fclose($socket);
					if (strlen($header)) {
						// try to acquire Content-Length within the response
						preg_match('/Content-Length:\s(\d+)/', $header, $matches);
						if (isset($matches[1])) {
							$size = $matches[1];
						}
					}
				}
			} elseif (($sch == "ftp") || ($sch == "ftps")) {
				if (strlen($host) && strlen($path)) {
					if (!$port) { $port = 21; }
					if (!$user) { $user = "anonymous"; }
					if (!$pass) { $pass = ""; }
					switch ($sch) {
						case "ftp":
							$ftpid = @ftp_connect($host, $port); break;
						case "ftps":
							$ftpid = @ftp_ssl_connect($host, $port); break;
						default:
							$ftpid = 0;
					}
					$ftpsize = 0;
					if ($ftpid) {
						$login = ftp_login($ftpid, $user, $pass);
						if ($login) {
							$ftpsize = ftp_size($ftpid, $path);
						}
						ftp_close($ftpid);
					}
					if ($ftpsize > 0) { $size = $ftpsize; }
				}
			}
		}

		return $size;
	}

	function eval_php_code(&$block_body)
	{
		if (preg_match_all("/(<\?php|<\?)(.*)\?>/Uis", $block_body, $matches)) {
			for ($p = 0; $p < sizeof($matches[0]); $p++) {
				ob_start();
				eval($matches[2][$p]);
				$output = ob_get_contents();
				ob_end_clean();
				$block_body = str_replace($matches[0][$p], $output, $block_body);
			}
		}
	}

	function split_long_words(&$text, $convert_links = 1, $symbols = 128)
	{
		if (preg_match_all("/[^\s\r\n]{" . $symbols . ",}/i", $text, $matches)) {
			$correction = $symbols - intval($symbols / 6);
			for ($p = 0; $p < sizeof($matches[0]); $p++) {
				$long_word = $matches[0][$p];
				if (!$convert_links || !preg_match("/https?:\\/\\/[\w\d_\-\+\\%\.\?\&\#\~\=\\/]+/i", $long_word)) {
					$original_word = $long_word;
					$new_word = ""; 
					$word_length = strlen($long_word);			
					while ($word_length) {
						if ($word_length > $symbols) {
							$word_part = substr($long_word, 0, $symbols);
							if ($word_length > $correction && preg_match("/^(.{".$correction."}.*[,\.\-\!\?\&\*_]).*/", $word_part, $word_match)) {
								$word_part = $word_match[1];
							}
						} else {
							$word_part = $long_word;
						}
						$word_part_len = strlen($word_part);
						$new_word .= $word_part . " ";
						$long_word = substr($long_word, $word_part_len);
						$word_length = strlen($long_word);
					}
					$text = str_replace($original_word, $new_word, $text);
				}
			}
		}
	}

	function user_login($login, $password, $user_id, $remember_me, $redirect_page, $make_redirects, &$errors)
	{
		global $db, $table_prefix, $settings;
		global $site_id, $multisites_version;
		$is_errors = false;
		$secure_sessions = get_setting_value($settings, "secure_sessions", 0);
		$password_encrypt = get_setting_value($settings, "password_encrypt", 0);
		if ($password_encrypt == 1) {
			$password_match = md5($password);
		} else {
			$password_match = $password;
		}
		// prepare site urls
		$site_url = get_setting_value($settings, "site_url", "");
		$secure_url = get_setting_value($settings, "secure_url", "");

		$sql  = " SELECT u.user_id, u.layout_id, u.user_type_id, u.is_approved, ";
		$sql .= " u.login, u.nickname, u.name, u.first_name, u.last_name, u.email, u.total_points, u.credit_balance, ";
		$sql .= " u.discount_type AS user_discount_type, u.discount_amount AS user_discount_amount, ";
		$sql .= " ut.discount_type AS group_discount_type, ut.discount_amount AS group_discount_amount, ";
		$sql .= " u.reward_type AS user_reward_type, u.reward_amount AS user_reward_amount, ";
		$sql .= " ut.reward_type AS group_reward_type, ut.reward_amount AS group_reward_amount, ";
		$sql .= " u.credit_reward_type AS user_credit_reward_type, u.credit_reward_amount AS user_credit_reward_amount, ";
		$sql .= " ut.credit_reward_type AS group_credit_reward_type, ut.credit_reward_amount AS group_credit_reward_amount, ";
		$sql .= " u.registration_last_step, u.registration_total_steps, ";
		$sql .= " ut.is_subscription, u.expiry_date, u.is_sms_allowed, ";
		$sql .= " u.tax_free AS user_tax_free, ut.tax_free AS group_tax_free, ";
		$sql .= " u.order_min_goods_cost AS user_min_goods, u.order_max_goods_cost AS user_max_goods, ";
		$sql .= " ut.order_min_goods_cost AS group_min_goods, ut.order_max_goods_cost AS group_max_goods, ";
		$sql .= " ut.price_type, c.currency_code, u.subscription_id ";
		$sql .= " FROM (((" . $table_prefix . "users u ";
		$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "countries c ON u.country_id=c.country_id) ";
		if ($multisites_version) {	
			if (isset($site_id))  {
				$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites AS uts ON uts.type_id=ut.type_id)";
				$sql .= " WHERE (ut.sites_all=1 OR uts.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") AND ";
			} else {
				$sql .= ") WHERE ut.sites_all=1 AND ";					
			}
		} else {
			$sql .= ") WHERE ";
		}
		if ($user_id) {
			$sql .= " u.user_id=" . $db->tosql($user_id, INTEGER);
		} else {
			$sql .= " u.login=" . $db->tosql($login, TEXT);
			$sql .= " AND u.password=" . $db->tosql($password_match, TEXT);
		}
		$db->query($sql);
		if ($db->next_record())
		{
			$current_date = va_time();
			$current_ts = va_timestamp();
			$user_id = $db->f("user_id");
			$layout_id = $db->f("layout_id");
			$is_approved = $db->f("is_approved");
			$is_sms_allowed = $db->f("is_sms_allowed");
			$total_points = $db->f("total_points");
			$credit_balance = $db->f("credit_balance");
			$user_tax_free = $db->f("user_tax_free");
			$group_tax_free = $db->f("group_tax_free");
			$tax_free = ($user_tax_free || $group_tax_free);
			$order_min_goods_cost = $db->f("user_min_goods");
			if (!strlen($order_min_goods_cost)) {
				$order_min_goods_cost = $db->f("group_min_goods");
			}
			$order_max_goods_cost = $db->f("user_max_goods");
			if (!strlen($order_max_goods_cost)) {
				$order_max_goods_cost = $db->f("group_max_goods");
			}
			// check account expiration date
			$expiry_date = $db->f("expiry_date", DATETIME);
			if (is_array($expiry_date)) {
				$expiry_date_ts = mktime (0, 0, 0, $expiry_date[MONTH], $expiry_date[DAY] + 1, $expiry_date[YEAR]);
			} else {
				$expiry_date_ts = $current_ts;
			}
			$user_type_id = $db->f("user_type_id");
			$is_subscription = $db->f("is_subscription");
			$registration_last_step = $db->f("registration_last_step");
			$registration_total_steps = $db->f("registration_total_steps");
			if ($registration_last_step < $registration_total_steps) {
				// if registration process wasn't finished
				set_session("session_new_user", "registration");
				set_session("session_new_user_id", $user_id);
				set_session("session_new_user_type_id", $user_type_id);
				// check secure option
				$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
				if ($secure_user_profile || $secure_sessions) {
					$user_profile_url = $secure_url . get_custom_friendly_url("user_profile.php");
				} else {
					$user_profile_url = $site_url . get_custom_friendly_url("user_profile.php");
				}
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}
				header ("Location: " . $user_profile_url);
				exit;
			} elseif ($current_ts > $expiry_date_ts && $is_subscription) {
				// if user have to pay for subscription
				set_session("session_new_user", "expired");
				set_session("session_new_user_id", $user_id);
				set_session("session_new_user_type_id", $user_type_id);
				// add some data into session for expired user as well
				$user_info = array(
					"tax_free" => $tax_free, "is_sms_allowed" => $is_sms_allowed,
					"total_points" => $total_points, "credit_balance" => $credit_balance,
					"order_min_goods_cost" => $order_min_goods_cost, "order_max_goods_cost" => $order_max_goods_cost,
				);
				set_session("session_user_info", $user_info);
				include_once ("./includes/shopping_cart.php");
				add_subscription($user_type_id, "", $subscription_name);
				// check secure option
				$secure_order_profile = get_setting_value($settings, "secure_order_profile", 0);
				if ($secure_order_profile || $secure_sessions) {
					$order_info_url = $secure_url . get_custom_friendly_url("order_info.php");
				} else {
					$order_info_url = $site_url . get_custom_friendly_url("order_info.php");
				}
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}
				header("Location: " . $order_info_url);
				exit;
			} elseif ($current_ts <= $expiry_date_ts && $is_approved) {
				$login = $db->f("login");
				$nickname = $db->f("nickname");
				if (!strlen($nickname)) { $nickname = $login; }
				$email = $db->f("email");
				$currency_code = $db->f("currency_code");
				$user_discount_type = $db->f("user_discount_type");
				$user_discount_amount = $db->f("user_discount_amount");
				$group_discount_type = $db->f("group_discount_type");
				$group_discount_amount = $db->f("group_discount_amount");
				$user_reward_type = $db->f("user_reward_type");
				$user_reward_amount = $db->f("user_reward_amount");
				$group_reward_type = $db->f("group_reward_type");
				$group_reward_amount = $db->f("group_reward_amount");
				$user_credit_reward_type = $db->f("user_credit_reward_type");
				$user_credit_reward_amount = $db->f("user_credit_reward_amount");
				$group_credit_reward_type = $db->f("group_credit_reward_type");
				$group_credit_reward_amount = $db->f("group_credit_reward_amount");
				$price_type = $db->f("price_type");
				$subscription_id = $db->f("subscription_id");
				
				set_session("session_new_user", "");
				set_session("session_new_user_id", "");
				set_session("session_new_user_type_id", "");
				set_session("session_user_id", $user_id);
				set_session("session_user_type_id", $user_type_id);
				set_session("session_user_login", $login);
				set_session("session_subscription_id", $subscription_id);

				if (strlen($db->f("name"))) {
					$user_name = $db->f("name");
				} elseif (strlen($db->f("first_name")) || strlen($db->f("last_name"))) {
					$user_name = $db->f("first_name") . " " . $db->f("last_name");
				} else {
					$user_name = $login;
				}
				set_session("session_user_name", $user_name);
				set_session("session_user_email", $email);
				$discount_type = ""; $discount_amount = "";
				if ($user_discount_type > 0) {
					$discount_type = $user_discount_type;
					$discount_amount = $user_discount_amount;
				} elseif ($group_discount_type)  {
					$discount_type = $group_discount_type;
					$discount_amount = $group_discount_amount;
				}
				set_session("session_discount_type", $discount_type);
				set_session("session_discount_amount", $discount_amount);
				set_session("session_price_type", $price_type);

				$reward_type = ""; $reward_amount = "";
				if ($user_reward_type > 0) {
					$reward_type = $user_reward_type;
					$reward_amount = $user_reward_amount;
				} elseif ($group_reward_type)  {
					$reward_type = $group_reward_type;
					$reward_amount = $group_reward_amount;
				}

				$credit_reward_type = ""; $credit_reward_amount = "";
				if ($user_credit_reward_type > 0) {
					$credit_reward_type = $user_credit_reward_type;
					$credit_reward_amount = $user_credit_reward_amount;
				} elseif ($group_credit_reward_type)  {
					$credit_reward_type = $group_credit_reward_type;
					$credit_reward_amount = $group_credit_reward_amount;
				}

				// check for subscriptions
				$subscriptions_ids = "";
				$check_date_ts = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
				$sql  = " SELECT subscription_id ";
				$sql .= " FROM " . $table_prefix . "orders_items ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND is_subscription=1 ";
				$sql .= " AND subscription_expiry_date>=" . $db->tosql($check_date_ts, DATETIME);
				$db->query($sql);
				while ($db->next_record()) {
					if ($subscriptions_ids) { $subscriptions_ids .= ","; }
					$subscriptions_ids .= $db->f("subscription_id");
				}
				set_session("session_subscriptions_ids", $subscriptions_ids);

				$user_info = array(
					"user_id" => $user_id, "user_type_id" => $user_type_id, "layout_id" => $layout_id,
					"login" => $login, "nickname" => $nickname, "name" => $user_name, "subscriptions_ids" => $subscriptions_ids,
					"email" => $email, "discount_type" => $discount_type, "discount_amount" => $discount_amount,
					"price_type" => $price_type, "tax_free" => $tax_free, "is_sms_allowed" => $is_sms_allowed,
					"reward_type" => $reward_type, "reward_amount" => $reward_amount, 
					"credit_reward_type" => $credit_reward_type, "credit_reward_amount" => $credit_reward_amount, 
					"total_points" => $total_points, "credit_balance" => $credit_balance,
					"order_min_goods_cost" => $order_min_goods_cost, "order_max_goods_cost" => $order_max_goods_cost,
				);
				set_session("session_user_info", $user_info);

				if ($remember_me && $login && $password)
				{
					setcookie("cookie_user_login", $login, va_timestamp() + 3600 * 24 * 366);
					setcookie("cookie_user_password", $password, va_timestamp() + 3600 * 24 * 366);
				}

				// get currency if available
				if ($currency_code) {
					get_currency($currency_code);
				}

				// update shopping cart if it's available
				$shopping_cart = get_session("shopping_cart");
				if (is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
					include_once ("./includes/shopping_cart.php");
					recalculate_shopping_cart();
					// check if any coupons can be added or removed
					check_coupons();
				}

				// check if need to regenerate session id for secure session
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}

				// update last visit time
				$sql  = " UPDATE " . $table_prefix . "users SET last_visit_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= ", last_visit_ip=" . $db->tosql(get_ip(), TEXT);
				$sql .= ", last_visit_page=" . $db->tosql(get_request_uri(), TEXT);
				$sql .= ", last_logged_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= ", last_logged_ip=" . $db->tosql(get_ip(), TEXT);
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$db->query($sql);

				if ($make_redirects && $redirect_page) {
					// convert redirect page to the full url
					$ssl = get_param("ssl");
					if ($ssl) {
						$page_site_url = $secure_url;
					} else {
						$page_site_url = $site_url;
					}
					$return_page = get_request_uri();
					if (!preg_match("/^https?:\\/\\//i", $redirect_page) && preg_match("/^https?:\\/\\/[^\\/]+(\\/.*)$/i", $page_site_url, $matches)) {
						$page_path_regexp = prepare_regexp($matches[1]);
						if (preg_match("/^" .$page_path_regexp. "/i", $redirect_page)) {
							$redirect_page = $page_site_url . preg_replace("/^" .$page_path_regexp. "/i", "", $redirect_page);
						} 
					}
					header("Location: " . $redirect_page);
					exit;
				}
			} elseif ($current_ts > $expiry_date_ts) {
				$is_errors = true;
				$errors .= ACCOUNT_EXPIRED_MSG . "<br>";
			} else {
				$is_errors = true;
				$errors .= ACCOUNT_APPROVE_ERROR . "<br>";
			}
		}
		else
		{
			$is_errors = true;
			if ($user_id) {
				$errors .= NO_RECORDS_MSG . "<br>";
			} else {
				$errors .= LOGIN_PASSWORD_ERROR . "<br>";
			}
		}
		if ($is_errors) {
			setcookie("cookie_user_login");
			setcookie("cookie_user_password");
		}
		return (!$is_errors);
	}

	function user_logout()
	{
		global $settings;

		set_session("session_user_id", "");
		set_session("session_new_user_id", "");
		set_session("session_new_user_type_id", "");
		set_session("session_new_user", "");
		set_session("session_user_type_id", "");
		set_session("session_user_login", "");
		set_session("session_user_name", "");
		set_session("session_user_email", "");
		set_session("session_discount_type", "");
		set_session("session_discount_amount", "");
		set_session("session_price_type", "");
		set_session("session_user_info", "");
		if (get_setting_value($settings, "logout_cart_clear", 0) == 1) {
			set_session("shopping_cart", "");
			set_session("session_coupons", "");
		}
		// update shopping cart if it's available
		$shopping_cart = get_session("shopping_cart");
		if (is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
			include_once ("./includes/shopping_cart.php");
			recalculate_shopping_cart();					
			// check if any coupons can be added or removed
			check_coupons();
		}
		setcookie("cookie_user_login");
		setcookie("cookie_user_password");
	}

	function auto_user_login()
	{
		// automatically login customer
		$session_user_id = get_session("session_user_id");
		if (!$session_user_id) {
			$cookie_login = get_cookie("cookie_user_login");
			$cookie_password = get_cookie("cookie_user_password");
			if ($cookie_login && $cookie_password) {
				user_login($cookie_login, $cookie_password, "", false, "", false, $errors);
			}
		}
	}

	function update_user_status($user_id, $status_id)
	{
		global $db, $table_prefix, $settings;

		$current_date = va_time();
		$user_ip = get_ip();
		$admin_id = get_session("session_admin_id");
		// update user status
		$sql  = " UPDATE " . $table_prefix . "users SET ";
		$sql .= " is_approved=" . $db->tosql($status_id, INTEGER) . ",";
		if ($admin_id) {
			$sql .= " admin_modified_date=" . $db->tosql($current_date, DATETIME) . ", ";
			$sql .= " admin_modified_ip=" . $db->tosql($user_ip, TEXT);
		} else {
			$sql .= " modified_date=" . $db->tosql($current_date, DATETIME) . ", ";
			$sql .= " modified_ip=" . $db->tosql($user_ip, TEXT);
		}
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);

		// get products settings for user
		$product_settings = array();
		$sql  = " SELECT user_type_id ";
		$sql .= " FROM " . $table_prefix . "users ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$type_id = $db->f("user_type_id");
			$setting_type = "user_product_" . $type_id;
			$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$db->query($sql);
			while ($db->next_record()) {
				$product_settings[$db->f("setting_name")] = $db->f("setting_value");
			}
		}

		$activate_products = get_setting_value($product_settings, "activate_products", 0);
		$deactivate_products = get_setting_value($product_settings, "deactivate_products", 0);
		if ($status_id == 1 && $activate_products == 1) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=1 ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		} elseif ($status_id == 0 && $deactivate_products == 1) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=0 ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
	}

	function get_friend_info() 
	{
		global $db, $table_prefix, $site_id;

		$user_id = get_session("session_user_id");
		$friend_code = get_session("session_friend");
		$friend_user_id = get_session("session_friend_id");
		$friend_type_id = get_session("session_friend_type_id");
		if (strlen($friend_code) && !strlen($friend_user_id)) {
			$sql  = " SELECT u.user_id,u.user_type_id FROM (";
			if (isset($site_id)) { $sql .= "("; }
			$sql .= $table_prefix . "users u";
			$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON ut.type_id=u.user_type_id)";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites s ON s.type_id=ut.type_id)";
			}
			$sql .= " WHERE (u.nickname=" . $db->tosql($friend_code, TEXT);
			$sql .= " OR u.login=" . $db->tosql($friend_code, TEXT) . ") ";
			if (isset($site_id)) {
				$sql .= " AND (ut.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";			
			} else {
				$sql .= " AND ut.sites_all=1";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$friend_user_id = $db->f("user_id");
				$friend_type_id = $db->f("user_type_id");
			}

			if ($friend_user_id == $user_id) {
				// user can't use himself as his own friend
				$friend_user_id = 0; $friend_type_id = 0;
			}
			set_session("session_friend_id", $friend_user_id);
			set_session("session_friend_type_id", $friend_type_id);
		}
		return $friend_user_id;
	}

	function sms_send_allowed($cell_phone_number)
	{
		global $settings, $db, $table_prefix;
		$user_id = get_session("session_user_id");
		if ($user_id) {
			$user_info = get_session("session_user_info");
			$is_sms_allowed = get_setting_value($user_info, "is_sms_allowed", 0);
		} else {
			$is_sms_allowed = get_setting_value($settings, "is_sms_allowed", 0);
		}
		if ($is_sms_allowed == 2) {
			// check if number in allowed list
			$cell_phone_number = preg_replace("/[^\d]/", "", $cell_phone_number);
			$sql = " SELECT cell_phone_id FROM " . $table_prefix . "allowed_cell_phones WHERE cell_phone_number=" . $db->tosql($cell_phone_number, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$is_sms_allowed = 1;
			} else {
				$sql = " SELECT is_sms_allowed FROM " . $table_prefix . "users WHERE cell_phone=" . $db->tosql($cell_phone_number, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$is_sms_allowed = $db->f("is_sms_allowed");
				} else {
					$is_sms_allowed = 0;
				}
			}
		}

		return $is_sms_allowed;
	}

	// Trancate text to desired length.
	// The last word is not trancated, so the length of result string may be greater than param $length
	function trancate_to_word($text, $length)
	{
		return preg_replace('/(^.{'.$length.'}.*?\s).+/is', '$1', $text);
	}


	function prepare_custom_friendly_urls()
	{
		global $db, $table_prefix, $settings, $custom_friendly_urls, $site_id;
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		$current_version = va_version();
		if ($friendly_urls && (compare_versions($current_version, "3.3.5") == 1)) {
			$sql  = " SELECT u.script_name, u.friendly_url ";
			if (isset($site_id)) {
				$sql .= " FROM (" . $table_prefix . "friendly_urls u";
				$sql .= " LEFT JOIN  " . $table_prefix . "friendly_urls_sites us ON (us.friendly_id=u.friendly_id AND u.sites_all=0))";
				$sql .= " WHERE u.sites_all=1 OR us.site_id=" . $db->tosql($site_id, INTEGER, true, false);
			} else {
				$sql .= " FROM " . $table_prefix . "friendly_urls u ";			
				$sql .= " WHERE u.sites_all=1";
			}
			$db->query($sql);
			while ($db->next_record()) {
				$custom_friendly_urls[$db->f("script_name")] = $db->f("friendly_url") . $friendly_extension;
			}
		}
		return $custom_friendly_urls;
	}

	function get_custom_friendly_url($script_name)
	{
		global $custom_friendly_urls;
		return (is_array($custom_friendly_urls) && isset($custom_friendly_urls[$script_name]) && strlen($custom_friendly_urls[$script_name])) ? $custom_friendly_urls[$script_name] : $script_name;
	}
	
	function get_forced_friendly_url($script_url, &$db)
	{
		global $table_prefix, $settings;
		
		$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");	
		if (!$friendly_urls) return $script_url;
		
		$friendly = get_custom_friendly_url($script_url);
		if ($friendly != $script_url) return $friendly;
		
		$parsed_url  = parse_url($script_url);
		$script_name = $parsed_url["path"];
		if (isset($parsed_url["query"])) {
			parse_str($parsed_url["query"], $script_vars);
		} else {
			return get_custom_friendly_url($script_name);
		}
		
		$friendly = "";
		switch ($script_name) {
			case "products.php": 
				if (isset($script_vars["category_id"])) {
					$sql  = " SELECT friendly_url FROM " . $table_prefix . "categories ";
					$sql .= " WHERE category_id=" . $db->tosql($script_vars["category_id"], INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						unset($script_vars["category_id"]);		
						$friendly = $db->f("friendly_url") . $friendly_extension;
					}
				} elseif (isset($script_vars["manf"])) {
					$sql  = " SELECT friendly_url FROM " . $table_prefix . "manufacturers ";
					$sql .= " WHERE manufacturer_id=" . $db->tosql($script_vars["manf"], INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						unset($script_vars["manf"]);		
						$friendly = $db->f("friendly_url") . $friendly_extension;
					}
				} else {
					$friendly = get_custom_friendly_url("products.php");
				}
			break;
			case "product_details.php":
				if (isset($script_vars["item_id"])) {
					$sql  = " SELECT friendly_url FROM " . $table_prefix . "items ";
					$sql .= " WHERE item_id=" . $db->tosql($script_vars["item_id"], INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						unset($script_vars["item_id"]);		
						$friendly = $db->f("friendly_url") . $friendly_extension;
					}
				} else {
					$friendly = get_custom_friendly_url("product_details.php");
				}
			break;
		}		
		
		if ($friendly) {			
			$friendly_vars = "";
			if ($script_vars) {
				foreach ($script_vars AS $key => $val) {
					$friendly_vars .= ($friendly_vars) ? "&" : "?";
					$friendly_vars .= $key . "=" . urlencode($val);
				}
			}
			return $friendly . $friendly_vars;			
		} else {
			return $script_url;
		}
	}
	
	function check_selected_url($script_url, $match_type = 2)
	{
		global $current_page;
		$request_page = get_request_page();
		if (!isset($current_page)) { $current_page = $request_page; }
		$request_uri_path = get_request_path();

		$parsed_url  = parse_url($script_url);
		if (isset($parsed_url["path"])) {
			$script_name = $parsed_url["path"];
			if (isset($parsed_url["query"])) {
				parse_str($parsed_url["query"], $script_vars);
			} else {
				$script_vars = array();
			}
		} else {
			$script_name = $script_url;
		}
		
		$url_matched = false;
		if ($match_type > 0) {
			if ($script_name  == $request_page || $script_name  == $current_page || $script_name  == $request_uri_path) {
				$url_matched = true;
			}
			if ($url_matched && $match_type == 2 && $script_vars) {
				foreach ($script_vars AS $key => $var) {
					if(get_param($key) != $var) {
						$url_matched = false;
						break;
					}
				}
			}
		}
		return $url_matched;
	}

 	function compare_versions($version1, $version2)
	{
		$first_numbers = explode(".", $version1);
		$second_numbers = explode(".", $version2); 

		if (count($first_numbers) > count($second_numbers)) {
			for ($i = 0; isset($first_numbers[$i]); $i++) {
				if (!isset($second_numbers[$i])) $second_numbers[$i] = "0";
			}
		} else {
			for ($i = 0; isset($second_numbers[$i]); $i++) {
				if (!isset($first_numbers[$i])) $first_numbers[$i] = "0";
			}
		}
			
		foreach ($first_numbers as $key => $value) {
			if ($first_numbers[$key] > $second_numbers[$key]) {
				return 1;
			} elseif ($first_numbers[$key] < $second_numbers[$key]) {
				return 2;
			}
		}
	
		return 0;
	}

	function friendly_url_redirect($friendly_url, $friendly_params)
	{
		global $is_friendly_url, $settings;
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_url_redirect = get_setting_value($settings, "friendly_url_redirect", 0);
		if (!$is_friendly_url && $friendly_urls && $friendly_url_redirect && $friendly_url && sizeof($_POST) == 0) {
			$friendly_extension = get_setting_value($settings, "friendly_extension", "");
			$query_string  = get_query_string($_GET, $friendly_params);
			$friendly_url .= $friendly_extension.$query_string;
			header("HTTP/1.1 301 Moved Permanently");
			header("Status: 301 Moved Permanently");
			header("Location: " . $friendly_url, true, 301);
			exit;
		}
	}

	/**
	 * Return escaped string ready to use in XML code
	 *
	 * @param string $str
	 * @return string
	 */
	function xml_escape_string($str) 
	{
		return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES));
	}
	
	/**
	 * Check whether the local image file exists
	 *
	 * @param string $check_image
	 * @return boolean
	 */
	function image_exists($check_image)
	{
		global $root_folder_path;

		if (strlen($check_image)) {
			if (!preg_match("/^http(s)?:\/\//", $check_image)) {
				while (strpos($check_image, "//") !== false) {
					$check_image = str_replace("//", "/", $check_image);
				}
				if (substr($check_image, 0, 1) == "/") {
					$check_image = substr($check_image, 1);
					$request_uri = get_var("REQUEST_URI");
					$current_path = substr($request_uri, 0, strpos($request_uri, "?"));
					while (strpos($current_path, "//") !== false) {
						$current_path = str_replace("//", "/", $current_path);
					}
					if (substr($current_path, 0, 1) == "/") {
						$current_path = substr($current_path, 1);
					}
					$current_path_parts = explode("/", $current_path);
					$check_image = str_repeat("../", sizeof($current_path_parts) - 1) . $check_image;
				} else {
					$check_image = $root_folder_path . $check_image;
				}
				if (!file_exists($check_image)) { 
					return false;
				}
			}
		}
		return true;
	}

	function prepare_user_name(&$full_name, &$first_name, &$last_name)
	{	
		if (strlen($full_name) && !strlen($first_name) && !strlen($last_name)) {
			$name = $full_name;
			$name_parts = explode(" ", $name, 2);
			if (sizeof($name_parts) == 2) {
				$first_name = $name_parts[0];
				$last_name = $name_parts[1];
			} else {
				$first_name = $name_parts[0];
				$last_name = "";
			}
		} elseif (!strlen($full_name) && (strlen($first_name) || strlen($last_name))) {
			$full_name = trim($first_name . " " . $last_name);
		}
	}	

	function prepare_js_value($js_value)
	{
		$find = array("%", "+", "&", "\"", "'", "\n", "\r", "=", "|", "#");
		$replace = array("%25", "%2B", "%26", "%22", "%27", "%0A", "%0D", "%3D", "%7C", "%23");
		$js_value = str_replace($find, $replace, $js_value);
		return $js_value;
	}

	function is_utf8($string) 
	{
		return preg_match('/^(?:
			[\x09\x0A\x0D\x20-\x7E]              # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		)*$/xs', $string);
	} 

	function charset_decode_utf8($string) 
	{ 
		// avoid using 0xA0 (\240) in regexp ranges. RH73 does not like that
		if (!preg_match("/[\200-\237]/", $string) && !preg_match("/[\241-\377]/", $string)) {
			// if there are only 8-bit characters return string
			return $string; 
		}

		// decode three byte unicode characters 
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
			"'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string); 

    // decode two byte unicode characters 
		$string = preg_replace("/([\300-\337])([\200-\277])/e", 
			"'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string); 

		return $string; 
	}

	function sql_explain($sql) {
		global $db;
		$eol = get_eol();
				
		$debug_output  = "";
		$debug_output .= $sql . $eol;
		
		$db->query("EXPLAIN $sql ");
		
		if ($db->next_record()) {
			
			$debug_output .= "<table>" . $eol;
			
			$fields = array_keys($db->Record);
			$debug_output .= "<tr>" . $eol;
			for ($i=0,$ic=count($fields); $i<$ic; $i++) {
				if(intval($fields[$i])) {
					unset ($fields[$i]);
				} else {
					$debug_output .= "<th>" . $fields[$i] . "</th>" . $eol;
				}
			}
			$debug_output .= "</tr>" . $eol;
			
			do {
				$debug_output .= "<tr>" . $eol;
				foreach ($fields AS $field) {
					$debug_output .= "<td>" . $db->f($field) . "</td>" . $eol;
				}
				$debug_output .= "</tr>" . $eol;
			} while ($db->next_record());
			
			$debug_output .= "</table>" . $eol;
		}
		return $debug_output;
	}
	
	function format_binary_for_sql($field_1, $field_2) {
		global $db_type;
		
		if ($db_type == "postgre") {
			return $field_1 . "&" . $field_2 . " > 0 ";			
		} else {
			return $field_1 . "&" . $field_2;
		}
	}
	
	function set_cache($data, $isset = 0, $cache_type="0", $cache_name="0", $cache_parameter="0") {
		global $db, $table_prefix;
		$current_version = va_version();
		if (compare_versions($current_version, "3.6.32") == 2) {
			return $data;
		}

		if ($isset) { //echo 1;
			$sql  = " UPDATE ".$table_prefix."caches ";
			$sql .= " SET cache_data = ".$db->tosql($data,TEXT).",";
			$sql .= " cache_date = ".$db->tosql(strtotime(date("Y-m-d H:i:s")),DATE);
			$sql .= " WHERE cache_type = ".$db->tosql($cache_type,TEXT);
			$sql .= " AND cache_name = ".$db->tosql($cache_name,TEXT);
			$sql .= " AND cache_parameter = ".$db->tosql($cache_parameter,TEXT);
			$db->query($sql);
		} else { //echo 2;
			$sql = " INSERT INTO ".$table_prefix."caches (cache_data,cache_date,cache_type,cache_name,cache_parameter) ";
			$sql.= " VALUES (".$db->tosql($data,TEXT).",";
			$sql.= $db->tosql(strtotime(date("Y-m-d H:i:s")),DATE).",";
			$sql.= $db->tosql($cache_type,TEXT).",";
			$sql.= $db->tosql($cache_name,TEXT).",";
			$sql.= $db->tosql($cache_parameter,TEXT).")";
			$db->query($sql);
		}
		return $data;
	}

	function get_cache($hour=24,$daily = 0,$cache_type="0",$cache_name="0",$cache_parameter="0") {
		global $db,$table_prefix;
		$current_version = va_version();
		if (compare_versions($current_version, "3.6.32") == 2) {
			return array(0);
		}
		$sql  = " SELECT * FROM ".$table_prefix."caches WHERE cache_type = ".$db->tosql($cache_type,TEXT);
		$sql .= " AND cache_name = ".$db->tosql($cache_name,TEXT);
		$sql .= " AND cache_parameter = ".$db->tosql($cache_parameter,TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$date = $db->f("cache_date");
			if (!$daily) {
				if (strtotime($date) + $hour * 60 * 60 > strtotime(date("Y-m-d H:i:s"))) {
					return $db->f("cache_data");
				} else {
					return array(1);
				}
			} else {
				if (strtotime(date("Y-m-d") . " 00:00:00") < strtotime($date) + 24 * 60 * 60) {
					return $db->f("cache_data");
				} else {
					return array(1);
				}
			}
		}
		return array(0);
	}

 	function comp_vers($version1, $version2)
	{
		$first_numbers = explode(".", $version1);
		$second_numbers = explode(".", $version2); 

		if (count($first_numbers) > count($second_numbers)) {
			for ($i = 0; isset($first_numbers[$i]); $i++) {
				if (!isset($second_numbers[$i])) $second_numbers[$i] = "0";
			}
		} else {
			for ($i = 0; isset($second_numbers[$i]); $i++) {
				if (!isset($first_numbers[$i])) $first_numbers[$i] = "0";
			}
		}
			
		foreach ($first_numbers as $key => $value) {
			if ($first_numbers[$key] > $second_numbers[$key]) {
				return 1;
			} elseif ($first_numbers[$key] < $second_numbers[$key]) {
				return 2;
			}
		}
	
		return 0;
	}

	function set_link_tag($href, $rel, $type)
	{
		set_head_tag("link", array("href" => $href, "rel" => $rel, "type" => $type), "href", 1); 
	}

	function set_script_tag($src, $language = "JavaScript", $type = "text/javascript")
	{
		set_head_tag("script", array("src" => $src, "language" => $language, "type" => $type), "src", 2); 
	}

	function set_head_tag($tag_name, $attributes, $unique_attribute = "", $close_type = 0) 
	{
		global $t, $head_tags;
		$attribute_value = ($unique_attribute) ? $attributes[$unique_attribute] : "";
		if (!$attribute_value || !isset($head_tags[$tag_name]) || !isset($head_tags[$tag_name][$attribute_value])) {
			if (strlen($attribute_value)) {
				$head_tags[$tag_name][$attribute_value] = true;
			}
			$head_tag = "<".$tag_name;
			foreach ($attributes as $attribute_name => $attribute_value) {
				if (strlen($attribute_value)) {
					$head_tag .= " ".$attribute_name."=\"".$attribute_value."\"";
				}
			}
			if ($close_type == 1) {
				$head_tag .= " />";
			} else if ($close_type == 2) {
				$head_tag .= "></".$tag_name.">";
			} else {
				$head_tag .= ">";
			}
			$t->set_var("head_tag", $head_tag);
			$t->parse("head_tags", true);
		}
	}

function va_mail($mail_to, $mail_subject, $mail_body, $mail_headers = "", $attachments = "")
{
	global $settings;

	if (!strlen($mail_to)) { 
		return false;
	}

	$mail_type = get_setting_value($mail_headers, "mail_type", 0);
	$mail_from = get_setting_value($mail_headers, "from", $settings["admin_email"]);
	$email_additional_headers = get_setting_value($settings, "email_additional_headers", "");
	$email_additional_parameters = get_setting_value($settings, "email_additional_parameters", "");

	$eol = get_eol();

	// set additional mail headers
	$add_mail_headers = preg_split("/[\r\n]+/", $email_additional_headers, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($add_mail_headers as $header) {
		$header = explode(":", $header);
		if (sizeof($header) == 2) {
			$mail_headers = array_merge(array(trim($header[0]) => trim($header[1])), $mail_headers);
		}
	}

	if (is_array($attachments) && sizeof($attachments) > 0) {
		$boundary = "--va_". md5(va_timestamp()) . "_" . va_timestamp(); 
		$mail_headers["Content-Type"] = "multipart/mixed; boundary=\"" . $boundary . "\"";
		if (isset($mail_headers["mail_type"])) {
			unset($mail_headers["mail_type"]);
		}

		$original_body = $mail_body;
		$mail_body  = "This is a multi-part message in MIME format." . $eol . $eol;
		$mail_body .= "--" . $boundary . $eol;
		if ($mail_type) {
			$mail_body .= "Content-Type: text/html;" . $eol;
		} else {
			$mail_body .= "Content-Type: text/plain;" . $eol;
		}
		$mail_body .= "\tcharset=\"" . CHARSET . "\"". $eol;
		$mail_body .= "Content-Transfer-Encoding: 7bit" . $eol;
		$mail_body .= $eol;
		$mail_body .= $original_body;
		$mail_body .= $eol . $eol;

		for ($at = 0; $at < sizeof($attachments); $at++) {
			$attachment_info = $attachments[$at];
			if (!is_array($attachment_info)) {
				$filepath = $attachment_info;
				$attachment_info = array(basename($filepath), $filepath, "");
			} elseif (sizeof($attachment_info) == 1) {                                                    
				$filepath = $attachment_info[0];
				$attachment_info = array(basename($filepath), $filepath, "");
			} 

			$filename = $attachment_info[0];
			if (!$filename) { $filename = basename($filepath); }
			if (!$filename) { $filename = "noname.txt"; }
			$filepath = $attachment_info[1];
			$filetype = isset($attachment_info[2]) ? $attachment_info[2] : "";
			if (preg_match("/^(http|https|ftp|ftps):\/\//", $filepath)) {
				$is_remote_file = true;
			} else {
				$is_remote_file = false;
			}
			$filebody = "";
			if ($filetype == "pdf") {
				$filebody = $pdf->get_buffer();
			} elseif ($filetype == "buffer") {
				$filebody = $filepath;
			} elseif ($filetype == "fp") {
				// read entire file from file pointer
				while (!feof($fp)) {
					$filebody .= fread($fp, 8192);
				}
			} elseif ($is_remote_file || (@file_exists($filepath) && !@is_dir($filepath))) {
				// read entire file into filebody
				$fp = fopen($filepath, "rb");
				while (!feof($fp)) {
					$filebody .= fread($fp, 8192);
				}
				fclose($fp);
			}

			if ($filebody) {
				$file_base64 = chunk_split(base64_encode($filebody)); 

				$mail_body .= "--" . $boundary . $eol;
				if (preg_match("/\.gif$/", $filename)) {
					$mail_body .= "Content-Type: image/gif;" . $eol;
				} elseif (preg_match("/\.pdf$/", $filename)) {
					$mail_body .= "Content-Type: application/pdf;" . $eol;
				} else {
					$mail_body .= "Content-Type: application/octet-stream;" . $eol;
				}
				$mail_body .= "\tname=\"" . $filename . "\"" . $eol;
				$mail_body .= "Content-Transfer-Encoding: base64" . $eol;
				$mail_body .= "Content-Disposition: attachment;" . $eol;
				$mail_body .= "\tfilename=\"" . $filename . "\"" . $eol;
				$mail_body .= $eol;
				$mail_body .= $file_base64;
				$mail_body .= $eol . $eol;
			}
		}
		// end multipart message
		$mail_body .= "--" . $boundary . "--" . $eol;
		$mail_body .= $eol;
	} else {
		$mail_headers["mail_type"] = $mail_type;
	}

	$smtp_mail = get_setting_value($settings, "smtp_mail", 0);
	if ($smtp_mail) {
		$smtp_host = get_setting_value($settings, "smtp_host", "127.0.0.1");
		$smtp_port = get_setting_value($settings, "smtp_port", 25);
		$smtp_timeout = get_setting_value($settings, "smtp_timeout", 30);
		$smtp_username = get_setting_value($settings, "smtp_username", "");
		$smtp_password = get_setting_value($settings, "smtp_password", "");

		$errors = "";

		$socket = @fsockopen($smtp_host, $smtp_port, $errno, $error, $smtp_timeout);
		if (!$socket) {
			$errors = $error;
			return false;
		}

		// read server reply
		$response = smtp_check_response($socket, 220, $error);
		if ($error) {
			$errors = $error;
			return false;
		}

		$smtp_username = get_setting_value($settings, "smtp_username", "");
		$smtp_password = get_setting_value($settings, "smtp_password", "");

		if (strlen($smtp_username) && strlen($smtp_password))
		{ 
			fputs($socket, "EHLO " . $smtp_host . "\r\n");
			smtp_check_response($socket, "250", $error);
			$errors .= $error;

			fputs($socket, "AUTH LOGIN\r\n");
			smtp_check_response($socket, "334", $error);
			$errors .= $error;

			fputs($socket, base64_encode($smtp_username) . "\r\n");
			smtp_check_response($socket, "334", $error);
			$errors .= $error;

			fputs($socket, base64_encode($smtp_password) . "\r\n");
			smtp_check_response($socket, "235", $error);
			$errors .= $error;
		}
		else
		{
			fputs($socket, "HELO " . $smtp_host . "\r\n");
			smtp_check_response($socket, "250", $error);
			$errors .= $error;
		}

		if ($errors) { return false; }

		fputs($socket, "MAIL FROM: <" . $mail_from . ">\r\n");
		smtp_check_response($socket, "250", $error);
		if ($error) {
			$errors = $error; return false;
		}

		if (!isset($mail_headers["to"])) {
			$mail_headers["to"] = $mail_to;
		}
		$header_names = array("to", "cc", "bcc");
		for ($hf = 0; $hf < sizeof($header_names); $hf++) {
			$recipients_string = get_setting_value($mail_headers, $header_names[$hf], "");
			$recipients_string = str_replace(";", ",", $recipients_string);
			if ($recipients_string) {
				$recipients_values = explode(",", $recipients_string);
				for ($i = 0; $i < sizeof($recipients_values); $i++) {
					$recipient_email = "";
					$recipient_value = $recipients_values[$i];
					if (preg_match("/<([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)>/i", $recipient_value, $match)) {
						$recipient_email = $match[1];
					} elseif (preg_match("/\s*([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)\s*/i", $recipient_value, $match)) {
						$recipient_email = trim($match[1]);
					}
					if ($recipient_email) {
						fputs($socket, "RCPT TO: <" . $recipient_email . ">\r\n");
						smtp_check_response($socket, "250", $error);
						$errors .= $error;
					}
				}
			}
		}

		if ($errors) {
			return false;
		}

		// Preparing for sending data
		fputs($socket, "DATA\r\n");
		smtp_check_response($socket, "354", $error);
		if ($error) {
			$errors = $error; return false;
		}

		// Send subject
		fputs($socket, "Subject: " . $mail_subject . "\r\n");
  
		// Add other headers 
		$headers_string = email_headers_string($mail_headers, "\r\n");
		fputs($socket, $headers_string. "\r\n\r\n");
  
		// Send the mail body 
		fputs($socket, $mail_body. "\r\n.\r\n");
		smtp_check_response($socket, "250", $error);
		if ($error) {
			$errors = $error; return false;
		}

		fputs($socket, "QUIT\r\n");
		fclose($socket);

		return true;
	} else {
		$headers_string = email_headers_string($mail_headers);
		$safe_mode = (strtolower(ini_get("safe_mode")) == "on" || intval(ini_get("safe_mode")) == 1) ? true : false;
		if ($safe_mode) {
			return @mail($mail_to, $mail_subject, $mail_body, $headers_string);
		} else {
			return @mail($mail_to, $mail_subject, $mail_body, $headers_string, $email_additional_parameters);
		}
	} 
}

function smtp_check_response($socket, $check_code, &$error) 
{
	$response = ""; $response_code = "";
	do {
		$line = fgets($socket, 512);
		if (preg_match("/^(\d{3})\s/", $line, $matches)) {
			$response_code = $matches[1];
		}
		$response .= $line;
	} while ($line !== false && !$response_code);

	if ($check_code == $response_code) {
		return $response;
	} else {
		if ($response) {
			$error = "Error while sending email. Server response: " . $response . "\n";
		} else {
			$error = "No response from mail server.\n";
		}
		return false;
	}
}

function save_log_file($filename, $content)
{
	$fp = @fopen($filename, "a");
	if ($fp) {
		$current_date = date("l jS \of F Y h:i:s A");
		@fwrite($fp, "-------------------------------\n");
		@fwrite($fp, $current_date."\n");
		@fwrite($fp, "-------------------------------\n");
		@fwrite($fp, $content);
		@fclose($fp);
	}
}	
?>
