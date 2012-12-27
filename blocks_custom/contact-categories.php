<?php                           
	include_once("../includes/var_definition.php");
	include_once("../includes/constants.php");
	include_once("../includes/db_$db_lib.php");
	header('Content-Type: text/html; charset=iso-8859-1');

	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	
        $sql  = " SELECT article_id, article_title, short_description  FROM va_articles WHERE article_id IN (SELECT article_id FROM va_articles_assigned WHERE category_id = 43) AND status_id = 2 ";
	$db->query($sql);
	?>
	<ul class="DetailTabs"><li class="tab tabActive"><a id="desc_a_tab" href="#" class="tab tabActive">Questions on Ordering, Shipping and Returns</a></li><li class="tab" id="reviews_td_tab"><a href="#" class="tab">Questions on Stenciling and Paint</a></li></ul>
	<div style="display: none;" id="faqData2">
<?php 
	while ($db->next_record()) {
		echo '<div class="accordionButton" name="'.$db->f("article_id").'">'.$db->f("article_title").'</div>
		<div class="accordionContent" style="display: none;">'.$db->f("short_description").'</div>';

	}
?>
	</div>
<?php
        $sql  = " SELECT article_id, article_title, short_description  FROM va_articles WHERE article_id IN (SELECT article_id FROM va_articles_assigned WHERE category_id = 42) AND status_id = 2 ";
	$db->query($sql);
	?>
	<div style="display: none;" id="faqData">
<?php 
	while ($db->next_record()) {
		echo '<div class="accordionButton" name="'.$db->f("article_id").'">'.$db->f("article_title").'</div>
		<div class="accordionContent" style="display: none;">'.$db->f("short_description").'</div>';

	}
?>
	</div>