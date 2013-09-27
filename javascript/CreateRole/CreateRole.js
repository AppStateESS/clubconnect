
function showCreateRole() {
	$("#create_role_form").show("fast");
	$("#show_form").hide("fast");	
}

function createRoleHelp() {
    $("#help_dialog").dialog({ autoOpen: false });
    $("#help_dialog").dialog('open');
}
