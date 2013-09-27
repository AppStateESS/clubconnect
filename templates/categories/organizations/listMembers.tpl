<table cellpadding="4" cellspacing="1" width="100%">
<tr class="bgcolor1">
<td><b>{NAME_LBL}</b></td>
<td><b>{MEMBER_STATUS_LBL}</b></td>
<!-- BEGIN ACTIONS_LBL -->
<td><b>{ACTION_LBL}</b></td>
<!-- END ACTIONS_LBL -->
</tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{NAME}</td>
    <td>{MEMBER_STATUS}</td>
<!-- BEGIN ADMIN_ACTIONS -->
    <td>{ADMIN_ACTIONS}</td>
<!-- END ADMIN_ACTIONS -->
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
