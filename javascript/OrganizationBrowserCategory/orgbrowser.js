$.fn.sdrOrganizationBrowser = function(settings) {

    settings = jQuery.extend({
        categorySelected: false,     // (function) Callback when a category is selected
        organizationSelected: false, // (function) Callback when an organization is selected
        submitOnOrganization: false, // (boolean)  If true, will submit form when an organization is selected
        submitValues: []             // (object)   Additional hidden values if submitOnOrganization is true
    }, settings);

    // 'this' is going to change, so let's keep the original set around
    var jQueryMatched = this;

    /**
     * Initialize the Organization Browser
     */
    function _initialize() {
        jQueryMatched.html('<div class="_ob_error"></div><form></form>');
    }

    /**
     * Get Categories and populate a select box for the user.
     */
    function _getCategories(obj) {
        jQueryMatched.showLoading();
        jQuery.ajax({
            dataType: "json",
            error: _getCategoriesError,
            success: _getCategoriesCallback,
            url: "index.php",
            data: {
                module: "sdr",
                ajax: "json",
                action: "AjaxGetCategories"
            }
        });
    }

    /**
     * Callback for Get Categories AJAX - on success
     */
    function _getCategoriesCallback(data, status)
    {
        jQueryMatched.hideLoading();

        if(jQuery.makeArray(data).length == 0){
            jQueryMatched.children("._ob_error").text("There are no organizations registered at this time. Please check back later.");
            return;
        }

        jQueryMatched.children("form").append('<select id="ob_categories" name="ob_categories"></select>');
        var select = jQueryMatched.children("form").children("#ob_categories");

        select.addOption({"0" : "Select a Category..."}).addOption(data).selectOptions("0");

        select.change(_categorySelected);
    }

    /**
     * Error Callback for Get Categories AJAX
     */
    function _getCategoriesError(request, status, error)
    {
        jQueryMatched.hideLoading();
        jQueryMatched.children("._ob_error").text('Error loading SDR.');
    }

    /**
     * Get Organizations by category and populate a select box for the user.
     */
    function _getOrganizations(catid)
    {
        jQueryMatched.showLoading();
        jQuery.ajax({
            dataType: "json",
            error: _getOrganizationsError,
            success: _getOrganizationsCallback,
            url: "index.php",
            data: {
                module: "sdr",
                ajax: "json",
                action: "AjaxGetOrganizations",
                catid: catid
            }
        });
    }

    /**
     * Callback for Get Organizations AJAX - on success
     */
    function _getOrganizationsCallback(data, status)
    {
        jQueryMatched.hideLoading();

        if($.makeArray(data).length == 0){
            jQueryMatched.children("._ob_error").text("Organization list temporarily unavailable.");
            return;
        }

        jQueryMatched.children("form").append('<select id="ob_organizations" name="organization_id"></select>');
        var select = jQueryMatched.children("form").children("#ob_organizations");

        select.addOption({"0" : "Select an Organization..."}).addOption(data).selectOptions("0");

        select.change(_organizationSelected);
    }

    /**
     * Error Callback for Get Organizations AJAX
     */
    function _getOrganizationsError(request, status, error)
    {
        jQueryMatched.hideLoading();
        jQueryMatched.children("._ob_error").text('Error loading SDR.');
    }

    /**
     * Event handler for Categories Drop-down
     */
    function _categorySelected()
    {
        value = jQueryMatched.children("form").children("#ob_categories").selectedValues()[0];

        // Clear out any existing organization list
        jQueryMatched.children("form").children("#ob_organizations").remove();

        if(value == 0) {
            return true;
        }

        if(typeof(settings.categorySelected) == 'function') {
            settings.categorySelected(value);
        }

        _getOrganizations(value);
    }

    /**
     * Event handler for Organizations Drop-down
     */
    function _organizationSelected()
    {
        value = jQueryMatched.children("form").children("#ob_organizations").selectedValues()[0];

        // If they didn't select one, do nothing
        if(value == 0) {
            return true;
        }

        if(typeof(settings.organizationSelected) == 'function') {
            settings.organizationSelected(value);
        }

        if(settings.submitOnOrganization) {
            _submitForm();
        }
    }

    /**
     * Submits Form for processing, loading organization profile page
     */
    function _submitForm()
    {
        var form = jQueryMatched.children("form");

        jQuery.each(settings.submitValues, function() {
            if(this[0] == "ob_templatehack") { return }; 
            form.append('<input type="hidden" name="' + this[0] + '" value="' + this[1] + '" />');
        });

        form.submit();
    }

    _initialize();
    _getCategories();
};

/**
 * Inserts 'loading' text and an image into a div that is loading.
 */
$.fn.showLoading = function(settings) {
    this.append('<div class="_ob_loading">Loading...</div>');
    this.children("._ob_loading").show("slow");
}

/**
 * Removes anything inserted by $().loading
 */
$.fn.hideLoading = function(settings) {
    this.children("._ob_loading").remove();
/*    this.children("._ob_loading").hide("slow", function() {
        $(this).remove();
    });*/
}
