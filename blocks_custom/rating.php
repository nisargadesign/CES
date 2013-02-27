<?php
if(isset($_REQUEST['id']) && isset($_REQUEST['rating']) && is_numeric($_REQUEST['id']) && is_numeric($_REQUEST['rating']) ){
	include_once("../includes/var_definition.php");
	include_once("../includes/constants.php");
	include_once("../includes/db_$db_lib.php");

	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	
        $sql = "INSERT INTO `va_reviews` (`review_id`, `item_id`, `user_id`, `admin_id`, `recommended`, `approved`, `rating`, `summary`, `user_name`, `user_email`, `remote_address`, `comments`, `admin_id_approved_by`, `admin_id_modified_by`, `date_added`, `date_modified`, `date_approved`) VALUES (NULL, '".$_REQUEST['id']."', '0', '0', '1', '1', '".$_REQUEST['rating']."', 'Web', 'Visitor', 'visitor@cuttingedgestencils.com', '".$_SERVER['REMOTE_ADDR']."', 'Fantastic product', NULL, NULL, NOW(), NOW(), NOW())";
	$db->query($sql);
}
else {
	echo "Wrong or missing parameters";
	header('HTTP', true, 500);
}
?>
