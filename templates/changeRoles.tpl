<br />
Change organizational role for: {STUDENT_NAME}
<br />
<br />

<h2 style="display: inline;">Roles:</h2>
&nbsp;
<img id="loading_img" height="25" width="25"
	src="images/mod/sdr/loading.gif">

<!-- BEGIN unapproved -->
{UNAPPROVED}You cannot currently edit the roles associated with this membership because the membership has not been approved yet.
<!--  END unapproved -->

{START_FORM}
<!-- BEGIN no_roles -->
{NO_ROLES}
<div id="no_roles">
No roles were found for this member. Use the drop down box below to add at least one role.
</div>
<ul id="role_list">
</ul>
<!-- END no_roles -->

<!-- BEGIN add_roles -->
<ul id="role_list">

	<!-- BEGIN ROLE_REPEAT -->
	<li id="{ROLE_ID}" class="roleList">{ROLE_TITLE} [<a
		href="javascript:removeRole({ROLE_ID});">remove</a>]</li>
	<!-- END ROLE_REPEAT -->

</ul>

<div id="role_drop_li">{ROLE_DROP_BOX}</div>
<div id="add_role_li"><a
	href="javascript:showRoleDrop();">Add another role</a></div>

<!-- END add_roles -->
{END_FORM}
