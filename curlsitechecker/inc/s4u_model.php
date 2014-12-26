<?php

class S4UModel{

	private $checkDocument = null;
	
	public function createDB(){
		global $wpdb;
		global $table_prefix;

		$wpdb->query("CREATE TABLE IF NOT EXISTS {$table_prefix}s4u_scansite (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `actief` tinyint(1) NOT NULL,
		  `xpath` varchar(255) NOT NULL,
		  `xpath_image` varchar(255) NOT NULL,
		  `test_url` varchar(255) NOT NULL,
		  `test_title` varchar(255) NOT NULL,
		  `test_image` varchar(255) NOT NULL,
		  `laatste_scan` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS {$table_prefix}s4u_configsetting (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `value` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `name` (`name`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");		
		
		$exists = intval($wpdb->get_var("SELECT COUNT(1) FROM {$table_prefix}s4u_configsetting WHERE name='cronkey'"));
		if ($exists <= 0){
			$wpdb->query("INSERT INTO {$table_prefix}s4u_configsetting (name, value) SELECT 'cronkey', ''");	
		}
	}
		 
	public function fetchUrlData(){
		global $wpdb;
		global $table_prefix;
		$site_id = intval($_POST["site_id"]);
	
		$xpath = $wpdb->get_var("SELECT xpath FROM {$table_prefix}s4u_scansite WHERE id={$site_id}");		
		$this->fetchHtmlPage("fetchdatawithxpath", $_POST["url"], "____", $xpath, false);		
		exit;
	}
	
	public function addScansite(){
		global $wpdb;
		global $table_prefix;

		$name = addslashes($_POST["name"]);
		$url = addslashes($_POST["url"]);
		$actief = intval($_POST["actief"]);
		$wpdb->query("INSERT INTO {$table_prefix}s4u_scansite (name, url, actief) SELECT '{$name}', '{$url}', '{$actief}'");
		exit;
	}
	 
	public function editScansite(){
		global $wpdb;
		global $table_prefix;

		$id = intval($_POST["id"]);
		$name = addslashes($_POST["name"]);
		$url = addslashes($_POST["url"]);
		$actief = intval($_POST["actief"]);
		$wpdb->query("UPDATE {$table_prefix}s4u_scansite SET name='{$name}', url='{$url}', actief='{$actief}' WHERE id={$id}");
		exit;
	}

	public function removeScansite(){
		global $wpdb;
		global $table_prefix;

		$id = intval($_POST["id"]);
		
		$wpdb->query("DELETE FROM {$table_prefix}s4u_scansite WHERE id={$id}");

		exit;
	}	
	
	public function detailScansite(){
		global $wpdb;
		global $table_prefix;

		$id = intval($_POST["id"]);
		$xpath = addslashes($_POST["xpath"]);
		$xpathimage = addslashes($_POST["xpath_image"]);
		$test_url = addslashes($_POST["testurl"]);
		$wpdb->query("UPDATE {$table_prefix}s4u_scansite SET xpath='{$xpath}', xpath_image='{$xpathimage}', test_url='{$test_url}' WHERE id={$id}");
		exit;
	}	
		
	public function fetchScanSiteData(){
		global $wpdb;
		global $table_prefix;

		$res = array();
		if (isset($_POST["id"])){
			$id = intval($_POST["id"]);
			$res = $wpdb->get_row("SELECT 
			`id`,`url`,`actief`,`xpath`,`xpath_image`,
			CASE WHEN IFNULL(`test_url`,'') = '' THEN url ELSE test_url END AS test_url			
			FROM {$table_prefix}s4u_scansite WHERE id={$id}", ARRAY_A);
		}
		
		$result_arr = array("result" => (count($res) > 0 ? "1" : "0"), "data" => $res);
		echo json_encode($result_arr);		
		exit;
	}

	public function fetchHtmlPage($postaction, $url, $testprijs, $xpath_string, $returnresult){
		// set URL and other appropriate options
//		set_time_limit(60); // 1 minute		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // put data in local var
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');  // handle gzipped response
		curl_setopt($ch, CURLOPT_POST, 0);
		
		// grab data and put in DOM-XML document
		$html = curl_exec($ch);
		curl_close($ch);
		
		$doc = new DOMDocument();
		// buffer and do not show to suppress warnings
		ob_start();
		$doc->loadHTML($html);
		$this->checkDocument = new DOMDocument();
		$this->checkDocument->loadHTML($html);
		ob_end_clean();

		
		$result = false;
		$message = "xpath kan niet gevonden worden";
		$xpath = "";
		$value = "";		

		if ($postaction == "valutapage"){
			return $html;
		}
		elseif ($postaction == "fetchhtmlpage"){
			// get XPATH
			if ($doc->hasChildNodes()){
				$found = false;
				if (strlen($testprijs)>0){
					if ($this->findXPath($doc, $testprijs, $found, $xpath_string)){
						$result = true;
						$message = "gelukt";
					}
				}
			}	
			else $message = "de pagina kan niet ingeladen worden";			
		}
		elseif ($postaction == "fetchscanresult"){
			$items = explode("|", $xpath_string);
			while (list($k,$v)=each($items)){
				$query = $v;
				// buffer errors
				ob_start();
				$xpath = new DOMXPath($this->checkDocument);
				$entries = $xpath->query($query);	
				foreach ($entries as $entry) {
					$message = $entry->textContent;
					$value = $entry->textContent;
					$result = true;
					break;
				}
				ob_end_clean();
				
				if ($result) break; // get out
			}				
		}
		else{		
			// test XPATH
			$items = explode("|", $xpath_string);
			while (list($k,$v)=each($items)){
				$query = $v;
				// buffer errors
				ob_start();
				$xpath = new DOMXPath($this->checkDocument);
				$entries = $xpath->query($query);
				foreach ($entries as $entry) {
/*										
					preg_match("/([0-9\.,])+/", $entry->textContent, $matches);
					if (count($matches) > 0){
						if (($matches[0] == $testprijs)||($testprijs == "____")){
							$result = true;
							$xpath = $query;
							$message = "gelukt (".$matches[0].")";
						}
						else{
							$message = "gevonden waarde is : " . $matches[0];
							$value = $matches[0];						
						}
						break; // out of foreach
					}
*/
					$message = $entry->textContent;
					$result = true;
					break;					
				}
				ob_end_clean();	
				
				if ($result) break; // get out
			}
		}
		
		$result_arr = array("result" => ($result ? "1" : "0"), "message" => $message, "xpath" => $xpath, "value" => $value);
		
		if ($returnresult){
			return json_encode($result_arr);
		}
		echo json_encode($result_arr);
		
		exit;
	}
	
	function findXPath($xDoc, $zoekWaarde, &$found, &$xpath){
		$this_found = false;

		if ($xDoc->hasChildNodes()){
			foreach ($xDoc->childNodes as $cn){
				if (strpos("_".$cn->textContent, $zoekWaarde) > 0 ){
					$this_found = true;
					$found = true;
				}
				if ($this->findXPath($cn, $zoekWaarde, $found, $xpath)){
					return true;
				}				
			}
		}
		else{
			if ($found){
				if (strpos("_".$xDoc->textContent, $zoekWaarde) > 0 ){
					if ($xDoc->parentNode != null){
						if ($this->calculateXPath($xDoc->parentNode, $zoekWaarde, $xpath)){
							return true;
						}						
					}					
				}
			}
					
		}
		return false;
		
	}
	
	function calculateXPath($node, $zoekWaarde, &$path){
		$query = "//";
		
		$filters = array();
		$query .= $node->nodeName;
		if ($node->attributes->length > 0){
			for ($z=0; $z < $node->attributes->length; $z++){
				switch (strtolower(trim($node->attributes->item($z)->name))){
					case "class":
						$filters["class"] = $node->attributes->item($z)->value;
						break;
					case "ids":
						$filters["id"] = $node->attributes->item($z)->value;
						break;
					default:
						break;
				}
			}
		}

		if (count($filters) > 0){
			if (array_key_exists("id", $filters)){
				$query .= "[@id=\"{$filters['id']}\"]";	
			}
			elseif (array_key_exists("class", $filters)){
				$query .= "[@class=\"{$filters['class']}\"]";
			}

			$xpath = new DOMXPath($this->checkDocument);
			$entries = $xpath->query($query);
			
			foreach ($entries as $entry) {
				preg_match("/([0-9\.,])+/", $entry->textContent, $matches);
				if (count($matches) > 0){
					if ($matches[0] == $zoekWaarde){
						$path = $query;
						return true;
					}
				}
			}	

		}
		
		return false;
	}

	function do_Scan($scansite_id, $echo_result, $do_stop){
		global $wpdb;
		global $table_prefix;
		$result = "NOTHING DONE";		
		
		$rows = $wpdb->get_results("SELECT name, url, xpath, xpath_image FROM {$table_prefix}s4u_scansite WHERE actief=1 AND id=".intval($scansite_id));
		if (count($rows) > 0){
			$result = "";
			for ($k=0; $k < count($rows); $k++){
				$title = "";
				$image = "";
				
				$titleresult = $this->fetchHtmlPage("fetchscanresult", $rows[$k]->url, "", $rows[$k]->xpath, true);
				$decoded = json_decode($titleresult);

				if ($decoded->result == "1"){
					$wpdb->query("UPDATE {$table_prefix}s4u_scansite SET laatste_scan=NOW() WHERE id=".intval($scansite_id));
					$result .= "{$k} processed OK<br/>";
					$title = $decoded->value;
					
					$imageresult = $this->fetchHtmlPage("fetchscanresult", $rows[$k]->url, "", $rows[$k]->xpath_image, true);
					$decoded = json_decode($imageresult);
						
					if ($decoded->result == "1"){
						$image = $decoded->value;
					}						
				
					if ($title != ""){
						$this->add_Wordpress_Post($rows[$k]->name, $rows[$k]->url, $title, $image);
					}
				}
				else{
					echo "<b>Site ".$rows[$k]->url." failed.</b><HR>";
				}
			}
		}		

		if ($echo_result) echo $result;
		if ($do_stop) exit;		
	}
	
	function add_Wordpress_Post($name, $url, $title, $image){
		// first check image
		if (preg_match("/^http(.*)$/", strtolower($image))){
			/* image is ok */
		}
		else{
			if (preg_match("/http(.*)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)/", strtolower($image), $matches)){
				$image = $matches[0];
			}
			else{
				if (preg_match("/\/\/(.*)(\.bmp|\.gif|\.jpg|\.jpeg|\.png)/", strtolower($image), $matches)){
					if (preg_match("/http([s]*):/", strtolower($url), $hostmatch)){
						$image = $hostmatch[0] . $matches[0]; 
					}
				}				
				else{
					$image = "";
				}
			}
		}

		// first, check if post from this URL with same title already exists (last 5 posts of this site)
		$criteria = array("meta_key" => "url", "meta_value" => $url);
		$posts = get_posts($criteria);
		for ($k=0; $k < count($posts); $k++){
			if (strtolower($title) == strtolower(get_post_meta($posts[$k]->ID, "original-title", true))){
				echo "<b>Already processed.</b><br/>";
				return;
			}	
		}		

		$data = array();
		$data['ID'] = null;
		$data['post_title'] = $title;
		//$data['post_content'] = $title;
		$data['post_status'] = "draft";
		$data['comment_status'] = "closed";
		$data['ping_status'] = "closed";
		$data['post_date_gmt'] = date('Y-m-d H:n:s', mktime());
		$p_id = wp_insert_post($data);

		add_post_meta($p_id, "url", $url);
		add_post_meta($p_id, "original-title", $title);
		
		wp_add_post_tags($p_id, $name);

		if ($image != ""){
			$imageUrl = $image;
			$tmpname = "dot_" . $p_id . "." . strrev(substr(strrev($imageUrl), 0, strpos(strrev($imageUrl), ".")));
								
			$upload_dir = wp_upload_dir();
			$image_data = file_get_contents($imageUrl);
			if(wp_mkdir_p($upload_dir['path']))
			    $file = $upload_dir['path'] . '/' . $tmpname;
			else
			    $file = $upload_dir['basedir'] . '/' . $tmpname;
			file_put_contents($file, $image_data);
			
			$wp_filetype = wp_check_filetype($tmpname, null );
			$attachment = array(
			    'post_mime_type' => $wp_filetype['type'],
			    'post_title' => sanitize_file_name($tmpname),
			    'post_content' => '',
			    'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $file, $p_id );
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );					
			
	
			set_post_thumbnail($p_id, $attach_id);			
		}
		
		$data['ID'] = $p_id;
		$data['post_status'] = "publish";
		wp_update_post($data);
		
	}	
	
	function add_Setting(){
		global $wpdb;
		global $table_prefix;
			
		$wpdb->query("INSERT INTO {$table_prefix}s4u_configsetting 
			SET 
			name = '".addslashes($_POST["name"])."',
			value = '".addslashes($_POST["value"])."'
		");
		exit;
	}
	
	function edit_Setting(){
		global $wpdb;
		global $table_prefix;
				
		$id = intval($_POST["id"]);
		
		$wpdb->query("UPDATE {$table_prefix}s4u_configsetting 
			SET 
			name = '".addslashes($_POST["name"])."',
			value = '".addslashes($_POST["value"])."'
			WHERE id={$id}
		");
		exit;
	}
	
	function get_Setting($setting){
		global $wpdb;
		global $table_prefix;
		
		return $wpdb->get_var("SELECT value
				FROM {$table_prefix}s4u_configsetting
				WHERE name='".addslashes($setting)."'
				");	
	}
	
	function remove_Setting(){
		exit;
	}
		
	function cron(){
		global $wpdb;
		global $table_prefix;
		
		$lastrun = $wpdb->get_var("SELECT id 
				FROM {$table_prefix}s4u_configsetting
				WHERE name='cron'
				ORDER BY id DESC
				LIMIT 0,1");
		if ($lastrun == ""){					
			$wpdb->query("INSERT INTO {$table_prefix}s4u_configsetting (name, value) SELECT 'cron', NOW()");
		}
		else{
			$wpdb->query("UPDATE {$table_prefix}s4u_configsetting SET value=NOW() WHERE name='cron'");
		}		
		
		if ($_REQUEST["force"] == "true"){
			$res = $wpdb->get_results("SELECT {$table_prefix}s4u_scansite.id
							FROM {$table_prefix}s4u_scansite
							WHERE {$table_prefix}s4u_scansite.actief = 1
							");			
		}
		else{
			$res = $wpdb->get_results("SELECT {$table_prefix}s4u_scansite.id
							FROM {$table_prefix}s4u_scansite
							WHERE {$table_prefix}s4u_scansite.actief = 1
							AND date(IFNULL({$table_prefix}s4u_scansite.laatste_scan,'1900-01-01')) < '".date('Y-m-d')."'
							");
		}

		foreach ($res as $scansite){
			$this->do_Scan($scansite->id, false, false);
		}
		

	}
}

?>