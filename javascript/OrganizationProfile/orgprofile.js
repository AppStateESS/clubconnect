/**
 * Callback function for the OrganizationBrowser
 */

var profile = jQuery("#profile");

function sdrOrganizationProfile(id) {
    $(".sdr-profile").hide("slow");
    $("#profile").hideMessages();
    $("#profile").showLoading();

    jQuery.ajax({
        dataType: "html",
        error: _organizationProfileError,
        success: _organizationProfileCallback,
        url: "index.php",
        data: {
            module: "sdr",
            ajax: "html",
            action: "ShowOrganizationProfile",
            organization_id: id
        }
    });
}

function _organizationProfileError(request, status, error)
{
    $("#profile").hideLoading();
    $("#profile").text("Error loading data for specified organization.");
}

function _organizationProfileCallback(data, status)
{
    $("#profile").hideLoading();
    $("#profile").html(data);

    if($("#profile").children().length == 0) {
        $("#profile").showMessage("No Description Available.");
    }

    $(".sdr-profile").hide();
    $(".sdr-profile").show("slow");
}

$.fn.showMessage = function(message) {
    this.append('<div class="_op_message">' + message + '</div>');
    this.children("._op_message").show("slow");
}

$.fn.hideMessages = function() {
    this.children("._op_message").hide("slow", function() {
        $(this).remove();
    });
}
