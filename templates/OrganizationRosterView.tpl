<!-- BEGIN UNREGISTERED -->
<div class="alert-danger alert"><p>This club is not registered for {UNREG_TERM}.  The Roster cannot be managed unless a club is registered.</p></div>
<!-- END UNREGISTERED -->
<!-- BEGIN LINKS -->[{ADD_MBR_LINK}] [{ADD_MULT_MBRS_LINK}]<!-- END LINKS -->

<!-- BEGIN ROSTER -->
<table class="organization-roster">
    <tr>
        <th>{ADMIN_HDR}</th>
        <th>{NAME_HDR}</th>
        <th>{ROLE_HDR}</th>
        <th class="actions">{ACTIONS_HDR}</th>
    </tr>
<!-- BEGIN MEMBER -->
    <tr class="{LEVEL}">
        <td>{ADMIN}</td>
        <td>{NAME}</td>
        <td>{ROLE} <!-- BEGIN CHANGE -->{ROLE_CHANGE}<!-- END CHANGE --></td>
        <td class="actions">{ACTIONS}</td>
    </tr>
<!-- END MEMBER -->
</table>
<!-- END ROSTER -->
<!-- BEGIN EMPTY_ROSTER -->
<div class="organization-roster-empty">
{EMPTY_MESSAGE}
</div>
<!-- END EMPTY_ROSTER -->
