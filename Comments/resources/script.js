/**
 * Comments Javascript actions
 *
 * @category    Ajax
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var CommentsCallback = {
    UpdateComment: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    ReplyComment: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    DeleteComments: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    MarkAs: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    SaveProperties: function(response) {
        showResponse(response);
    }
}

/**
 * Fetches comments data to fills the data grid
 */
function getCommentsDataGrid(name, offset, reset)
{
    var comments = CommentsAjax.callSync(
        'SearchComments',
        offset,
        $('gadgets_filter').value,
        $('filterby').value,
        $('filter').value,
        $('status').value
    );
    if (reset) {
        stopAction();
        $(name).setCurrentPage(0);
        var total = CommentsAjax.callSync(
            'SizeOfCommentsSearch',
            $('gadgets_filter').value,
            $('filterby').value,
            $('filter').value,
            $('status').value
        );
    }

    resetGrid(name, comments, total);
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
    $('id').value                      = 0;
    $('gadget').value                  = '';
    $('comment_ip').set('html', '');
    $('name').value                    = '';
    $('email').value                   = '';
    $('url').value                     = '';
    $('message').value                 = '';
    $('reply').value                   = '';
    $('comment_status').selectedIndex  = 0;
    $('reply_area').style.display      = 'none';
    $('btn_save').style.display        = 'none';
    $('btn_reply').style.display       = 'none';
    $('btn_cancel').style.display      = 'none';
    $('name').disabled                 = false;
    $('email').disabled                = false;
    $('url').disabled                  = false;
    $('message').disabled              = false;
    $('comment_status').disabled       = false;

    unselectDataGridRow();
    $('name').focus();
}

/**
 * Edit a Comment
 *
 */
function editComment(element, id)
{
    selectDataGridRow(element.parentNode.parentNode);
    var comment = CommentsAjax.callSync('getcomment', id);
    $('name').disabled            = false;
    $('email').disabled           = false;
    $('url').disabled             = false;
    $('message').disabled         = false;
    $('comment_status').disabled  = false;
    $('id').value                 = comment['id'];
    $('gadget').value             = comment['gadget'];
    $('comment_ip').set('html', comment['ip']);
    $('name').value               = comment['name'];
    $('email').value              = comment['email'];
    $('url').value                = comment['url'];
    $('message').value            = comment['msg_txt'].defilter();
    $('comment_status').value     = comment['status'];
    $('reply_area').style.display = 'none';
    $('btn_reply').style.display  = 'none';
    $('btn_save').style.display   = 'inline';
    $('btn_cancel').style.display = 'inline';
}

/**
 * Reply a Comment
 *
 */
function replyComment(element, id)
{
    selectDataGridRow(element.parentNode.parentNode);
    var comment = CommentsAjax.callSync('getcomment', id);

    $('id').value               = comment['id'];
    $('gadget').value           = comment['gadget'];
    $('comment_ip').set('html', comment['ip']);
    $('name').value             = comment['name'];
    $('email').value            = comment['email'];
    $('url').value              = comment['url'];
    $('message').value          = comment['msg_txt'].defilter();
    $('comment_status').value   = comment['status'];
    $('reply').value            = comment['reply'];

    $('name').disabled           = true;
    $('email').disabled          = true;
    $('url').disabled            = true;
    $('message').disabled        = true;
    $('comment_status').disabled = true;

    $('btn_save').style.display     = 'none';
    $('btn_reply').style.display    = 'inline';
    $('btn_cancel').style.display   = 'inline';
    $('reply_area').style.display   = 'table-row';
}

/**
 * Update a Comment
 */
function updateComment(action)
{
    if(action=='update') {
        CommentsAjax.callAsync('UpdateComment',
                        $('gadget').value,
                        $('id').value,
                        $('name').value,
                        $('email').value,
                        $('url').value,
                        $('message').value,
                        $('comment_status').value);
    } else if(action=='reply') {
        CommentsAjax.callAsync('ReplyComment',
                         $('gadget').value,
                         $('id').value,
                         $('reply').value);
    }
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
    getCommentsDataGrid('comments_datagrid', 0, true);
}

/**
 * save properties
 */
function saveProperties()
{
    CommentsAjax.callAsync('SaveProperties', $('allow_duplicate').value);
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
