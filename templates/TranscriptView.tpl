<div style="background-color: white;">

    <!-- BEGIN MENU_LINKS -->
    <div style="float: right;">
    {PRINT_VIEW} | {REQUEST}
    </div>
    <!-- END MENU_LINKS -->
    
    <!-- BEGIN PRINT -->
    <script type="text/javascript">
        $(document).ready(function(){
            window.print();
        });
    </script>
    {PRINT}
    <div style="float:right;">
    <a href="javascript:window.print();" class="print-hidden"><img src="images/mod/sdr/tango-icons/actions/document-print.png" alt="Print"></img></a>
    </div>
    <!--  END PRINT -->
    
    <br />

<div style="text-align: center;">Appalachian State University<br />
Boone, North Carolina 28608<br />
<br />
<span style="font-size: larger;"><b>Student Record of
Involvement and Honors</b></span> <br />
Unofficial Transcript</div>
<br />

<table width="99%" style="margin: 6px;">
	<tr class="bgcolor1">
		<td style="border: thin solid black;">
		<div style="margin: 5px;">
		<table width="100%">
			<tr>
				<td><b>NAME:</b>&nbsp;&nbsp;{NAME}</td>
				<td align="right">&nbsp;</td>
			</tr>
		</table>
		</div>
		</td>
	</tr>

	<tr>
		<td><br />
		<div style="margin: 15px;">


			<!-- BEGIN term_repeat -->
			<strong>{TERM_LABEL}</strong>
			<br />
			<!--  BEGIN membership_repeat -->
			&nbsp;&nbsp;&nbsp;&nbsp;{EDIT} <span class="{CLASS}">{ROLE}, {ORGANIZATION}</span>
			<br />
			<!--  END membership_repeat -->
			<br />
			<!--  END term_repeat -->
		</div>
		<br />
		</td>
	</tr>

	<tr class="bgcolor1">
		<td style="border: thin solid black;">
		<div style="margin: 5px;"><!-- footer -->
		<table width="100%">
			<tr>
				<td><b>ISSUE DATE:</b> &nbsp;{DATE}</td>
			</tr>
		</table>
		<!-- footer --></div>
		</td>
	</tr>
</table>
<br />
<br />
</div>
