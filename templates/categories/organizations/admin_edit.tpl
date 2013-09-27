<h1>Administrators</h1><br />
<div style="float:right;">{BACK}</div>
{MESSAGE}
{START_FORM}

<b>Select User:</b><br />
<table>
<tr><td>{LOGIN_OPTION_1}</td><td>Organization Officer</td></tr>
<tr><td>&nbsp;</td><td>{OFFICER}</td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>{LOGIN_OPTION_3}</td><td>Advisor</td></tr>
<tr><td>&nbsp;</td><td>{ADVISORS}</td></tr>
<!-- 
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td>{LOGIN_OPTION_2}</td><td>phpWebSite User</td></tr>
<tr><td>&nbsp;</td><td>{ASSIGNED_GROUPS}&nbsp;&nbsp;Name: -->
<!-- {NAME}</td></tr>
-->
</table>

<!-- BEGIN SPECIAL_EDIT -->
<br />
<table>
<tr><td><b>Semester</b>: &nbsp;{SEMESTER} &nbsp;<b>Year</b>: {YEAR}</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><b>Active</b>: &nbsp;{ACTIVE}</td></tr>
</table>
<!-- END SPECIAL_EDIT -->

<br /><br />

{SAVE}

{END_FORM}
