/**
 * Quotes Javascript actions
 *
 * @category   Ajax
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var QuotesCallback = {
    AddQuotesToGroup: function(response) {
        QuotesAjax.showResponse(response);
    }
};

/**
 * Fills the quotes combo
 */
function fillQuotesCombo()
{
    var box = $('#quotes_combo')[0];
    box.options.length = 0;
    var quotes = QuotesAjax.callSync('GetQuotes', [-1, $('#group_filter').val()]);
    if (quotes.length > 0) {
        $.each(quotes, function(index, value) {
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
        $('#gid').val(0);
        $('#title').val('');
        $('#view_mode').val('0');
        $('#view_type').val('0');
        $('#show_title').val('true');
        $('#limit_count').val('0');
        $('#random').val('false');
        $('#published').val('true');
        $('#groups_combo').prop('selectedIndex', -1);

        $('#add_quotes').css('display', 'none');
        $('#btn_del').css('display', 'none');
        break;
    case 'GroupQuotes':
        editGroup($('#gid').val());
        break;
    case 'Quotes':
        $('#id').val(0);
        $('#title').val('');
        $('#show_title').val('true');
        $('#published').val('true');
        $('#gid').prop('selectedIndex', $('#group_filter').prop('selectedIndex') - 1);
        $('#start_time').val('');
        $('#stop_time').val('');
        setEditorValue('#quotation', '');
        $('#quotes_combo').prop('selectedIndex', -1);
        $('#btn_del').css('display', 'none');
        initDatePicker('start_time');
        initDatePicker('stop_time');
        break;
    }
}

/**
 * Add/Update a Quote
 */
function saveQuote()
{
    if (!$('#title').val() ||
        getEditorValue('#quotation').blank() ||
        $('#gid').val() == 0)
    {
        alert(jaws.gadgets.Quotes.incompleteQuoteFields);
        return;
    }

    if($('#id').val() == 0) {
        var response = QuotesAjax.callSync(
            'InsertQuote', [
                $('#title').val(),
                getEditorValue('#quotation'),
                $('#gid').val(),
                $('#start_time').val(),
                $('#stop_time').val(),
                $('#show_title').val() == 'true',
                $('#published').val() == 'true'
            ]
        );
        if (response[0]['type'] == 'alert-success') {
            if ($('#group_filter').val() == -1 || $('#group_filter').val() == $('#gid').val()) {
                var box = $('#quotes_combo')[0];
                box.options[box.options.length] = new Option(response[0]['data']['title'], response[0]['data']['id']);
            }
            stopAction();
        }
        QuotesAjax.showResponse(response);
    } else {
        var box = $('#quotes_combo')[0];
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync(
            'UpdateQuote', [
                $('#id').val(),
                $('#title').val(),
                getEditorValue('#quotation'),
                $('#gid').val(),
                $('#start_time').val(),
                $('#stop_time').val(),
                $('#show_title').val() == 'true',
                $('#published').val() == 'true'
            ]
        );
        if (response[0]['type'] == 'alert-success') {
            box.options[quoteIndex].text = $('#title').val();
            stopAction();
        }
        QuotesAjax.showResponse(response);
    }
}

/**
 * Delete a Quote
 */
function deleteQuote()
{
    var answer = confirm(jaws.gadgets.Quotes.confirmQuoteDelete);
    if (answer) {
        var box = $('#quotes_combo')[0];
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('DeleteQuote', box.value);
        if (response[0]['type'] == 'alert-success') {
            box.options[quoteIndex] = null;
            stopAction();
        }
        QuotesAjax.showResponse(response);
    }
}

/**
 * Edit a Quote
 *
 */
function editQuote(id)
{
    if (id == 0) return;
    var quoteInfo = QuotesAjax.callSync('GetQuote', id);
    currentAction = 'Quotes';
    $('#id').val(quoteInfo['id']);
    $('#title').val(quoteInfo['title'].defilter());
    setEditorValue('#quotation', quoteInfo['quotation']);
    $('#gid').val(quoteInfo['gid']);
    if (quoteInfo['gid'] == 0) {
        $('#gid').prop('selectedIndex', -1);
    }

    $('#start_time').val((quoteInfo['start_time'] == null)? '': quoteInfo['start_time']);
    $('#stop_time').val((quoteInfo['stop_time'] == null)? '': quoteInfo['stop_time']);
    $('#show_title')[0].value = quoteInfo['show_title'];
    $('#published')[0].value = quoteInfo['published'];

    $('#btn_del').css('display', 'inline');
}

/**
 * Edit group
 */
function editGroup(gid)
{
    if (gid == 0) return;
    if (currentAction == 'GroupQuotes') {
        $('#work_area').html(cacheGroupForm);
    }

    currentAction = 'Groups';
    var groupInfo = QuotesAjax.callSync('GetGroup', gid);
    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('#view_mode').val(groupInfo['view_mode']);
    $('#view_type').val(groupInfo['view_type']);
    $('#limit_count').val(groupInfo['limit_count']);
    $('#show_title')[0].value = groupInfo['show_title'];
    $('#random')[0].value = groupInfo['random'];
    $('#published')[0].value = groupInfo['published'];

    $('#add_quotes').css('display', 'inline');
    $('#btn_del').css('display', 'inline');
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    if (currentAction == 'Groups') {
        if (!$('#title').val()) {
            alert(jaws.gadgets.Quotes.incompleteGroupFields);
            return false;
        }

        if($('#gid').val() == 0) {
            var response = QuotesAjax.callSync(
                'InsertGroup', [
                    $('#title').val(),
                    $('#view_mode').val(),
                    $('#view_type').val(),
                    $('#show_title').val() == 'true',
                    $('#limit_count').val(),
                    $('#random').val() == 'true',
                    $('#published').val() == 'true'
                ]
            );
            if (response[0]['type'] == 'alert-success') {
                var box = $('#groups_combo')[0];
                box.options[box.options.length] = new Option(response[0]['data']['title'], response[0]['data']['id']);
                stopAction();
            }
            QuotesAjax.showResponse(response);
        } else {
            var box = $('#groups_combo')[0],
                groupIndex = box.selectedIndex;
            var response = QuotesAjax.callSync(
                'UpdateGroup', [
                    $('#gid').val(),
                    $('#title').val(),
                    $('#view_mode').val(),
                    $('#view_type').val(),
                    $('#show_title').val() == 'true',
                    $('#limit_count').val(),
                    $('#random').val() == 'true',
                    $('#published').val() == 'true'
                ]
            );
            if (response[0]['type'] == 'alert-success') {
                box.options[groupIndex].text = $('#title').val();
                stopAction();
            }
            QuotesAjax.showResponse(response);
        }
    } else {
        var inputs  = $('#work_area').find('input');
        var keys    = [];
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
        QuotesAjax.callAsync('AddQuotesToGroup', [$('#gid').val(), keys]);
    }
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(jaws.gadgets.Quotes.confirmGroupDelete);
    if (answer) {
        var box = $('#groups_combo')[0];
        var quoteIndex = box.selectedIndex;
        var response = QuotesAjax.callSync('DeleteGroup', box.value);
        if (response[0]['type'] == 'alert-success') {
            box.options[quoteIndex] = null;
            stopAction();
        }
        QuotesAjax.showResponse(response);
    }
}

/**
 * Show a simple-form with checkboxes so quotes can check their group
 */
function editGroupQuotes()
{
    if ($('#gid').val() == 0) return;
    if (cacheGroupQuotesForm == null) {
        cacheGroupQuotesForm = QuotesAjax.callSync('GroupQuotesUI');
    }

    $('#add_quotes').css('display', 'none');
    $('#btn_del').css('display', 'none');
    if (cacheGroupForm == null) {
        cacheGroupForm = $('#work_area').html();
    }
    $('#work_area').html(cacheGroupQuotesForm);

    currentAction = 'GroupQuotes';
    var quotesList = QuotesAjax.callSync('GetQuotes', [-1, $('#gid').val()]);
    var inputs  = $('#work_area').find('input');

    if (quotesList) {
        $.each(quotesList, function(index, value) {
            for (var i=0; i<inputs.length; i++) {
                if (inputs[i].name.indexOf('group_quotes') == -1) {
                    continue;
                }
                if (value['id'] == inputs[i].value) {
                    inputs[i].checked = true;
                    break
                }
            }
        });
    }
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Quotes':
            currentAction = 'Quotes';
            $('#group_filter').prop('selectedIndex', 0);
            stopAction();
            break;
        case 'QuoteGroups':
            currentAction = 'Groups';
            stopAction();
            break;
    }
});

var QuotesAjax = new JawsAjax('Quotes', QuotesCallback);

//Cache for saving the group-form template
var cacheGroupForm = null;

//Cache for saving the group quotes form template
var cacheGroupQuotesForm = null;

//Which action are we running?
var currentAction = null;
