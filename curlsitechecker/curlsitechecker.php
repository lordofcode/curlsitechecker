<?php
/* @package CurlSiteChecker
 * @version 1.0
 */
/*
Plugin Name: Curl SiteChecker
Plugin URI: http://solution4u.nl
Description: Titel en afbeelding van sites opvragen
Author: Dirk Hornstra
Version: 1.0
Author URI: http://solution4u.nl/
*/

require_once("inc/mysql.class.php");
require_once("inc/s4u_model.php");

class SiteCheckerS4U{

	public function initialize(){
		// Setup the administration menus
		add_action('admin_menu', array(&$this, 'addsc_adminmenu'));
	}	
	
	public function addsc_adminmenu(){
		// Top Level Menu
		add_menu_page('Scraper', 'Scraper', 0, 's4u-prijschecker', array(&$this, 'generatePageCompareSites'), EMI_URL.'images/emi_16px.png', 400);
		add_submenu_page('s4u-prijschecker', 'Configuratie', 'Configuratie', 0, 's4u-configuratie', array(&$this, 'generatePageConfig'));		
		
		$sm = new S4UModel();
		$sm->createDB();
	}		

	public function generatePageCompareSites(){
		global $wpdb;		
		global $table_prefix;
		$mySQL = new MySQL(true, DB_NAME, DB_HOST, DB_USER, DB_PASSWORD, DB_CHARSET);
		require_once("inc/scansitelist.php");
	}	

	public function generatePageConfig(){
		global $table_prefix;
		$mySQL = new MySQL(true, DB_NAME, DB_HOST, DB_USER, DB_PASSWORD, DB_CHARSET);		
		require_once("inc/configure.php");
	}

	public function handlePost($postaction){
		switch($postaction){
			case "fetchurldata":
				$sm = new S4UModel();
				$sm->fetchUrlData();		
				break;
			case "addscansite":
				$sm = new S4UModel();
				$sm->addScansite();
				break;
			case "editscansite":
				$sm = new S4UModel();
				$sm->editScansite();
				break;	
			case "removescansite":
				$sm = new S4UModel();
				$sm->removeScansite();
				break;					
			case "detailscansite":
				$sm = new S4UModel();
				$sm->detailScansite();
				break;				
			case "addvaluta":
				$sm = new S4UModel();
				$sm->addValuta();
				break;
			case "fetchscansitedata":
				$sm = new S4UModel();
				$sm->fetchScanSiteData();
				break;				
			case "fetchhtmlpage":
			case "fetchdatawithxpath":
				$sm = new S4UModel();
				$sm->fetchHtmlPage($postaction, $_POST["testurl"], $_POST["testprijs"], $_POST["xpath"], false);
				break;	
			case "do_scan":
				$sm = new S4UModel();
				$sm->do_Scan(intval($_POST["productsite_id"]), true, true);
				break;
			case "remove_scan":
				$sm = new S4UModel();
				$sm->remove_Scan();
				break;
			case "addsetting":
				$sm = new S4UModel();
				$sm->add_Setting();
				break;
			case "editsetting":
				$sm = new S4UModel();
				$sm->edit_Setting();
				break;
			case "removesetting":
				$sm = new S4UModel();
				$sm->remove_Setting();
				break;
			case "cron":
				$sm = new S4UModel();
				$sm->cron();
				break;														
			default:
				break;
		}
		if ($postaction != "cron"){
			exit;
		}
	}
	
}
$s4u = new SiteCheckerS4U();
$s4u->initialize();
if (isset($_GET["custom_action"])) $s4u->handlePost($_GET["custom_action"]);

function curlsitechecker_cron(){
	$s4u = new SiteCheckerS4U();
	$s4u->initialize();	
	$s4u->handlePost("cron");
}
?>