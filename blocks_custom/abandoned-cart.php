<?php
require(dirname(__FILE__) . '/madmimi/MadMimi.class.php');

$mailer = new MadMimi('janna@cuttingedgestencils.com', '24cb537d19cf1169d2074ba06984ab4a'); 

if( isset($_REQUEST['email']) ){
    $list = isset($_REQUEST['list']) ? $_REQUEST['list'] : 'Newsletter' ;
    $user = array('email' => $_REQUEST['email'], 'add_list' => $list);
    $mailer->AddUser($user);
    mail('vital@fineonly.com', 'Contact Added', $_REQUEST['email']." to ".$list);
}
else {
    include_once("../includes/common_functions.php");
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
    
    $sql  = "SELECT first_name, last_name, email FROM ". $table_prefix ."orders WHERE order_status IN (1,2,10) AND order_placed_date BETWEEN DATE_SUB(NOW(), INTERVAL 6 HOUR) AND DATE_SUB(NOW(), INTERVAL 3 HOUR)";
    $db->query($sql);
    $body = "";
    while ($db->next_record()) {
        if( strlen( $db->f("email") ) > 5 ){
            $user = array('email' => $db->f("email"), 'firstName' => $db->f("first_name"), 'lastName' => $db->f("last_name"), 'add_list' => 'Abandoned Cart List');
            $mailer->AddUser($user);
            $body .= $user['email']." - ".$user['firstName']." - ".$user['lastName']."\n";
        }
    }
    
    mail('vital@fineonly.com', 'Abandoned Cart Contacts', $body);
    //echo $body;
}
?>