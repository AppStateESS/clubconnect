renderPersonResults = function(orgBrowser, resultsArea, data, onClickFunction)
{
	resultsArea.html('<dl class="orgbrowser_sorted"></dl>');
    dl = resultsArea.find('dl');

    console.log(orgBrowser);
    
    if(data.length == 0){
        dl.append('<dd class="noresult">No matching people found.</dd>');
    }

    for(var i = 0; i < data.length; i++) {
        var result = data[i];

        var name = ((result.last_name   == null) ? '' : result.last_name) +
                   ((result.suffix      == null) ? '' : " " + result.suffix) + ", " +
                   ((result.prefix      == null) ? '' : result.prefix) + " " +
                   ((result.first_name  == null) ? '' : result.first_name) + " " +
                   ((result.middle_name == null) ? '' : result.middle_name) + " ";

        if(typeof result.id != 'undefined') {
            var sdt = $('<dd member_id="' + result.id + '">' + name + '</dd>');
        } else {
            var sdt = $('<dd username="' + result.username + '">' + name + '</dd>');
        }
        dl.append(sdt);
    }

    resultsArea.find('dd').click(onClickFunction);
    resultsArea.find('dd').css('cursor', 'pointer');
}

selectPerson = function(orgBrowser, resultsArea, clicked, resetViewFunc, fieldsToSet)
{
    if($(clicked).hasClass('noresult')) return false;
    
    // Construct the 'change' link
    changeLink = "<a href='javascript: return false;' id='search-change' class='search-change'>change</a>";
    
    selected = jQuery(orgBrowser).find('.search-results');
    
    // Append the change link to the DOM
    selected.append('<p>' + $(clicked).text() + ' [' + changeLink + ']</p>');

    // Hide the things we don't need anymore
    _hideInput(orgBrowser);
    $(resultsArea).children('dl').remove();

    $.each(fieldsToSet, function(index, value){
        jQuery('[name="'+value+'"]').val($(clicked).attr('member_id'));
    });
    
    // Set the 'change' link's click event handler
    selected.find('a').click(function(e) {
        _showInput(orgBrowser);
        resetViewFunc();
        selected.html('');
        return false;
    });
    
    return false;
}
