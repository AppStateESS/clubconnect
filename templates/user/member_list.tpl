<table cellpadding="4" cellspacing="1" width="100%">
<tr class="bgcolor1">
<td><b>{ORGANIZATION_LBL}</b></td>
<td><b>{MEMBER_STATUS_LBL}</b></td>
<!-- <td><b>{SEMESTER_LBL}</b></td> -->
</tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{ORGANIZATION}</td>
    <td>{MEMBER_STATUS}</td>
<!--    <td>{SEMESTER}</td> -->
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
