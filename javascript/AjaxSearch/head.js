<script type="text/javascript" src="mod/sdr/javascript/AjaxSearch/AjaxSearch.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#{elementId}').ajaxSearch({
        <!-- BEGIN QUERYPARAMS -->
		queryParams: {queryParams},
        <!-- END QUERYPARAMS -->
        <!-- BEGIN AJAXURI -->
        ajaxUri: {ajaxUri},
        <!-- END AJAXURI -->
        <!-- BEGIN SEARCHWHENEMPTY -->
		searchWhenEmpty: {searchWhenEmpty},
        <!-- END SEARCHWHENEMPTY -->
        <!-- BEGIN RENDERCALLBACK -->
		renderCallback: {renderJSCallback},
        <!-- END RENDERCALLBACK -->
        <!-- BEGIN SELECTEDURI -->
		selectedUri: {selectedUri},
        <!-- END SELECTEDURI -->
        <!-- BEGIN SELECTEDURIKEY -->
        selectedUriKey: {selectedUriKey},
        <!-- END SELECTEDURIKEY -->
        <!-- BEGIN SELECTEDATTR -->
        selectedAttr: {selectedAttr},
        <!-- END SELECTEDATTR -->
        <!-- BEGIN SELECTEDCALLBACK -->
		selectedCallback: {selectedCallback},
        <!-- END SELECTEDCALLBACK -->
        <!-- BEGIN FIELDSSETONSELECT -->
        fieldsSetOnSelect: {fieldsSetOnSelect},
        <!-- END FIELDSSETONSELECT -->
        searchDelay: 500
	});
});
</script>
