<h1>Membership Form</h1>
{JAVASCRIPT}
<!-- BEGIN CALM_MESSAGE -->
<i>{MESSAGE}</i>
<br /><br />
<!-- END CALM_MESSAGE -->
<!-- BEGIN ERROR_MESSAGE -->
<span style="color:red;"><i>{ERROR_MESSAGE}</i></span>
<!-- END ERROR_MESSAGE -->
{START_FORM}
<table cellpadding="7">
<tr><td>{MEMBER_NAME_LBL}: </td><td>{MEMBER_NAME}</td></tr>
<tr><td>{MEMBER_STATUS_LBL}: </td><td>{MEMBER_STATUS}</td></tr>
<!-- BEGIN SEMESTER_YEAR -->
<tr><td colspan="2">Semester: {SEMESTER}  &nbsp;&nbsp; Year: {YEAR}</td></tr>
<!-- END SEMESTER_YEAR -->
<tr><td colspan="2"><br />{JOIN}&nbsp;&nbsp;{CANCEL_JOIN}</td></tr>
</table>
{END_FORM}
