function showRoleDrop() {
	$("#role_drop_li").show();
	$("#add_role_li").hide();
}

function addRoleDropBoxHandler()
{
	if ($("#role_add_form_role_drop_box").attr('selectedIndex') == 0) {
           return;
       }else{
       	$("#role_add_form_role_drop_box").attr('disabled', true);
           addRole($("#role_add_form_role_drop_box").val());
       }
}

function addRole(role_id) {
	$("#loading_img").show();
		var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "sdr",
			ajax : true,
			action : "AddRole",
			role_id : role_id,
			membership_id : $("#role_add_form_membership_id").val()
		},
		success : function(data, textStatus) {
			addRoleComplete(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			addRoleError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function addRoleComplete(data, textStatus) {
	// Reset the drop down box
	$("#role_add_form_role_drop_box").attr('disabled', false);
	$("#role_add_form_role_drop_box").attr('selectedIndex', 0);
	$("#role_add_form_role_drop_box").val(0);
		// Remove the Role that was just added from the drop down box
	$("#role_add_form_role_drop_box").removeOption(data.role_id);
		// Add the new role to the list of roles
	$("#role_list").append(
			"<li style=\"display: none;\" id=\"" + data.role_id + "\">" + data.title + " [<a href=\"javascript:removeRole("+ data.role_id + ")\">remove</a>]</li>");
	$("#" + data.role_id).hide();
		// Sort the list of roles
	sortList($("#role_list"));
		// Show/hide things appropriately
	$("#role_drop_li").hide();
	$("#loading_img").hide();
	$("#add_role_li").show();
	$("#no_roles").fadeOut(1000);
		$("#" + data.role_id).fadeIn(1000);
}

function addRoleError(XMLHttpRequest, textStatus, errorThrown) {
	alert('Error: ' + textStatus);
	// Reset the drop down box
	$("#role_add_form_role_drop_box").attr('disabled', false);
	$("#role_add_form_role_drop_box").attr('selectedIndex', 0);
	$("#role_add_form_role_drop_box").val(0);
		// Show/hide things
	$("#loading_img").hide();
	$("#role_drop_li").hide();
	$("#add_role_li").show();
}
	
function removeRole(role_id) {
		$("#loading_img").show();
	
	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "sdr",
			ajax : true,
			action : "RemoveRole",
			role_id : role_id,
			membership_id : $("#role_add_form_membership_id").val()
		},
		success : function(data, textStatus) {
			removeRoleComplete(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			removeRoleError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function removeRoleComplete(data, textStatus) {
		// Remove the "Choose role.." option so we can sort the options later
	$("#role_add_form_role_drop_box").removeOption(0);
		// Add the role which was just removed to the drop down box
    if(data.hidden == 0){
        // Do not re-add role to drop box if hidden
        $("#role_add_form_role_drop_box").addOption(data.role_id, data.title);
    }
    $("#role_add_form_role_drop_box").sortOptions();
	    // Prepend the "Choose role option" back to the select box (can't use
	// the drop box package)
    $("#role_add_form_role_drop_box").prepend("<option value=\"0\">Choose Role</option>");
	    // Make sure nothing is selected
    $("#role_add_form_role_drop_box").attr('selectedIndex', 0);
	    // Hide the loading image
       $("#loading_img").hide();
    
    // Remove the role from the list
    $("#" + data.role_id).fadeOut(1000, function(){
	    $("#" + data.role_id).remove();
		    // Check to see if all roles have been removed
       if($("#role_list").children().length == 0){
    	   // Re-add the "no roles" message
    		$("#no_roles").fadeIn(1000);
	   }
    });
}

function removeRoleError() {
    alert('Error: ' + textStatus);
    $("#loading_img").hide();
}

function sortList(mylist) {
	var listitems = mylist.children('li').get();
	listitems.sort(function(a, b) {
		var compA = $(a).text().toUpperCase();
		var compB = $(b).text().toUpperCase();
		return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
	})
	$.each(listitems, function(idx, itm) {
		mylist.append(itm);
	});
}