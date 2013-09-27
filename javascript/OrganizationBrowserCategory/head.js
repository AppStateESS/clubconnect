<style type="text/css">@import url("javascript/modules/sdr/OrganizationBrowserCategory/style.css")</style>
<script type="text/javascript" src="javascript/modules/sdr/OrganizationBrowserCategory/jquery.selectboxes.js"></script>
<script type="text/javascript" src="javascript/modules/sdr/OrganizationBrowserCategory/orgbrowser.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('#orgbrowser').sdrOrganizationBrowser({
<!-- BEGIN CATEGORY_SELECTED -->
        categorySelected: {CAT_SEL_CALLBACK},
<!-- END CATEGORY_SELECTED -->
<!-- BEGIN ORGANIZATION_SELECTED -->
        organizationSelected: {ORG_SEL_CALLBACK},
<!-- END ORGANIZATION_SELECTED -->
<!-- BEGIN JUST_SUBMIT -->
        submitOnOrganization: true,
        submitValues: [
<!-- BEGIN PARAMS -->
            ["{PARAM}", "{VALUE}"],
<!-- END PARAMS -->
["ob_templatehack", "templatehack"]
        ],
<!-- END JUST_SUBMIT -->
        jqueryTemplateHack: "jqueryTemplateHack"
    });
});

</script>
