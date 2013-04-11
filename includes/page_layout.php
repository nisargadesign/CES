<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 4.0.5                                                ***
  ***      File:  page_layout.php                                          ***
  ***      Built: Fri Jan 28 01:45:24 2011                                 ***
  ***      Updated: Wed May 16 2012                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/




	// initialize template class

	$t = new VA_Template($settings["templates_dir"]);



	// get and set global values

	$site_url = get_setting_value($settings, "site_url", "");

	$secure_url = get_setting_value($settings, "secure_url", "");

	if ($is_ssl) {

		$absolute_url = $secure_url;

	} else {

		$absolute_url = $site_url;

	}

	$parsed_url = parse_url($site_url);

	$site_path = isset($parsed_url["path"]) ? $parsed_url["path"] : "/";

	

	$css_file = "";

	if (strlen(get_setting_value($settings, "style_name", ""))) {

		$css_file  = $absolute_url;

		$css_file .= "styles/" . get_setting_value($settings, "style_name");

		if (strlen(get_setting_value($settings, "scheme_name", ""))) {

			$css_file .= "_" . get_setting_value($settings, "scheme_name");

		}

		$css_file .= ".css";

	}



	$t->set_var("CHARSET", CHARSET);

	$t->set_var("meta_language", $language_code);

	$t->set_var("site_url", $site_url);

	$t->set_var("secure_url", $secure_url);

	$t->set_var("absolute_url", $absolute_url);

	$t->set_var("css_file", $css_file);

	if (isset($current_page)) {

		$t->set_var("current_href", $current_page);

	}



	// add google analytics code to hidden blocks
	$google_analytics = get_setting_value($settings, "google_analytics", 0);
	$google_tracking_code = get_setting_value($settings, "google_tracking_code", "");
	if ($google_analytics && $google_tracking_code) {
		$t->set_file("head_tag", "ga.html");
		$t->set_var("google_tracking_code", $google_tracking_code);
	}

	if (isset($debug_mode) && $debug_mode) {

		$t->set_var("debug_buffer", $debug_buffer);

	}



	// check page settings id	

	$sql  = " SELECT cps.* ";

	$sql .= " FROM (" . $table_prefix . "cms_pages_settings cps ";

	$sql .= " INNER JOIN " . $table_prefix . "cms_pages cp ON cp.page_id=cps.page_id) ";

	if (isset($cms_ps_id) && strlen($cms_ps_id)) {

		$sql .= " WHERE cps.ps_id=" . $db->tosql($cms_ps_id, INTEGER);

	} else {

		$sql .= " WHERE cp.page_code=" . $db->tosql($cms_page_code, TEXT);

		$sql .= " AND cps.key_code='' AND cps.key_type='' ";

		if (isset($site_id) && $site_id != 1) {

			$sql .= " AND (cps.site_id=1 OR cps.site_id=" . $db->tosql($site_id, INTEGER) . ") ";

		} else {

			$sql .= " AND cps.site_id=1 ";

		}

		$sql .= " ORDER BY cps.site_id DESC ";

	}

	$db->query($sql);

	if ($db->next_record()) {

		$ps_id = $db->f("ps_id");

		$layout_id = $db->f("layout_id");

		if (!isset($meta_title) || !strlen($meta_title)) {

			$meta_title = $db->f("meta_title");

		}

		if (!isset($meta_keywords) || !strlen($meta_keywords)) {

			$meta_keywords = $db->f("meta_keywords");

		}

		if (!isset($meta_description) || !strlen($meta_description)) {

			$meta_description = $db->f("meta_description");

		}

	} else {

		echo "Page <b>".$cms_page_code."</b> wasn't found.";

		exit;

	}



	// get layout template 

	$sql  = " SELECT * FROM " . $table_prefix . "cms_layouts ";

	$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);

	$db->query($sql);

	if ($db->next_record()) {

		$layout_template = $db->f("layout_template");

	}	



	// set layout

	$t->set_file("main", $layout_template);

	// set head tags

	set_head_tag("base", array("href"=>$absolute_url), "href", 1);

	if ($css_file) {

		set_link_tag($css_file, "stylesheet", "text/css");

	}

	set_script_tag("js/menu.js");

	set_script_tag("js/ajax.js");

	set_script_tag("js/blocks.js");

	set_script_tag("js/compare.js");

	set_script_tag("js/shopping.js");



	// get frames settings

	$frames = array();

	$sql  = " SELECT * FROM " . $table_prefix . "cms_frames ";

	$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);

	$db->query($sql);

	while ($db->next_record()) {

		$frame_id = $db->f("frame_id");

		$tag_name = $db->f("tag_name");

		// initialize all frames for layouts with empty values in case there are no saved settings

		$frames[$frame_id] = array(

			"tag_name" => $tag_name, "blocks" => 0, 

			"frame_style" => "", "html_frame_start" => "", 

			"html_between_blocks" => "", "html_before_block" => "", 

			"html_after_block" => "", "html_frame_end" => "", 

		);

	}	

	$sql  = " SELECT * FROM " . $table_prefix . "cms_frames_settings ";

	$sql .= " WHERE ps_id=" . $db->tosql($ps_id, INTEGER);

	$db->query($sql);

	while ($db->next_record()) {

		$frame_id = $db->f("frame_id");

		if (isset($frames[$frame_id])) {

			$tag_name = $frames[$frame_id]["tag_name"];

			$frames[$frame_id] = $db->Record;

			$frames[$frame_id]["tag_name"] = $tag_name;

			$frames[$frame_id]["blocks"] = 0;

			$t->set_var($tag_name."_style", $frames[$frame_id]["frame_style"]);

			$t->set_var($tag_name, $frames[$frame_id]["html_frame_start"]);

		}

	}	



	// get page blocks

	$page_blocks = array();

	$sql  = " SELECT cpb.pb_id, cb.block_code, cb.php_script, cpb.frame_id, cpb.block_key, ";

	$sql .= " cpb.tag_name, cpb.html_template, cpb.block_style, cpb.css_class ";

	$sql .= " FROM (" . $table_prefix . "cms_pages_blocks cpb ";

	$sql .= " INNER JOIN " . $table_prefix . "cms_blocks cb ON cpb.block_id=cb.block_id) ";

	$sql .= " WHERE cpb.ps_id=" . $db->tosql($ps_id, INTEGER);

	$sql .= " ORDER BY cpb.block_order ";

	$db->query($sql);

	while ($db->next_record()) {

		$pb_id = $db->f("pb_id");

		$page_blocks[$pb_id] = $db->Record;

		$page_blocks[$pb_id]["vars"] = array();

	}



	// get blocks variables

	$sql  = " SELECT cbs.pb_id, cbs.variable_name, cbs.variable_value ";

	$sql .= " FROM " . $table_prefix . "cms_blocks_settings cbs ";

	$sql .= " WHERE cbs.ps_id=" . $db->tosql($ps_id, INTEGER);

	$db->query($sql);

	while ($db->next_record()) {

		$pb_id = $db->f("pb_id");

		if (isset($page_blocks[$pb_id])) {

			$variable_name = $db->f("variable_name");

			$variable_value = $db->f("variable_value");

			if (isset($page_blocks[$pb_id]["vars"][$variable_name])) {

				if (is_array($page_blocks[$pb_id]["vars"][$variable_name])) {

					$page_blocks[$pb_id]["vars"][$variable_name][] = $variable_value;

				} else {

					$page_blocks[$pb_id]["vars"][$variable_name] = array($page_blocks[$pb_id]["vars"][$variable_name]);

					$page_blocks[$pb_id]["vars"][$variable_name][] = $variable_value;

				}

			} else {

				$page_blocks[$pb_id]["vars"][$variable_name] = $variable_value;

			}

		}

	}



	// parse blocks

	foreach ($page_blocks as $pb_id => $block) {

		$frame_id = $block["frame_id"];

		$frame_tag_name = $frames[$frame_id]["tag_name"];

		$php_script = $block["php_script"];

		$cms_block_code = $block["block_code"];

		$block_tag_name = $block["tag_name"];

		$html_template = $block["html_template"];

		$block_style = $block["block_style"];

		$block_class = $block["css_class"];

		$vars = array();

		$vars = $block["vars"];

		$vars["block_key"] = $block["block_key"];

		$vars["tag_name"] = $frames[$frame_id]["tag_name"];

		$block_parsed = false;

		$t->set_var("pb_id", $pb_id);

		$t->set_var("block_style", $block_style);

		$t->set_var("block_class", $block_class);

		$tag_name = strlen($block_tag_name) ? $block_tag_name : $frame_tag_name;



		include("./blocks/".$php_script);

		if ($block_parsed) {

			$frames[$frame_id]["blocks"]++;

			if ($frames[$frame_id]["blocks"] > 1) {

				$t->set_var("frame_code", $frames[$frame_id]["html_between_blocks"]);

				$t->copy_var("frame_code", $tag_name, true);

			}

			$t->set_var("frame_code", $frames[$frame_id]["html_before_block"]);

			$t->copy_var("frame_code", $tag_name, true);

			$t->copy_var("block_body", $tag_name, true);

			$t->set_var("frame_code", $frames[$frame_id]["html_after_block"]);

			$t->copy_var("frame_code", $tag_name, true);

		}

	}



	// close frames 

	foreach ($frames as $frame_id => $frame) {

		$tag_name = $frames[$frame_id]["tag_name"];

		$t->set_var("frame_code", $frames[$frame_id]["html_frame_end"]);

		$t->copy_var("frame_code", $tag_name, true);

	}	



	// check if auto data has to be applied

	if (!strlen($meta_title)) { 

		if (isset($auto_meta_title) && strlen($auto_meta_title)) {

			$meta_title = $auto_meta_title; 

		} else {

			$meta_title = get_setting_value($settings, "site_name"); 

		}

	}

	if (!strlen($meta_description) && isset($auto_meta_description)) { 

		$meta_description = $auto_meta_description; 

	}

	// set some meta data

	$t->set_var("meta_title", get_translation($meta_title));

	if ($meta_keywords) {

		set_head_tag("meta", array("name"=>"keywords","content"=>get_translation($meta_keywords)), "name", 1);

	}

	if ($meta_description) {

		set_head_tag("meta", array("name"=>"description","content"=>get_translation(get_meta_desc($meta_description))), "name", 1);

	}

	if (!isset($canonical_url) || !strlen($canonical_url)) {
		$canonical_url = "";
	}
	set_link_tag(htmlspecialchars($canonical_url), "canonical", "");
	
	
	if ($google_analytics && $google_tracking_code) {
		$t->parse_to("head_tag", "head_tags");
	}
	
	//Customization by Vital - Open Graph tags
	if (!isset($meta_OG_image) || !strlen($meta_OG_image)) { 
		$meta_OG_image = "http://www.cuttingedgestencils.com/images/small/Casablanca-stencil-design.jpg"; 
	}
	$meta_OG_image = (strpos($meta_OG_image, "cuttingedgestencils.com"))? $meta_OG_image : "http://www.cuttingedgestencils.com/".$meta_OG_image ;
	set_head_tag("meta", array("property"=>"og:type","content"=>"website"), "property", 1);
	set_head_tag("meta", array("property"=>"og:image","content"=>$meta_OG_image), "property", 1);
	set_head_tag("meta", array("property"=>"og:title","content"=>$meta_title), "property", 1);
	set_head_tag("meta", array("property"=>"og:url","content"=>"http://www.cuttingedgestencils.com/".$canonical_url), "property", 1);
	//END customization

//---------HOME STYLES

$t->set_var("contentStyle", "contentStyle");
$t->set_var("leftStyle", "leftStyle");
$t->set_var("centerStyle", "centerStyle");

if ($current_page == "index.php") 
		{
		$t->set_var("contentStyle", "contentStyleHome");
		$t->set_var("leftStyle", "leftStyleHome");
		$t->set_var("centerStyle", "centerStyle");	
		}
		
	// parse page content

	$t->pparse("main");



?>