/**
 * Comments Javascript actions
 *
 * @category   Ajax
 * @package    Comments
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var CommentsCallback = {
 
}

/**
 * Get data
 */
function getData(limit)
{
    if (limit == undefined) {
        limit = $('comments_datagrid').getCurrentPage();
    }
    //var formData = getDataOfLCForm();
    updateCommentsDatagrid(limit, '',
                           '', '',
                           false);
}

/**
 * Update comments datagrid
 */
function updateCommentsDatagrid(limit, filter, search, status, resetCounter)
{
    result = CommentsAjax.callSync('SearchComments', limit, $('gadgets_filter').value, '', '', '');
    resetGrid('comments_datagrid', result);
    if (resetCounter) {
        var size = BlogAjax.callSync('sizeofcommentssearch', '', '', '');
        $('comments_datagrid').rowsSize    = size;
        $('comments_datagrid').setCurrentPage(0);
        $('comments_datagrid').updatePageCounter();
    }
}

function isValidEmail(email) {
    return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email));
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Clean the form
 *
 */
function stopAction() 
{
    $('id').value      = 0;
    $('cmments_ip').set('html', '');
    $('name').value    = '';
    $('email').value   = '';
    $('url').value     = '';
    $('subject').value = '';
    $('message').value = '';
    $('btn_save').style.visibility   = 'hidden';
    $('btn_cancel').style.visibility = 'hidden';
    unselectDataGridRow();
    $('name').focus();
}

/**
 * Edit a Comment
 *
 */
function commentEdit(element, id)
{
    selectDataGridRow(element.parentNode.parentNode);

    var comment = CommentsAjax.callSync('getcomment', $('gadgets_filter').value, id);
    $('id').value      = comment['id'];
    $('comment_ip').set('html', comment['ip']);
    $('name').value    = comment['name'];
    $('email').value   = comment['email'];
    $('url').value     = comment['url'];
    $('subject').value = comment['title'].defilter();
    $('message').value = comment['msg_txt'].defilter();
    $('btn_save').style.visibility   = 'visible';
    $('btn_cancel').style.visibility = 'visible';
}

/**
 * Delete contact
 *
 */
function deleteContact(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    if (confirm(confirmContactDelete)) {
        ContactAjax.callAsync('deletecontact', id);
    }
    unselectDataGridRow();
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
    selectedRow = null,
    selectedRowColor = null;
