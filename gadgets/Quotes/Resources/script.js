/**
 * Quotes Javascript actions
 *
 * @category   Ajax
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var QuotesCallback = {
    addquotestogroup: function(response) {
        showResponse(response);
    }
}

/**
 * Fills the quotes combo
 */
function fillQuotesCombo() 
{
    var box = $('quotes_combo');
    box.options.length = 0;
    var quotes = QuotesAjax.callSync('getquotes', -1, $('group_filter').value);
    if (quotes.length > 0) {
        quotes.each(function(value, index) {
            box.options[box.options.length] = new Option(value['title'].defilter(), value['id']);
        });
    }
    stopAction();
}

/**
 * Clean the form
 */
function stopAction() 
{
    switch(currentAction) {
    case 'Groups':
        $('gid').value         = 0;
        $('title').value       = '';
        $('view_mode').value   = '0';
        $('view_type').value   = '0';
        $('show_title').value  = 'true';
        $('limit_count').value = '0';
        $('random').value      = 'false';
        $('published').value   = 'true';
        $('groups_combo').selectedIndex = -1;

        $('add_quotes').style.display = 'none';
        $('btn_del').style.display    = 'none';
        break;
    case 'GroupQuotes':
        editGroup($('gid').value);
        break;
    case 'Quotes':
        $('id').value          = 0;
        $('title').value       = '';
        $('show_title').value  = 'true';
        $('published').value   = 'true';
        $('gid').selectedIndex = $('group_filter').selectedIndex -1;
        $('start_time').value  = '';
        $('stop_time').value   = '';
        changeEditorValue('quotation', '');
        $('quotes_combo').selectedIndex = -1;
        $('btn_del').style.display = 'none';
        break;
    }
}

/**
 * Add/Update a Quote
 */
function saveQuote()
{
    if ($('title').value.blank() ||
        getEditorValue('quotation').blank() ||
        $('gid').value == 0)
    {
        alert(incompleteQuoteFields);
        return;
    }

    if($('id').value==0) {
        var response = QuotesAjax.callSync('insertquote',
                                            $('title').value,
                                            getEditorValue('quotation'),
                                            $('gid').value,
                                            $('start_time').value,
                                            $('stop_time').value,
                                            $('show_title').value == 'true',
                                            $('published').value == 'true');
        if (response[0]['type'] == 'response_notice') {
            if ($('group_filter').value == -1 || $('group_filter').value == $('gid').value) {
                var box = $('quotes_combo');
                box.options[box.options.length] = new Option(response[0]['data']['title'], response[0]['data']['id']);
            }
            stopAction();
        }
        showResponse(response);
    } else {
        var box = $('quotes_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('updatequote',
                                            $('id').value,
                                            $('title').value,
                                            getEditorValue('quotation'),
                                            $('gid').value,
                                            $('start_time').value,
                                            $('stop_time').value,
                                            $('show_title').value == 'true',
                                            $('published').value == 'true');
        if (response[0]['type'] == 'response_notice') {
            box.options[quoteIndex].text = $('title').value;
            stopAction();
        }
        showResponse(response);
    }
}

/**
 * Delete a Quote
 */
function deleteQuote()
{
    var answer = confirm(confirmQuoteDelete);
    if (answer) {
        var box = $('quotes_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('deletequote', box.value);
        if (response[0]['type'] == 'response_notice') {
            box.options[quoteIndex] = null;
            stopAction();
        }
        showResponse(response);
    }
}

/**
 * Edit a Quote
 *
 */
function editQuote(id)
{
    if (id == 0) return;
    var quoteInfo = QuotesAjax.callSync('getquote', id);
    currentAction = 'Quotes';
    $('id').value    = quoteInfo['id'];
    $('title').value = quoteInfo['title'].defilter();
    changeEditorValue('quotation', quoteInfo['quotation']);
    $('gid').value = quoteInfo['gid'];
    if (quoteInfo['gid'] == 0) {
        $('gid').selectedIndex= -1;
    }

    $('start_time').value  = (quoteInfo['start_time'] == null)? '': quoteInfo['start_time'];
    $('stop_time').value   = (quoteInfo['stop_time'] == null)? '': quoteInfo['stop_time'];
    $('show_title').value  = quoteInfo['show_title'];
    $('published').value   = quoteInfo['published'];

    $('btn_del').style.display = 'inline';
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (currentAction == 'GroupQuotes') {
        $('work_area').innerHTML = cacheGroupForm;
    }

    currentAction = 'Groups';
    var groupInfo = QuotesAjax.callSync('getgroup', gid);
    $('gid').value         = groupInfo['id'];
    $('title').value       = groupInfo['title'].defilter();
    $('view_mode').value   = groupInfo['view_mode'];
    $('view_type').value   = groupInfo['view_type'];
    $('show_title').value  = groupInfo['show_title'];
    $('limit_count').value = groupInfo['limit_count'];
    $('random').value      = groupInfo['random'];
    $('published').value   = groupInfo['published'];

    $('add_quotes').style.display = 'inline';
    $('btn_del').style.display    = 'inline';
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'Groups') {
        if ($('title').value.blank()) {
            alert(incompleteGroupFields);
            return false;
        }

        if($('gid').value==0) {
            var response = QuotesAjax.callSync('insertgroup',
                                                $('title').value,
                                                $('view_mode').value,
                                                $('view_type').value,
                                                $('show_title').value == 'true',
                                                $('limit_count').value,
                                                $('random').value == 'true',
                                                $('published').value == 'true');
            if (response[0]['type'] == 'response_notice') {
                var box = $('groups_combo');
                box.options[box.options.length] = new Option(response[0]['data']['title'], response[0]['data']['id']);
                stopAction();
            }
            showResponse(response);
        } else {
            var box = $('groups_combo');
            var groupIndex = box.selectedIndex;
            var response = QuotesAjax.callSync('updategroup',
                                                $('gid').value,
                                                $('title').value,
                                                $('view_mode').value,
                                                $('view_type').value,
                                                $('show_title').value == 'true',
                                                $('limit_count').value,
                                                $('random').value == 'true',
                                                $('published').value == 'true');
            if (response[0]['type'] == 'response_notice') {
                box.options[groupIndex].text = $('title').value;
                stopAction();
            }
            showResponse(response);
        }
    } else {
        var inputs  = $('work_area').getElementsByTagName('input');
        var keys    = new Array();
        var counter = 0;
        for (var i=0; i<inputs.length; i++) {
            if (inputs[i].name.indexOf('group_quotes') == -1) {
                continue;
            }

            if (inputs[i].checked) {
                keys[counter] = inputs[i].value;
                counter++;
            }
        }
        QuotesAjax.callAsync('addquotestogroup', $('gid').value, keys);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(confirmGroupDelete);
    if (answer) {
        var box = $('groups_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('deletegroup', box.value);
        if (response[0]['type'] == 'response_notice') {
            box.options[quoteIndex] = null;
            stopAction();
        }
        showResponse(response);
    }
}

/**
 * Show a simple-form with checkboxes so quotes can check their group
 */
function editGroupQuotes()
{
    if ($('gid').value == 0) return;
    if (cacheGroupQuotesForm == null) {
        cacheGroupQuotesForm = QuotesAjax.callSync('groupquotesui');
    }

    $('add_quotes').style.display = 'none';
    $('btn_del').style.display    = 'none';
    if (cacheGroupForm == null) {
        cacheGroupForm = $('work_area').innerHTML;
    }
    $('work_area').innerHTML = cacheGroupQuotesForm;

    currentAction = 'GroupQuotes';
    var quotesList = QuotesAjax.callSync('getquotes', -1, $('gid').value);
    var inputs  = $('work_area').getElementsByTagName('input');

    if (quotesList) {
        quotesList.each(function(value, index) {
            for (var i=0; i<inputs.length; i++) {
                if (inputs[i].name.indexOf('group_quotes') == -1) {
                    continue;
                }
                if (value['id'] == inputs[i].value) {
                    inputs[i].checked= true;
                    break
                }
            }
        });   
    }
}

var QuotesAjax = new JawsAjax('Quotes', QuotesCallback);

//Cache for saving the group-form template
var cacheGroupForm = null;

//Cache for saving the group quotes form template
var cacheGroupQuotesForm = null;

//Which action are we runing?
var currentAction = null;
