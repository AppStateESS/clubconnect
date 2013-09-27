<table cellpadding="4" cellspacing="1" width="100%">
<tr class="bgcolor1">
<td><b>{ORGANIZATION_LBL}</b></td>
<!-- <td><b>{SEMESTER_LBL}</b></td> -->
<!-- BEGIN ACTIONS_LBL -->
<td><b>{ACTIONS_LBL}</b></td>
<!-- END ACTIONS_LBL -->
</tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{ORGANIZATION}</td>
<!--    <td>{SEMESTER}</td> -->
<!-- BEGIN USER_ACTIONS -->
    <td>{USER_ACTIONS}</td>
<!-- END USER_ACTIONS -->
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
