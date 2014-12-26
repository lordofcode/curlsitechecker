<link rel="stylesheet" href="/wp-content/plugins/curlsitechecker/css/ui-lightness/jquery-ui-1.10.3.custom.min.css">
<link rel="stylesheet" href="/wp-content/plugins/curlsitechecker/css/s4u.css">

<div class="addconfigurecolor" style="float:right;margin:10px;">
	<button id="create-configurecolor">Configuratiewaarde toevoegen</button>
</div>
<div style="clear:both"></div>

<div id="dialog-newcolor-form" title="Nieuwe configuratiewaarde" style="display:none">
	<form id="newcolor">
	<input type="hidden" name="type" value="color" />
	<fieldset>
		<label for="name">Naam</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="value">Waarde</label>
		<input type="text" name="value" id="value" class="text ui-widget-content ui-corner-all" />		
	</fieldset>
	</form>
</div>	

<div id="editcolor-dialog-form" title="Bewerk configuratiewaarde" style="display:none">
	<form id="editcolor">
	<input type="hidden" name="id" id="color_edit_id" value="" />
	<input type="hidden" name="type" value="color" />
	<fieldset>
		<label for="edit_name">Naam</label>
		<input type="text" name="name" id="edit_name" class="text ui-widget-content ui-corner-all" /><br/>
		<label for="edit_value">Waarde</label>
		<input type="text" name="value" id="edit_value" class="text ui-widget-content ui-corner-all" />
	</fieldset>
	</form>
</div>	

<div id="colorlist" style="float:left;">
<?php 		
	$mySQL->Query("SELECT id,name,value 
		FROM {$table_prefix}s4u_configsetting
		ORDER BY name ASC
		");
	echo $mySQL->GetHTML(false);
?>
</div>

<script src="/wp-content/plugins/curlsitechecker/js/jquery-1.9.1.js"></script>
<script src="/wp-content/plugins/curlsitechecker/js/jquery-ui-1.10.3.custom.min.js"></script>
<script language="javascript" type="text/javascript" src="/wp-content/plugins/curlsitechecker/js/s4uscript.js"></script>
<script language="javascript" type="text/javascript">
jQuery(function() {
	// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
/*	try{jQuery( "#dialog:ui-dialog" ).dialog( "destroy" );}catch(e){}*/
});

jQuery(function() {

	jQuery( "#dialog-newcolor-form" ).dialog({
		autoOpen: false,
		height: 200,
		width: 400,
		modal: true,
		buttons: {
			"Opslaan": function() {
				$.post("/wp-admin/admin.php?page=s4u-configuratie&custom_action=addsetting", $("#dialog-newcolor-form #newcolor").serialize(), function(data){		
					document.location.href = "/wp-admin/admin.php?page=s4u-configuratie";
				});
			},
			"Annuleren": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});

	jQuery( "#editcolor-dialog-form" ).dialog({
		autoOpen: false,
		height: 200,
		width: 400,
		modal: true,
		buttons: {
			"Opslaan": function() {
				$.post("/wp-admin/admin.php?page=s4u-configuratie&custom_action=editsetting", $("#editcolor-dialog-form #editcolor").serialize(), function(data){			
					document.location.href = "/wp-admin/admin.php?page=s4u-configuratie";
				});
			},
			"Annuleren": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});
	
	jQuery( "#create-configurecolor" )
	.button()
	.click(function() {
		jQuery( "#name" ).val("");
		jQuery( "#value" ).val("");
		jQuery( "#dialog-newcolor-form" ).dialog( "open" );
	});
	
	jQuery( "#colorlist tr td" )
	.click(function() {
		if (isNaN(jQuery(this).parent().children("td:eq(0)").html())) return;
		jQuery("#color_edit_id").val(jQuery(this).parent().children("td:eq(0)").html());
		jQuery("#edit_name").val(jQuery(this).parent().children("td:eq(1)").html());
		jQuery("#edit_value").val(jQuery(this).parent().children("td:eq(2)").html());
		jQuery( "#editcolor-dialog-form" ).dialog( "open" );
	})
	
});
</script>