<script language="javascript">
$("document").ready(function() {
	$(".tr-manual-address").hide();
	
	$("input[name='delivery_method']").click(function() {
		if($("input[name='delivery_method']:checked").val() == -1) {
			$(".tr-manual-address").show("slow");
		} else {
			$(".tr-manual-address").hide("slow");
		}
	});
});
</script>
<div class="sdr-box">
    <div class="sdr-box-head">
    <h1>{PAGE_TITLE}</h1>
    <h3>Fill out the form below to request a copy of your customized
co-curricular transcript.</h3>
    </div>

    {START_FORM}

<!-- this is getting rid of the number of transcripts option -->
<!--    <div class="tr-number-copies"> -->
<!--        <h3>{NUMBER_COPIES_LABEL} {NUMBER_COPIES}</h3> -->
<!--    </div> -->
    
    <div class="tr-addresses">
        <h3>{ADDRESS_SELECT}</h3>
        <table>
<!-- BEGIN delivery_method_repeat -->
            <tr>
                <td>{DELIVERY_METHOD}</td>
                <td>{DELIVERY_METHOD_LABEL}</td>
            </tr>
<!-- END delivery_method_repeat -->
        </table>
    </div>
    
    <div class="label-above-edit tr-manual-address">
    
    <div class="multi-line">
    {ADDRESS_1_LABEL}
    {ADDRESS_1}
    </div>
    <div class="multi-line">
    {ADDRESS_2_LABEL}
    {ADDRESS_2}
    </div>
    <div class="multi-line">
    {ADDRESS_3_LABEL}
    {ADDRESS_3}
    </div>
    <div class="single-line"><div>
    {CITY_LABEL}
    {CITY}
    </div></div>
    <div class="single-line"><div>
    {STATE_LABEL}
    {STATE}
    </div></div>
    <div class="single-line"><div>
    {ZIP_LABEL}
    {ZIP}
    </div></div>
    <div class="clear"></div>
    
    </div>
    
    <div class="tr-email">
        <h3>{EMAIL_LABEL}</h3>
        {EMAIL}
    </div>

    <div class="tr-submit">{SUBMIT}</div>

    {END_FORM}
</div>
