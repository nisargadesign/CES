<?php
if(isset($_REQUEST['id']) && isset($_REQUEST['rating']) && is_numeric($_REQUEST['id']) && is_numeric($_REQUEST['rating']) ){
	include_once("../includes/var_definition.php");
	include_once("../includes/constants.php");
	include_once("../includes/db_$db_lib.php");
	include_once("../includes/common_functions.php");

	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	$product_id = $db->tosql($_REQUEST['id'], INTEGER);
        $sql = "INSERT INTO `va_reviews` (`review_id`, `item_id`, `user_id`, `admin_id`, `recommended`, `approved`, `rating`, `summary`, `user_name`, `user_email`, `remote_address`, `comments`, `admin_id_approved_by`, `admin_id_modified_by`, `date_added`, `date_modified`, `date_approved`) VALUES (NULL, '".$product_id."', '0', '0', '1', '1', '".$db->tosql($_REQUEST['rating'], INTEGER)."', 'Web', 'Visitor', 'visitor@cuttingedgestencils.com', '".$_SERVER['REMOTE_ADDR']."', 'Fantastic product', NULL, NULL, NOW(), NOW(), NOW())";
	$db->query($sql);
	$votes = get_db_value("SELECT COUNT(*) FROM ".$table_prefix."reviews WHERE approved=1 AND rating <> 0 AND item_id=".$product_id);
	$points = get_db_value("SELECT SUM(rating) FROM ".$table_prefix."reviews WHERE approved=1 AND rating <> 0 AND item_id=".$product_id);
	$sql = "UPDATE ".$table_prefix."items SET votes=".$votes.", points=".$points." WHERE item_id=".$product_id;
	$db->query($sql);
}
else {
	echo "Wrong or missing parameters";
	header('HTTP', true, 500);
}
?>
