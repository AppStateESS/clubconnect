<!-- BEGIN ROSTER -->
<h1>{ROSTER_HEADING}</h1>
<table>
    <tr><th>Name</th><th>Class</th><th>Current</th><th>Cumulative</th></tr>
    <!-- BEGIN MEMBER -->
    <tr><td>{NAME}</td><td>{CLASS}</td><td>{SEM}</td><td>{CUM}</td></tr>
    <!-- END MEMBER -->
    <tr><th colspan="2">Average:</th><td>{AVGMEMSEM}</td><td>{AVGMEMCUM}</td></tr>
</table>
<strong>Total Number of Members: {MEMCOUNT}</strong>
<br /><br />
<!-- END ROSTER -->
<!-- BEGIN SUMMARY -->
<br /><br />
<h1>UNIVERSITY TOTALS</h1>
<table>
    <tr><th>&nbsp;</th><th>Current GPA</th><th>Cumulative GPA</th></tr>
    <!-- BEGIN SUMMARY_ROW -->
    <tr><th>{HEADING}</th><td>{SEMGPA}</td><td>{CUMGPA}</td></tr>
    <!-- END SUMMARY_ROW -->
</table>
<!-- END SUMMARY -->
<br /><br />
<p><em>*Note: Students with 'N/A' in the Current Semester column were involved in Study Abroad, Student Teaching, or another academic activity that does not grant a grade.</em></p>
