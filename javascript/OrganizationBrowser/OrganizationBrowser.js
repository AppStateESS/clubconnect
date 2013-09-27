renderResults = function(orgBrowser, resultsArea, data, onClickFunction)
{
    resultsArea.html('<ul class="orgbrowser_sorted"></ul>');
    browser = resultsArea.find('.orgbrowser_sorted');
    
    if(data.length == 0){
    	browser.append('<li class="orgbrowser_empty">No matching clubs found.</li>');
    }
    
    var lastcat = 'NOCAT';
    for(var i = 0; i < data.length; i++) {
        var result = data[i];

        if(result.category != lastcat) {
            browser.append('<li class="orgbrowser_category">' + result.category + '</li>');
            lastcat = result.category;
        }

        var org = $('<li class="orgbrowser_org"><a orgid="' + result.id + '" href="' + result.uri + '">' + result.name + '</a></li>');
        browser.append(org);
    }

    resultsArea.find('a').click(onClickFunction);
}

selectOrganizationCallback = function(orgBrowser, resultsArea, clicked, resetViewFunc, fieldsToSet, e)
{
    if($(clicked).hasClass('orgbrowser_empty')) return false;
    
    // Construct the 'change' link
    changeLink = "<a href='javascript: return false;' id='search-change' class='search-change'>change</a>";
    
    selected = jQuery(orgBrowser).find('.search-results');

    // Append the change link to the DOM
    selected.append('<p>' + $(clicked).text() + ' [' + changeLink + ']</p>');
    selected.append('<input type="hidden" value="' + $(clicked).attr('orgid') + '" name="parent" />');
    
	// Hide the things we don't need anymore
	_hideInput(orgBrowser);
    $(resultsArea).children('.orgbrowser_sorted').remove();

    // Set the 'change' link's click event handler
    selected.find('a').click(function(e) {
        _showInput(orgBrowser);
        resetViewFunc();
        selected.html('');
        if(typeof onOrgCleared == 'function') onOrgCleared();
        return false;
    });

    if(typeof onOrgSelected == 'function') onOrgSelected($(clicked).attr('orgid'));

    e.preventDefault();
    return false;
}
