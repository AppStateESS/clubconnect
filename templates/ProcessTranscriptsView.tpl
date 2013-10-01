<div class="sdr-box">
    <div class="sdr-box-head">
        {MENU}
        <h1>{TITLE}</h1>
    </div>
    <table class="transcript-requests">
        <tr>
            <th>{NAME_LABEL}</th>
            <th>{DATE_LABEL}</th>
            <!-- BEGIN STATUS_LABEL --><th>{STATUS_LABEL}</th><!-- END STATUS_LABEL -->
            <th>{ACTIONS_LABEL}</th>
        </tr>
<!-- BEGIN empty_table -->
        <tr>
            <td colspan="4">{EMPTY_MESSAGE}</td>
        </tr>
<!-- END empty_table -->

<!-- BEGIN listrows -->
        <tr class="{STATUS_CLASS}">
            <td>{NAME}</td>
            <td>{DATE}</td>
            <!-- BEGIN STATUS --><td>{STATUS}</td><!-- END STATUS -->
            <td>{ACTIONS}</td>
        </tr>
<!-- END listrows -->
    </table>
    <br />
    <!-- BEGIN page_label -->
    <div>
    Results: {TOTAL_ROWS}
    </div>
    <!-- END page_label -->
    <!-- BEGIN pages -->
    <div>
    {PAGE_LABEL}: {PAGES}
    </div>
    <!-- END pages -->
    <!-- BEGIN limits -->
    <div>
    {LIMIT_LABEL}: {LIMITS}
    </div>
    <!-- END limits -->
      <a class="btn btn-default" href="{PRINT_SETTINGS_URI}">Print Settings (Advanced)</a>
</div>
