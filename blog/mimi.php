<?php
require(dirname(__FILE__) . '/madmimi/MadMimi.class.php');

$mailer = new MadMimi('janna@cuttingedgestencils.com', '24cb537d19cf1169d2074ba06984ab4a'); 

if( isset($_REQUEST['email']) ){
    $list = isset($_REQUEST['list']) ? $_REQUEST['list'] : 'Cutting Edge Stencils List' ;
    $user = array('email' => $_REQUEST['email'], 'add_list' => $list);
    $mailer->AddUser($user);
}
?>