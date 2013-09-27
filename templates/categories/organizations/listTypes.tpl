<table cellpadding="4" cellspacing="1" width="100%">
<tr class="bgcolor1">
<td><b>{NAME_LBL}</b></td>
<td><b>{ACTION_LBL}</b></td>
</tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{NAME}</td>
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
