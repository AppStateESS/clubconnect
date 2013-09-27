<h2>Search Results</h2>

<table class="search-results">
    <tr>
        <th>Name</th>
        <th>Email Address</th>
        <!-- BEGIN banner_id -->
        <th>{BANNER_ID_LABEL}</th>
        <!-- END banner_id -->

        <th>Action</th>
    </tr>
    <!-- BEGIN empty_table -->
    <tr>
        <td colspan="4">{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END empty_table -->
    
    <!-- BEGIN listrows -->
    <tr class="{ADVISOR_CLASS}">
        <td>{NAME}</td>
        <td>{EMAIL}</td>
        <td>{BANNER_ID}</td>
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
