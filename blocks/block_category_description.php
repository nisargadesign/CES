<?php



	$desc_image = get_setting_value($vars, "category_description_image", 3);

	$desc_type = get_setting_value($vars, "category_description_type", 2);



	$sql  = " SELECT category_name, short_description, full_description, ";

	$sql .= " image, image_alt, image_large, image_large_alt ";

	$sql .= " FROM " . $table_prefix . "categories WHERE category_id = " . $db->tosql($category_id, INTEGER);

	$db->query($sql);

	if ($db->next_record())

	{

		$category_name = get_translation($db->f("category_name"));

		$description = "";

		if ($desc_type == 2) {

			$description = get_translation($db->f("full_description"));

		} elseif ($desc_type == 1) {

			$description = get_translation($db->f("short_description"));

		}

		$image = ""; $image_alt = "";

		if ($desc_image == 3) {

			$image      = $db->f("image_large");

			$image_alt  = get_translation($db->f("image_large_alt"));

		} elseif ($desc_image == 2) {

			$image      = $db->f("image");

			$image_alt  = get_translation($db->f("image_alt"));

		}



		if (!strlen($image)) {

			$image_exists = false;

		} elseif (!image_exists($image)) {

			$image_exists = false;

		} else {

			$image_exists = true;

		}



		if (strlen($description) || $image_exists) 

		{

			$html_template = get_setting_value($block, "html_template", "block_category_description.html"); 

		  $t->set_file("block_body", $html_template);



			if ($image_exists) {

				if (preg_match("/^http\:\/\//", $image)) {

					$image_size = "";

				} else {

					$image_size = @GetImageSize($image);

					if (isset($restrict_categories_images) && $restrict_categories_images) { $image = "image_show.php?category_id=".$category_id."&type=large"; }

				}

				if (!strlen($image_alt)) { $image_alt = $category_name; }

				$t->set_var("alt", htmlspecialchars($image_alt));

				$t->set_var("src", htmlspecialchars($image));

				if (is_array($image_size)) {

					$t->set_var("width", "width=\"" . $image_size[0] . "\"");

					$t->set_var("height", "height=\"" . $image_size[1] . "\"");

				} else {

					$t->set_var("width", "");

					$t->set_var("height", "");

				}

				$t->sparse("image_large_block", false);

			} else {

				$t->set_var("image_large_block", "");

			}

	  

			$t->set_var("category_name", $category_name);

			$t->set_var("full_description", $description);



			$block_parsed = true;

			$t->parse("block_body", false);

		}

	}



?>