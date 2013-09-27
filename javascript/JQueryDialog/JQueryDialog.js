function openJQueryDialog(uri, dialogTitle)
{
	$("#sdr_jquery_dialog").dialog({title: dialogTitle, autoOpen: false, modal: true, width: 425, resizable: false, position: 'top'});
	$("#sdr_jquery_dialog").empty();
    $("#sdr_jquery_dialog").load(uri);
    $("#sdr_jquery_dialog").dialog('open');
    
    return true;
}