<script type="text/javascript" src="mod/sdr/javascript/OrganizationBrowserCategory/jquery.selectboxes.js"></script>
<script type="text/javascript" src="mod/sdr/javascript/RoleEditor/RoleEditor.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	// Hide the drop down with the lsit of roles
		$("#role_drop_li").hide();
		$("#loading_img").hide();

		// Set event listener on "add another role" link
		$("#add_role_li").bind("click", showRoleDrop);

		// Set event listender for changes on role drop down
		$("#role_add_form_role_drop_box").bind("change", addRoleDropBoxHandler);

	});
</script>
