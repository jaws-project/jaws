/**
 * Chatbox Javascript actions
 *
 * @category   Ajax
 * @package    Chatbox
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ChatboxCallback = {

    updateproperties: function(response) {
        showResponse(response);
    },

    deletecomment: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            $('comments_datagrid').deleteItem();
            var limit = $('comments_datagrid').getCurrentPage();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        }
    },

    deletecomments: function(response) {
        if (response[0]['css'] == 'notice-message') {
            var rows = $('comments_datagrid').getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('comments_datagrid').deleteItem();
                }
            }
            PiwiGrid.multiSelect($('comments_datagrid'));
            var limit = $('comments_datagrid').getCurrentPage();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('comments_datagrid'));
        }
        showResponse(response);
    },

    markas: function(response) {
        if (response[0]['css'] == 'notice-message') {
            PiwiGrid.multiSelect($('comments_datagrid'));
            resetLCForm();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(0,
                                   formData['filter'],
                                   formData['search'],
                                   formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('comments_datagrid'));
        }
        showResponse(response);
    }
}

/**
 * Reset ListComments form
 */
function resetLCForm()
{
    var form = document.forms['ListComments'];
    form.elements['filterby'].value = '';
    form.elements['filter'].value   = '';
    form.elements['status'].value   = 'approved';
}

/**
 * Get data of the form ListComments form
 */
function getDataOfLCForm()
{
    var form = document.forms['ListComments'];

    var data = new Array();

    data['filter']   = form.elements['filterby'].value;
    data['search']   = form.elements['filter'].value;
    data['status']   = form.elements['status'].value;

    return data;
}

/**
 * Delete a comment
 */
function deleteComment(id)
{
    ChatboxAjax.callAsync('deletecomment', id);
}

/**
 * search for a comment
 */
function searchComment()
{
    var formData = getDataOfLCForm();
    updateCommentsDatagrid(0, formData['filter'], formData['search'], formData['status'], true);
    return false;
}

/**
 * Get posts data
 */
function getData(limit)
{
    if (limit == undefined) {
        limit = $('comments_datagrid').getCurrentPage();
    }

    var formData = getDataOfLCForm();
    updateCommentsDatagrid(limit, formData['filter'],
                           formData['search'], formData['status'],
                           false);
}

/**
 * Get previous values of comments
 */
function previousValues()
{
    var previousValues = $('comments_datagrid').getPreviousPagerValues();
    getData(previousValues);
    $('comments_datagrid').previousPage();
}

/**
 * Get next values of comments
 */
function nextValues()
{
    var nextValues = $('comments_datagrid').getNextPagerValues();
    getData(nextValues);
    $('comments_datagrid').nextPage();
}

/**
 * Update comments datagrid
 */
function updateCommentsDatagrid(limit, filter, search, status, resetCounter)
{
    result = ChatboxAjax.callSync('searchcomments', limit, filter, search, status);
    resetGrid('comments_datagrid', result);
    if (resetCounter) {
        var size = ChatboxAjax.callSync('sizeofcommentssearch', filter, search, status);
        $('comments_datagrid').rowsSize    = size;
        $('comments_datagrid').setCurrentPage(0);
        $('comments_datagrid').updatePageCounter();
    }
}

/**
 * Delete comment
 */
function commentDelete(row_id)
{
    var confirmation = confirm(deleteConfirm);
    if (confirmation) {
        ChatboxAjax.callAsync('deletecomments', row_id);
    }
}

/**
 * Executes an action on comments
 */
function commentDGAction(combo)
{
    var rows = $('comments_datagrid').getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

    if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                ChatboxAjax.callAsync('deletecomments', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            ChatboxAjax.callAsync('markas', rows, combo.value);
        }
    }
}

/**
 * Update the properties
 *
 */
function updateProperties(form)
{
    var limitEntries = form.elements['limit_entries'].value;
    var max_strlen   = form.elements['max_strlen'].value;
    var authority    = form.elements['authority'].value;
    ChatboxAjax.callAsync('updateproperties', limitEntries, max_strlen, authority);
}

var ChatboxAjax = new JawsAjax('Chatbox', ChatboxCallback);

var firstFetch = true;
var currentIndex = 0;
