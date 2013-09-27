function addAdmin(membership_id)
{
	$("#" + membership_id).replaceWith('<img id="' + membership_id + '" width="16" height="16" src="images/mod/sdr/loading.gif">');
	
	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "sdr",
			ajax : true,
			action : "AddOrganizationAdmin",
			membership_id : membership_id
		},
		success : function(data, textStatus) {
			addAdminComplete(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			addAdminError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function addAdminComplete(data, textStatus)
{
	$("#" + data.membership_id).replaceWith('<a id="' + data.membership_id + '" href="javascript:removeAdmin(' + data.membership_id + ');"><img width="16" height="16" src="images/mod/sdr/tango-icons/emblems/emblem-system.png"></a>');
}

function addAdminError(XMLHttpRequest, textStatus, errorThrown)
{
	$("#" + data.membership_id).replaceWith('<a id="' + data.membership_id + '" href="javascript:addAdmin(' + data.membership_id + ');"><img width="16" height="16" src="images/mod/sdr/tango-icons/emblems/emblem-unreadable.png"></a>');
	alert('Error adding admininstrator: ' + textStatus);
}

function removeAdmin(membership_id)
{
	$("#" + membership_id).replaceWith('<img id="' + membership_id + '" width="16" height="16" src="images/mod/sdr/loading.gif">');
	
	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "sdr",
			ajax : true,
			action : "RemoveOrganizationAdmin",
			membership_id : membership_id
		},
		success : function(data, textStatus) {
			removeAdminComplete(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			removeAdminError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function removeAdminComplete(data, textStatus)
{
	$("#" + data.membership_id).replaceWith('<a id="' + data.membership_id + '" href="javascript:addAdmin(' + data.membership_id + ');"><img width="16" height="16" src="images/mod/sdr/tango-icons/emblems/emblem-unreadable.png"></a>');
}

function removeAdminError(XMLHttpRequest, textStatus, errorThrown)
{
	$("#" + data.membership_id).replaceWith('<a id="' + data.membership_id + '" href="javascript:removeAdmin(' + data.membership_id + ');"><img width="16" height="16" src="images/mod/sdr/tango-icons/emblems/emblem-system.png"></a>');
	alert('Error removing admininstrator: ' + textStatus);
}
