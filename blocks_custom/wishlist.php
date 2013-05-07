<?php
include_once("../includes/common_functions.php");
include_once("../includes/var_definition.php");
include_once("../includes/constants.php");
include_once("../includes/db_$db_lib.php");
session_start();

$db = new VA_SQL();
$db->DBType      = $db_type;
$db->DBDatabase  = $db_name;
$db->DBHost      = $db_host;
$db->DBPort      = $db_port;
$db->DBUser      = $db_user;
$db->DBPassword  = $db_password;
$db->DBPersistent= $db_persistent;

if(isset($_REQUEST['action'])&& $_REQUEST['action'] == "add" && isset($_REQUEST['item_id']) && is_numeric($_REQUEST['item_id']) && isset($_REQUEST['item_name']) && isset($_REQUEST['quantity']) && isset($_REQUEST['price']) ){
	
	$duplicates = false;

	if( isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) ) {
		$user_id = get_session("session_user_id") ? get_session("session_user_id") : $_REQUEST['user_id'];
		$product_dup_query = "SELECT * FROM va_saved_items WHERE item_id = ".$_REQUEST['item_id']." AND user_id = ".$user_id;
		$db->query($product_dup_query);
		$duplicates = $db->next_record();		
	} else {
		$id_query = "SELECT MAX(user_id) AS last_user_id FROM va_saved_items";
		$db->query($id_query);
		$db->next_record();
		$user_id = $db->f("last_user_id") < 444444444 ? 444444444 : $db->f("last_user_id") + 1;
	}
	
	if(!$duplicates){
        $sql = "INSERT INTO `va_saved_items` (`cart_item_id`, `site_id`, `item_id`, `cart_id`, `user_id`, `type_id`, `item_name`, `quantity`, `quantity_bought`, `price`,`date_added`) VALUES (NULL, 1, '".$_REQUEST['item_id']."', 0, '".$user_id."', '1', '".$_REQUEST['item_name']."', '".$_REQUEST['quantity']."', 0, '".$_REQUEST['price']."', NOW())";
	$db->query($sql);
	}
	$data = array('user_id' => $user_id, 'duplicates' => $duplicates);
	echo json_encode($data);
}
else if(isset($_REQUEST['action'])&& $_REQUEST['action'] == "show"){
	$user_id = get_session("session_user_id");
	if(!$user_id){
		if(isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != "new" ) {
			$user_id = $_REQUEST['user_id'];
		}
		else {
			echo "empty";
			exit; 
		}
	}
	$wishlist_content = "";
	$sql = "SELECT va_items.item_name AS name, va_items.friendly_url AS url, va_items.small_image AS image, va_saved_items.price AS price  FROM va_saved_items, va_items WHERE va_saved_items.user_id = ".$user_id." AND va_saved_items.item_id = va_items.item_id ORDER BY va_saved_items.date_added DESC";
	$db->query($sql);
	$cnt = 0;
	while( $db->next_record() && $cnt++ < 5){
		$wishlist_content .= '<div class="wishlistItem"><a href="/'.$db->f("url").'.html"><img src="/'.$db->f("image").'" alt="'.$db->f("name").'" /></a><h4><a href="/'.$db->f("url").'.html">'.$db->f("name").'</a></h4><b>$'.$db->f("price").'</b><a class="button small-button floatright" href="/'.$db->f("url").'.html">View > </a></div>';
	}
	echo strlen($wishlist_content) == 0 ? "empty" : $wishlist_content;
}
else {
	echo "Wrong or missing parameters";
	header('HTTP', true, 500);
}
?>
