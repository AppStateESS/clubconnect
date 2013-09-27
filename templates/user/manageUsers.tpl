{JAVASCRIPT}

<h1>{TITLE}</h1><br />

<!-- BEGIN STATUS -->
<span style="color:red;">{STATUS}</span>
<!-- END STATUS -->

<i>Use the drop down menu shown below to locate an organization.</i><br /><br />

{START_FORM}
<table>
<tr>
  <td valign="top">Categories:&nbsp;&nbsp;</td>
  <td>{TYPES}</td>
</tr>
<tr>
  <td valign="top">Organizations:&nbsp;&nbsp;</td>
  <td><!-- {ORGANIZATIONS} --> {ORGANIZATION_SWAPPER}
   <br />
  </td>
</tr>
</table>
{LEVEL_ADVISOR} Advisor<br />
{LEVEL_CLUB_ADMIN} Student Representative

<br /><br />
{ASSIGNED_GROUPS_LABEL}:<br />
{ASSIGNED_GROUPS}
<br /><br />
{GO}
{END_FORM}

