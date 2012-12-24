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
    UpdateComment: function(response) {
        if (response[0]['css'] == 'notice-message') {
            limit = $('comments_datagrid').getCurrentPage();
            getData(limit);
            stopAction();
        }
        showResponse(response);
    },

    DeleteComments: function(response) {
        if (response[0]['css'] == 'notice-message') {
            limit = $('comments_datagrid').getCurrentPage();
            getData(limit);
            stopAction();
        }
        showResponse(response);
    },

    MarkAs: function(response) {
        if (response[0]['css'] == 'notice-message') {
            limit = $('comments_datagrid').getCurrentPage();
            getData(limit);
            stopAction();
        }
        showResponse(response);
    }
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
        var size = CommentsAjax.callSync('SizeOfCommentsSearch', $('gadgets_filter').value, '', '', '');
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
    $('gadget').value  = '';
    $('comment_ip').set('html', '');
    $('name').value    = '';
    $('email').value   = '';
    $('url').value     = '';
    $('subject').value = '';
    $('message').value = '';
    $('status').value  = '';
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
    $('gadget').value  = comment['gadget'];
    $('comment_ip').set('html', comment['ip']);
    $('name').value    = comment['name'];
    $('email').value   = comment['email'];
    $('url').value     = comment['url'];
    $('subject').value = comment['title'].defilter();
    $('message').value = comment['msg_txt'].defilter();
    $('status').value  = comment['status'];
    $('btn_save').style.visibility   = 'visible';
    $('btn_cancel').style.visibility = 'visible';
}

/**
 * Update a Comment
 */
function updateComment()
{
    CommentsAjax.callAsync('UpdateComment',
                    $('gadget').value,
                    $('id').value,
                    $('name').value,
                    $('email').value,
                    $('url').value,
                    $('subject').value,
                    $('message').value,
                    $('status').value);
}

/**
 * Delete comment
 *
 */
function commentDelete(id)
{
    stopAction();
    if (confirm(confirmCommentDelete)) {
        CommentsAjax.callAsync('DeleteComments', id);
    }
    unselectDataGridRow();
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
            var confirmation = confirm(confirmCommentDelete);
            if (confirmation) {
                CommentsAjax.callAsync('DeleteComments', rows);
            }
        }
    } else if (combo.value != '') {
        if (selectedRows) {
            CommentsAjax.callAsync('MarkAs', $('gadget').value, rows, combo.value);
        }
    }
}

/**
 * search for a comment
 */
function searchComment()
{
    updateCommentsDatagrid(0, '', '', '', true);
    return false;
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
