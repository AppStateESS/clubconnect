<script type="text/javascript" src="mod/sdr/javascript/OrganizationApplicationForm/jquery.form.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#oaf-tabs").tabs();

    // This is the variable for the "Review" tab index.
    var ReviewIndex = 6;

    // This is the action to review the form
    var ReviewAction = 'VerifyOrganizationApplication';

    // Setup the next/back buttons
    $(".tab-nav").button();
    $(".tab-nav.next").bind('click', function(){
        $("#oaf-tabs").tabs("select", $("#oaf-tabs").tabs("option", "selected") + 1);
        //TODO set scroll position
        });
    $(".tab-nav.back").bind('click', function(){
        $("#oaf-tabs").tabs("select", $("#oaf-tabs").tabs("option", "selected") - 1);
        //TODO set scroll position
        });
    
    // This section controls which DIVs to show depending on the position of
    // various radio buttons in the form.
    $("input[name='has_history']").change(function(e) {
        if($(this).val() == 0) {
            $("#has-history").hide('slow');
            $("#history-type").hide();
        }
        if($(this).val() == 1) {
            $("#has-history").show('slow');
            if($("input[name='parent']").val()) {
                $("#history-type").show();
            }
        }
    });

    $('#organization_application').keypress(function(e) {
        return e.keyCode !== 13;
    });

    $("#organization_application_parent").change(function(e) {
        // TODO: AJAX to server, get past info
        var selected = $(this).children("[selected]");
        console.log(selected);
        if(selected.val() == 0) {
            $("#organization_application_name").val("");
        } else {
            $("#organization_application_name").val(selected.text());
        }
    });

    $("input[name='has_website']").change(function(e) {
        if($(this).val() == 0) {
            $("#website-yes").hide('slow');
            $("#website-yes input").attr('disabled', 'disabled');
            $("#website-no input").removeAttr('disabled');
            $("#website-no").show('slow');
        }
        if($(this).val() == 1) {
            $("#website-no").hide('slow');
            $("#website-no input").attr('disabled', 'disabled');
            $("#website-yes input").removeAttr('disabled');
            $("#website-yes").show('slow');
        }
    });

    $("input[name='wants_website']").change(function(e) {
        if($(this).val() == 0)
            $("#wants-website").hide('slow');
        if($(this).val() == 1)
            $("#wants-website").show('slow');
    });
    
    $("input[name='user_type']").change(function(e) {
        $("#go-back-4").hide();
        if($(this).val() == 1) {
            $("#search-pres").hide();
            $("#search-advisor").show();
        }
        if($(this).val() == 2) {
            $("#search-pres").show();
            $("#search-advisor").hide();
        }
        if($(this).val() == 0) {
            $("#search-pres").show();
            $("#search-advisor").show();
        }
    });

    $("a#advisor-specify").click(function(e) {
        $("#search-advisor").hide('slow');
        $("#specify-advisor").show('slow');
        $("#specify-advisor input").removeAttr('disabled');
        return false;
    });

    $("a#advisor-search").click(function(e) {
        $("#specify-advisor").hide('slow');
        $("#specify-advisor input").attr('disabled', 'disabled');
        $("#search-advisor").show('slow');
        return false;
    });

    // Hide all the optional DIVs by default
    $("#has-history").hide();
    $("#website-yes").hide();
    $("#website-yes input").attr('disabled', 'disabled');
    $("#website-no").hide();
    $("#website-no input").attr('disabled', 'disabled');
    $("#wants-website").hide();
    $("#search-pres").hide();
    $("#search-advisor").hide();
    $("#specify-advisor").hide();
    $("#specify-advisor input").attr('disabled', 'disabled');
    $("#history-type").hide();

    // Show some DIVs depending on values coming from PHP
    if($("input[name='has_history']:checked").val() == 1) {
        $("#has-history").show();
        $("#history-type").show();
    }

    if($("input[name='has_website']:checked").val() == 1) {
        $("#website-yes").show();
        $("#website-yes input").removeAttr('disabled');
    }
    if($("input[name='has_website']:checked").val() == 0) {
        $("#website-no").show();
        $("#website-no input").removeAttr('disabled');
    }

    if($("input[name='wants_website']:checked").val() == 1)
        $("#wants-website").show();

    if($("input[name='user_type']:checked").val() == 1)
        $("#search-advisor").show();
    if($("input[name='user_type']:checked").val() == 2)
        $("#search-pres").show();
    if($("input[name='user_type']:checked").val() == 0) {
        $("#search-advisor").show();
        $("#search-pres").show();
    }

    if( $("input[name='req_advisor_name']").val() ||
        $("input[name='req_advisor_dept']").val() ||
        $("input[name='req_advisor_bldg']").val() ||
        $("input[name='req_advisor_phone']").val() ||
        $("input[name='req_advisor_email']").val()) {
        $("#search-advisor").hide();
        $("#specify-advisor").show();
        $("#specify-advisor input").removeAttr('disabled');
    }

    // The last tab is magical; it submits the form for verification and
    // shows any problems with links back to them.
    $("#oaf-tabs").bind("tabsselect", function(event, ui) {
        if(ui.index == ReviewIndex) {
            doReview($("#organization_application"), $(ui.panel).find(".review"));
        }
    });

    doReview = function(form, target) {
        oldAction = form.find("input[name='action']").val();
        form.find("input[name='action']").val(ReviewAction);
        form.append("<input name='ajax' value='json' />");
        target.html('<img src="mod/sdr/img/loading.gif" style="vertical-align: middle" />');
        form.find("input[type='submit']").attr('disabled', 'disabled').val('Verifying Form...');

        form.ajaxSubmit({dataType: 'json', success: function(data, statusText, xhr, form) {
            target.html(data.view);
            if(data.errors == 0) {
                form.find("input[type='submit']").removeAttr('disabled').val('Click Here to Submit Registration Form');
            } else {
                form.find("input[type='submit']").val('Please fix the errors listed above.');
            }
        }});

        form.find("input[name='action']").val(oldAction);
        form.find("input[name='ajax']").remove();
    }
});

var onOrgSelected = function(orgId) {
    if(!orgId) return false;

    ajaxParams = {
        module: "sdr",
        ajax: "json",
        action: "GetOrganizationAjax",
        organization_id: orgId
    };

    jQuery.ajax({
        dataType: "json",
        error: _orgSelectedError,
        success: _orgSelectedSuccess,
        url: "index.php",
        data: ajaxParams
    });
}

var onOrgCleared = function() {
    $("#history-type-value").text('');
    $("#history-type").hide();
}

var _orgSelectedError = function(request, status, error) {
    console.log(request);
    console.log(status);
    console.log(error);
}

var _orgSelectedSuccess = function(data, status) {
    $("#history-type-value").text(data.category);
    $("#history-type").show();

    $("input[name=name]").val(data.name);
    $("input[name=address]").val(data.address);
}
</script>
