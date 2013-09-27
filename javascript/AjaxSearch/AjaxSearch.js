$.fn.ajaxSearch = function(settings) {

    settings = jQuery.extend({
        selectedId:  null,
        searchDelay: null,
        ajaxUri: null,
        queryParams: null,
        searchWhenEmpty: null,
        renderCallback: null, // A function to call to render the results of
								// the AJAX query
        selectedUri: null, // A URI to go to when a callback is not set
        selectedUriKey: null, // The key for the value supplied to the above URI
        selectedAttr: null, // The attribute to pass to selectedUri
        selectedCallback: null, // A function to call when an organization is
								// selected
        fieldsSetOnSelect: null
    }, settings);
    
    jQuery(this).each(function() {

	    var jQueryMatched = this;
	    
	    var opts = jQuery.extend({}, settings);
	    
	    var input				= null;
	    var timeout				= null;
	    var results				= null;
	    var selected			= null;
	    var selectedHiddenField = null;
	    var loadingGif			= null;
	    
	    function _initialize() {
	        jQuery(jQueryMatched).html('<div style="height:30px" class="search">Search:<input style="margin-top:10px" id="orgSearch" type="text" /><img id="loadingGif" src="mod/sdr/img/loading.gif" style="vertical-align:middle"></div><div class="search-results"></div><div class="search-selected"></div>');
	        input = jQuery(jQueryMatched).find('#orgSearch');
	        results = jQuery(jQueryMatched).find('.search-results');
	        selected = jQuery(jQueryMatched).find('.search-selected');
	        loadingGif = jQuery(jQueryMatched).find('#loadingGif');
	        loadingGif.hide();
	        
	        input.keydown(_inputKeydown);
	        _submitSearch();
	    }
	
	    function _inputKeydown(e) {
	        clearTimeout(timeout);
	
	        if(e.charCode == 13) {
	            _submitSearch();
	            return false;
	        }
	
	        timeout = setTimeout(_submitSearch, opts.searchDelay);
	    }
	
	    function _submitSearch() {
	    	
	    	if(!opts.searchWhenEmpty && input.val().length == 0){
	    		$(results).html('');
	    		return;
	    	}
	    	
	        if(input.val().length < 3 && input.val().length > 0)
	            return;
	
	        $(loadingGif).show();
	        
	        ajaxParams = {
	                searchFor: input.val(),
	            };
	        
	        ajaxData = $.extend(ajaxParams, opts.queryParams);
	        
	        jQuery.ajax({
	            dataType: "json",
	            error: _submitSearchError,
	            success: _submitSearchCallback,
	            url: opts.ajaxUri,
	            data: ajaxData
	        });
	    }
	
	    function _submitSearchError(request, status, error) {
	    	$(loadingGif).hide();
	        $(jQueryMatched).html('Error performing search.');
	        console.log(request);
	        console.log(status);
	        console.log(error);
	    }
	
	    function _submitSearchCallback(data, status) {
	    	$(loadingGif).hide();
	    	opts.renderCallback(jQueryMatched, results, data, _resultClicked);
	    }
	
	    function _resultClicked(e) {
	        //console.log('Clicked:');
	    	// console.log(e);
	        
	    	// If there's a callback function set, we'll use it
	    	if(opts.selectedCallback != null){
	    		//console.log('callback!');
	    		// window[opts.selectedCallback]();//arguments
	    		// window['$']['fn']['organizationBrowser'][opts.selectedCallback]();
	    		if(typeof opts.selectedCallback == 'function'){
	    			opts.selectedCallback(jQueryMatched, results, this, _submitSearch, opts.fieldsSetOnSelect, e);
	    		}
                        e.preventDefault();
	    	// Otherwise, check for a URI
	    	}else if(opts.selectedUri != null){
//                console.log(opts.selectedUri + $(this).attr(opts.selectedAttr));return;
	    		window.location = opts.selectedUri + $(this).attr(opts.selectedAttr);
                        e.preventDefault();
	    	}else{
	    		console.log('nothing');
	    		// Do nothing
	    	}
	    }
	
	    _initialize();
    });
}

function _hideInput(orgBrowser) {
    $(orgBrowser).find('.search').hide();
}

function _showInput(orgBrowser) {
    $(orgBrowser).find('.search').show();
}
