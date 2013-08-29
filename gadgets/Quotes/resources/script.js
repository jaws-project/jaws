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
    var box = _('quotes_combo');
    box.options.length = 0;
    var quotes = QuotesAjax.callSync('getquotes', -1, _('group_filter').value);
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
        _('gid').value         = 0;
        _('title').value       = '';
        _('view_mode').value   = '0';
        _('view_type').value   = '0';
        _('show_title').value  = 'true';
        _('limit_count').value = '0';
        _('random').value      = 'false';
        _('published').value   = 'true';
        _('groups_combo').selectedIndex = -1;

        _('add_quotes').style.display = 'none';
        _('btn_del').style.display    = 'none';
        break;
    case 'GroupQuotes':
        editGroup(_('gid').value);
        break;
    case 'Quotes':
        _('id').value          = 0;
        _('title').value       = '';
        _('show_title').value  = 'true';
        _('published').value   = 'true';
        _('gid').selectedIndex = _('group_filter').selectedIndex -1;
        _('start_time').value  = '';
        _('stop_time').value   = '';
        changeEditorValue('quotation', '');
        _('quotes_combo').selectedIndex = -1;
        _('btn_del').style.display = 'none';
        break;
    }
}

/**
 * Add/Update a Quote
 */
function saveQuote()
{
    if (_('title').value.blank() ||
        getEditorValue('quotation').blank() ||
        _('gid').value == 0)
    {
        alert(incompleteQuoteFields);
        return;
    }

    if(_('id').value==0) {
        var response = QuotesAjax.callSync('insertquote',
                                            _('title').value,
                                            getEditorValue('quotation'),
                                            _('gid').value,
                                            _('start_time').value,
                                            _('stop_time').value,
                                            _('show_title').value == 'true',
                                            _('published').value == 'true');
        if (response[0]['css'] == 'notice-message') {
            if (_('group_filter').value == -1 || _('group_filter').value == _('gid').value) {
                var box = _('quotes_combo');
                box.options[box.options.length] = new Option(response[0]['message']['title'], response[0]['message']['id']);
            }
            response[0]['message'] = response[0]['message']['message'];
            stopAction();
        }
        showResponse(response);
    } else {
        var box = _('quotes_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('updatequote',
                                            _('id').value,
                                            _('title').value,
                                            getEditorValue('quotation'),
                                            _('gid').value,
                                            _('start_time').value,
                                            _('stop_time').value,
                                            _('show_title').value == 'true',
                                            _('published').value == 'true');
        if (response[0]['css'] == 'notice-message') {
            box.options[quoteIndex].text = _('title').value;
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
        var box = _('quotes_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('deletequote', box.value);
        if (response[0]['css'] == 'notice-message') {
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
    _('id').value    = quoteInfo['id'];
    _('title').value = quoteInfo['title'].defilter();
    changeEditorValue('quotation', quoteInfo['quotation']);
    _('gid').value = quoteInfo['gid'];
    if (quoteInfo['gid'] == 0) {
        _('gid').selectedIndex= -1;
    }

    _('start_time').value  = (quoteInfo['start_time'] == null)? '': quoteInfo['start_time'];
    _('stop_time').value   = (quoteInfo['stop_time'] == null)? '': quoteInfo['stop_time'];
    _('show_title').value  = quoteInfo['show_title'];
    _('published').value   = quoteInfo['published'];

    _('btn_del').style.display = 'inline';
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (currentAction == 'GroupQuotes') {
        _('work_area').innerHTML = cacheGroupForm;
    }

    currentAction = 'Groups';
    var groupInfo = QuotesAjax.callSync('getgroup', gid);
    _('gid').value         = groupInfo['id'];
    _('title').value       = groupInfo['title'].defilter();
    _('view_mode').value   = groupInfo['view_mode'];
    _('view_type').value   = groupInfo['view_type'];
    _('show_title').value  = groupInfo['show_title'];
    _('limit_count').value = groupInfo['limit_count'];
    _('random').value      = groupInfo['random'];
    _('published').value   = groupInfo['published'];

    _('add_quotes').style.display = 'inline';
    _('btn_del').style.display    = 'inline';
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'Groups') {
        if (_('title').value.blank()) {
            alert(incompleteGroupFields);
            return false;
        }

        if(_('gid').value==0) {
            var response = QuotesAjax.callSync('insertgroup',
                                                _('title').value,
                                                _('view_mode').value,
                                                _('view_type').value,
                                                _('show_title').value == 'true',
                                                _('limit_count').value,
                                                _('random').value == 'true',
                                                _('published').value == 'true');
            if (response[0]['css'] == 'notice-message') {
                var box = _('groups_combo');
                box.options[box.options.length] = new Option(response[0]['message']['title'], response[0]['message']['id']);
                response[0]['message'] = response[0]['message']['message'];
                stopAction();
            }
            showResponse(response);
        } else {
            var box = _('groups_combo');
            var groupIndex = box.selectedIndex;
            var response = QuotesAjax.callSync('updategroup',
                                                _('gid').value,
                                                _('title').value,
                                                _('view_mode').value,
                                                _('view_type').value,
                                                _('show_title').value == 'true',
                                                _('limit_count').value,
                                                _('random').value == 'true',
                                                _('published').value == 'true');
            if (response[0]['css'] == 'notice-message') {
                box.options[groupIndex].text = _('title').value;
                stopAction();
            }
            showResponse(response);
        }
    } else {
        var inputs  = _('work_area').getElementsByTagName('input');
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
        QuotesAjax.callAsync('addquotestogroup', _('gid').value, keys);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(confirmGroupDelete);
    if (answer) {
        var box = _('groups_combo');
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('deletegroup', box.value);
        if (response[0]['css'] == 'notice-message') {
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
    if (_('gid').value == 0) return;
    if (cacheGroupQuotesForm == null) {
        cacheGroupQuotesForm = QuotesAjax.callSync('groupquotesui');
    }

    _('add_quotes').style.display = 'none';
    _('btn_del').style.display    = 'none';
    if (cacheGroupForm == null) {
        cacheGroupForm = _('work_area').innerHTML;
    }
    _('work_area').innerHTML = cacheGroupQuotesForm;

    currentAction = 'GroupQuotes';
    var quotesList = QuotesAjax.callSync('getquotes', -1, _('gid').value);
    var inputs  = _('work_area').getElementsByTagName('input');

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
