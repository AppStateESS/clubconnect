<script type="text/javascript">
$(function() {
    $("input[name='registered']").change(function(e) {
        console.log($(this).val());
        console.log($("#if-org-registered"));
        if($(this).attr('checked')) {
            $("#if-org-registered").show('slow');
            $("#if-org-registered input").removeAttr('disabled');
        } else {
            $("#if-org-registered").hide('slow');
            $("#if-org-registered input").attr('disabled', 'disabled');
        }
    });

    $("#if-org-registered").hide();
    $("#if-org-registered input").attr('disabled', 'disabled');

    if($("input[name='registered']").attr('checked')) {
        $("#if-org-registered").show();
        $("#if-org-registered input").removeAttr('disabled');
    }
});
</script>
