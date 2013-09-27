<table class="table">
    <tr>
        <th>{HEAD_NAME}</th>
        <th>{HEAD_DATE}</th>
        <th>{HEAD_ADMIN_APPROVED}</th>
        <th>{HEAD_PRES_APPROVED}</th>
        <th>{HEAD_ADVISOR_APPROVED}</th>
        <th>{HEAD_ACTIONS}</th>
    </tr>
<!-- BEGIN NO_RESULTS -->
    <tr>
        <td colspan="4">{NO_RESULTS}</td>
    </tr>
<!-- END NO_RESULTS -->
<!-- BEGIN ROW -->
    <tr>
        <td>{NAME}</td>
        <td>{DATE}</td>
        <td class="{ADMIN_WARNING}">{ADMIN_APPROVED}</td>
        <td class="{PRES_WARNING}">{PRES_APPROVED}</td>
        <td class="{ADVISOR_WARNING}">{ADVISOR_APPROVED}</td>
        <td>{ACTIONS}</td>
    </tr>
<!-- END ROW -->
</table>
