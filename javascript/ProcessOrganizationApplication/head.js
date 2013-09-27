<script type="text/javascript">
$(document).ready(function() {
    var init = function() {
        $("#change-type").click(function(e) {
            $("#orgtype").html('<img id="loadingGif" src="mod/sdr/img/loading.gif" style="vertical-align: middle" />');

            ajaxData = {
                module: "sdr",
                ajax: "json",
                action: "GetCategories"
            };

            jQuery.ajax({
                dataType: "json",
                error: _getChangeTypeError,
                success: _getChangeTypeCallback,
                url: "index.php",
                data: ajaxData
            });

            return false;
        });
    }

    var _getChangeTypeError = function(request, status, error) {
        $("#orgtype").html('Error requesting list of categories.');
        console.log(request);
        console.log(status);
        console.log(error);
    }

    var _getChangeTypeCallback = function(data, status) {
        $("#orgtype #loadingGif").remove();
        $("#orgtype").html("<select id='change-type-select'></select>");

        $("#change-type-select").append("<option value='0'>Choose a Category...</option>");

        for(var i = 0; i < data.length; i++) {
            $("#change-type-select").append("<option value='" + data[i].id + "'>" + data[i].name + "</option>");
        }

        $("#change-type-select").change(function(e) {
            if($(this).val() < 1) return false;

            ajaxData = {
                module: "sdr",
                ajax: "json",
                action: "SetCategoryForOrgApp",
                app_id: $("input[name=app_id]").val(),  // TODO: This will absolutely break someday.
                type_id: $(this).val()
            };

            jQuery.ajax({
                dataType: "json",
                error: _changeTypeError,
                success: _changeTypeSuccess,
                url: "index.php",
                data: ajaxData
            });

            $(this).attr('disabled', 'disabled');
            $(this).parent().append('<img id="loadingGif" src="mod/sdr/img/loading.gif" style="vertical-align: middle" />');
        });
    }

    var _changeTypeError = function(request, status, error) {
        $("#orgtype").html('Error changing type.');
        console.log(request);
        console.log(status);
        console.log(error);
    }

    var _changeTypeSuccess = function(data, status) {
        var cat = $("#change-type-select option:selected").text();

        var dd = $("#orgtype");

        dd.html(cat + ' [<a href="#" id="change-type">Change</a>]');
        init();

        $("#approve_action input[type=submit]").removeAttr('disabled');
    }

    init();
});
</script>
