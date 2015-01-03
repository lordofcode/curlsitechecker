<?php
// dagelijks bijwerken van de posts

// eerst terug naar de ROOT
chdir("../../..");

// inladen CORE wordpress
require_once( './wp-load.php' );

// inladen CORE eigen module
require_once("inc/mysql.class.php");
require_once("inc/s4u_model.php");

$sm = new S4UModel();
$cronKey = $sm->get_Setting("cronkey");
if (trim($cronKey) == ""){
	echo "<b>cronkey variabele (nog) niet geconfigureerd. stel dit eerst in. cron-job afgebroken.</b>";
	exit;	
}
else{
	if ($_REQUEST["cronkey"] == $cronKey){
		$sm->cron(intval($_REQUEST["id"]));		
	}
	else{
		die("<b>Onjuiste aanroep curlsitechecker/cron.php</b>");
	}
}
?>