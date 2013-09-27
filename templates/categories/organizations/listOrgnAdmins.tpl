<div style="float:right;">{NEW_ADMIN}</div>
<h1>Administrators</h1><br />
Listed below are users with special access rights to this organization.
<br /><br />
<!-- BEGIN MESSAGE -->
{MESSAGE}
<!-- END MESSAGE -->

<table cellpadding="4" cellspacing="1" width="100%">
<tr class="bgcolor1">
<td><b>{NAME_LBL}</b></td>
<td><b>{USERNAME_LBL}</b></td>
<td><b>{ACCESS_LBL}</b></td>
<td><b>{SEMESTER_LBL}</b></td>
<td><b>{LAST_LOGIN_LBL}</b></td>
<td><b>{ACTION_LBL}</b></td>
</tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{NAME}</td>
    <td>{USERNAME}</td>
    <td>{ACCESS}</td>
    <td>{SEMESTER}</td>
    <td>{LAST_LOGIN}</td>
    <td>{ACTIONS}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<br />
<div style="text-align : center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div style="text-align : right">
{SEARCH}
</div>
