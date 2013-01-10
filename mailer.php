<?php 

$myFile = "/var/www/vhosts/cesdev.net/cuttinge/subscription/subscription.txt";
$myFile2 = "/var/www/vhosts/cesdev.net/cuttinge/subscription/subscription_ALL.txt";

$stringData = $_REQUEST['email'] . "\n";


if ($_POST['source'] != 'contactBottom')
		{ 
$fh = fopen($myFile, 'a') or die("can't open file 1");

fwrite($fh, $stringData);

fclose($fh);

$fh2 = fopen($myFile2, 'a') or die("can't open file 2");

fwrite($fh2, $stringData);

fclose($fh2);
		}

if($_POST['source'] == 'newsletter')
	{
	$from = "customerservice@cuttingedgestencils.com"; 
	$headers = 'From: customerservice@cuttingedgestencils.com' . "\r\n" .
				'Reply-To: customerservice@cuttingedgestencils.com' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
		
	//$to = "design2@nisarga.net"; 
	//$to = "customerservice@cuttingedgestencils.com"; 
	
	$subject = "Web Site Newsletter Subscription"; 
	$email_field = $_POST['email']; 
	 
	$body = 'Email:' . $_POST['email']; 
	
	//mail($to, $subject, $body, $headers); 
	
	header("Location: " . "page.php?page=thank_you_subscr");
	exit;
	} 
	else if ($_POST['source'] == 'contactBottom')
		{ 
		$from = "customerservice@cuttingedgestencils.com"; 
		$headers = 'From: customerservice@cuttingedgestencils.com' . "\r\n" .
					'Reply-To: customerservice@cuttingedgestencils.com' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
			
		//$to = "design2@nisarga.net"; 
		$to = "customerservice@cuttingedgestencils.com";
		
		$subject = "Web Site Contact"; 
		$email_field = $_POST['email']; 
		 
		$body = 'Name:' . $_POST['name'] . "\r\n" .
				'Email:' . $_POST['email'] . "\r\n" .
				'Message:' . $_POST['message'] . "\r\n" . 
				'(footer)'; 
		
		mail($to, $subject, $body, $headers); 
		
		//header("Location: " . "page.php?page=thank_you_contac");
		header("Location: " . "thank-you-for-contacting-us.html");
		exit;
		} 
		else if ($_POST['source'] == 'contactPage')
			{ 
			$from = "customerservice@cuttingedgestencils.com"; 
			$headers = 'From: customerservice@cuttingedgestencils.com' . "\r\n" .
						'Reply-To: customerservice@cuttingedgestencils.com' . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
				
			//$to = "design2@nisarga.net"; 
			$to = "customerservice@cuttingedgestencils.com";
			
			$subject = "Web Site Contact"; 
			$email_field = $_POST['email']; 
			 
			$body = 'Name:' . $_POST['name'] . "\r\n" .
					'Email:' . $_POST['email'] . "\r\n" .
					'Found us:' . $_POST['found'] . "\r\n" .
					'Message:' . $_POST['message'] . "\r\n"; 
			
			mail($to, $subject, $body, $headers); 
			
			//header("Location: " . "page.php?page=thank_you_contac");
			header("Location: " . "thank-you-for-your-request.html");
			exit;
			}









?> 