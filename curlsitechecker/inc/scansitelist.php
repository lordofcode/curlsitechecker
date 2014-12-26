<link rel="stylesheet" href="/wp-content/plugins/curlsitechecker/css/ui-lightness/jquery-ui-1.10.3.custom.min.css">
<link rel="stylesheet" href="/wp-content/plugins/curlsitechecker/css/s4u.css">
  
<div class="addlink" style="float:right;margin:10px;">
	<button id="create-scansite">Scansite toevoegen</button>
</div>
<div style="clear:both"></div>

<div id="dialog-form" title="Nieuwe scansite" style="display:none">
	<form id="newscansite">
	<fieldset>
		<label for="name">Naam</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" /><br/>	
		<label for="url">Url</label>
		<input type="text" name="url" id="url" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="actief">Actief</label>
		<input type="checkbox" name="actief" id="actief" class="text ui-widget-content ui-corner-all" value="1" checked="checked" /><br/>
		<br/>		
	</fieldset>
	</form>
</div>	

<div id="edit-dialog-form" title="Bewerk scansite" style="display:none">
	<form id="editscansite">
	<input type="hidden" name="id" id="scansite_edit_id" value="" />
	<fieldset>
		<label for="name">Naam</label>
		<input type="text" name="name" id="edit_name" class="text ui-widget-content ui-corner-all" /><br/>	
		<label for="url">Url</label>
		<input type="text" name="url" id="edit_url" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="edit_actief">Actief</label>
		<input type="checkbox" name="actief" id="edit_actief" class="text ui-widget-content ui-corner-all" value="1" /><br/>
		<br/>		
	</fieldset>
	</form>
</div>	

<div id="scansitelist" style="float:left;">
<?php 		
	$mySQL->Query("SELECT {$table_prefix}s4u_scansite.id,
		{$table_prefix}s4u_scansite.name, 
		{$table_prefix}s4u_scansite.url,
		CASE WHEN IFNULL(actief,0) = 0 THEN 'inactief'
		ELSE 'actief' END AS actief,
		laatste_scan,
		'#' as bewerken
		FROM {$table_prefix}s4u_scansite
		ORDER BY {$table_prefix}s4u_scansite.actief ASC, {$table_prefix}s4u_scansite.url ASC
		");	
	echo $mySQL->GetHTML(false);
?>
</div>
<div id="detail-dialog-form" title="XPath voor opvragen featured artikel" style="display:none">
	<form id="detailscansite">
	<input type="hidden" name="id" id="scansite_detail_id" value="" />
	<fieldset>
		<label for="xpath">XPath Titel</label>
		<input type="text" name="xpath" id="xpath" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="xpathknop">&nbsp;</label>
		<input type="button" name="xpathknop" id="xpathknop" value="Controleer xpath titel" /><br/>
		<label for="xpath">XPath Afbeelding</label>
		<input type="text" name="xpath_image" id="xpath_image" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="xpathknopimage">&nbsp;</label>
		<input type="button" name="xpathknopimage" id="xpathknopimage" value="Controleer xpath afbeelding" /><br/>
		<cite>Test</cite><br/>		
		<label for="testurl">Test-url</label>
		<input type="text" name="testurl" id="testurl" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="testurl_link">&nbsp;</label>
		<a id="testurl_link" href="#" target="_blank"><span id="testurl_span"></span></a><br/>
		<br/><br/>
		<label>Testresultaat</label><br/>
		<div id="testresultaat" style="font-weight:bold"></div>				
	</fieldset>
	</form>
</div>
<script src="/wp-content/plugins/curlsitechecker/js/jquery-1.9.1.js"></script>
<script src="/wp-content/plugins/curlsitechecker/js/jquery-ui-1.10.3.custom.min.js"></script>
<script language="javascript" type="text/javascript" src="/wp-content/plugins/curlsitechecker/js/s4uscript.js"></script>
<script language="javascript" type="text/javascript">
jQuery(function() {
	// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
	jQuery( "#dialog:ui-dialog" ).dialog( "destroy" );
});


jQuery(function() {
	jQuery( "#dialog-form" ).dialog({
		autoOpen: false,
		height: 220,
		width: 400,
		modal: true,
		buttons: {
			"Opslaan": function() {
				$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=addscansite", $("#dialog-form #newscansite").serialize(), function(data){
					document.location.href = "/wp-admin/admin.php?page=s4u-prijschecker";
				});
			},
			"Annuleren": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});

	jQuery( "#edit-dialog-form" ).dialog({
		autoOpen: false,
		height: 220,
		width: 400,
		modal: true,
		buttons: {
			"Verwijderen": function() {
				if (confirm('Scansite echt verwijderen?')){
					$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=removescansite", $("#edit-dialog-form #editscansite").serialize(), function(data){			
						document.location.href = "/wp-admin/admin.php?page=s4u-prijschecker";
					});
				}
			},		
			"Opslaan": function() {
				$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=editscansite", $("#edit-dialog-form #editscansite").serialize(), function(data){			
					document.location.href = "/wp-admin/admin.php?page=s4u-prijschecker";
				});
			},
			"Annuleren": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});

	jQuery( "#detail-dialog-form" ).dialog({
		autoOpen: false,
		height: 400,
		width: 600,
		modal: true,
		buttons: {
			"Opslaan": function() {
				$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=detailscansite", $("#detail-dialog-form #detailscansite").serialize(), function(data){			
					document.location.href = "/wp-admin/admin.php?page=s4u-prijschecker";
				});
			},
			"Annuleren": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});
	
	jQuery( "#create-scansite" )
	.button()
	.click(function() {
		jQuery("#url").val("");
		jQuery("#actief").prop("checked",true);		
		jQuery( "#dialog-form" ).dialog( "open" );
	});

	jQuery( "#scansitelist tr td" )
	.click(function() {
		if (jQuery(this).html() != "#") return;
		jQuery("#scansite_detail_id").val(jQuery(this).parent().children("td:eq(0)").html());
		
		$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=fetchscansitedata", "id="+jQuery(this).parent().children("td:eq(0)").html(), function(data){
			var d = eval("("+data+")");
			if (d["result"] == "1"){
				jQuery("#xpath").val(d["data"]["xpath"]);
				jQuery("#xpath_image").val(d["data"]["xpath_image"]);
				jQuery("#testurl").val(d["data"]["test_url"]);
			}
			else{
				jQuery("#xpath").val("");
				jQuery("#testurl").val("");
			}					
			jQuery( "#detail-dialog-form" ).dialog( "open" );
			jQuery( "#testurl" ).blur();		
		});
		
	})
	.dblclick(function() {
		jQuery("#scansite_edit_id").val(jQuery(this).parent().children("td:eq(0)").html());
		jQuery("#edit_name").val(jQuery(this).parent().children("td:eq(1)").html());
		jQuery("#edit_url").val(jQuery(this).parent().children("td:eq(2)").html());
		

		jQuery("#edit_actief").attr("checked", (jQuery(this).parent().children("td:eq(3)").html() == "actief"));
		jQuery( "#edit-dialog-form" ).dialog( "open" );

		try{
			jQuery(".ui-dialog .ui-dialog-buttonset").each(function(){
				if (jQuery(this).children().length == 3){
					jQuery(this).css("width","100%");
				}
			});
		}catch(e){}		
	});	

	jQuery( "#testurl" ).on("keyup" , function() {
		fillSpanWithLink(jQuery(this).val());
	});
	jQuery( "#testurl" ).on("blur" , function() {
		fillSpanWithLink(jQuery(this).val());
	});	

	jQuery( "#xpathknop" )
	.click(function() {
		jQuery("#testresultaat").html("bezig met zoeken...");
		$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=fetchdatawithxpath", $("#detail-dialog-form #detailscansite").serialize(), function(data){			
			var d = eval("("+data+")");
			jQuery("#testresultaat").html(d["message"]);
		});		
	});

	jQuery( "#xpathknopimage" )
	.click(function() {
		jQuery("#testresultaat").html("bezig met zoeken...");
		// ivm 2x een XPATH even de doorgestuurde variabele swappen
		var t = $("#detail-dialog-form #detailscansite").serialize();
		t = t.replace("xpath=", "dummyvar=");
		t = t.replace("xpath_image=", "xpath="); 
		$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=fetchdatawithxpath", t, function(data){			
			var d = eval("("+data+")");
			jQuery("#testresultaat").html(d["message"]);
		});		
	});	
	
	jQuery( "#testknop" )
	.click(function() {
		jQuery("#testresultaat").html("bezig met zoeken...");
		$.post("/wp-admin/admin.php?page=s4u-prijschecker&custom_action=fetchhtmlpage", $("#detail-dialog-form #detailscansite").serialize(), function(data){			
			var d = eval("("+data+")");
			if (d["result"] == "1"){
				jQuery("#xpath").val(d["xpath"]);
			}
			jQuery("#testresultaat").html(d["message"]);
		});		
	});
});

function fillSpanWithLink(t_v){
	jQuery("#testurl_link").attr("href", t_v);
	jQuery("#testurl_span").html(t_v);	
}
</script>