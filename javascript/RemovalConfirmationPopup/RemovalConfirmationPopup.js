function removeMembershipConfirmationJS(memberId)
{
	$("#remove_confirm_dialog").empty();
    $("#remove_confirm_dialog").load('index.php?module=sdr&ajax=true&action=RemoveMembershipConfirmation&membership_id=' + memberId);
    $("#remove_confirm_dialog").dialog('open');
    
    return true;
}