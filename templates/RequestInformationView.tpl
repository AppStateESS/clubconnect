<script type="text/javascript">
$("#cancel_link").replaceWith('<input type="button" value="Cancel" onClick="$(\'#sdr_jquery_dialog\').dialog(\'destroy\')">');
</script>

<p>If you'd like to send an additional message with your request, enter it below.</p>
{START_FORM}
{EXTRA_MESSAGE}
<div>{SUBMIT} <span id="cancel_link">{CANCEL}</span></div>
{END_FORM}