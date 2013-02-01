<?php
//Customization by Vital - Mad Mimi integration
require(dirname(__FILE__) . '/blocks_custom/madmimi/MadMimi.class.php');
$mailer = new MadMimi('janna@cuttingedgestencils.com', '24cb537d19cf1169d2074ba06984ab4a'); 

if( isset($_REQUEST['email']) ){
    $list = isset($_REQUEST['list']) ? $_REQUEST['list'] : 'CES.COM contact form' ;
    $user = array('email' => $_REQUEST['email'], 'name' => $_REQUEST['name'], 'add_list' => $list);
    $mailer->AddUser($user);
}
//End customization
		
$subject = "Web Site Contact";

if($_POST['subject'] == 'stenciling'){
    $to = "melissa@cuttingedgestencils.com";
    $subject .= " - inquiry about stenciling";
}
    
if ($_POST['subject'] == 'order'){
    $to = "shipping@cuttingedgestencils.com";
    $subject .= " - order status, returns, exchanges or refunds";
}

if ($_POST['subject'] == 'other'){
$to = "melissa@cuttingedgestencils.com";
$subject .= " - general inquiry";
}				
           
//-----SEND THE MESSAGE
$from = "customerservice@cuttingedgestencils.com"; 
$headers = 'From: '. $_POST['email'] . "\r\n" .'Reply-To: '. $_POST['email'] . "\r\n" .'X-Mailer: PHP/' . phpversion();
$email_field = $_POST['email']; 
$body = 'Name: ' . $_POST['name'] . "\r\n" .'Email: ' . $_POST['email'] . "\r\n" . 'Message: ' . $_POST['message'] . "\r\n"; 
mail($to, $subject, $body, $headers); 
header("Location: " . "thank-you-for-contacting-us.html");
exit;

?>
