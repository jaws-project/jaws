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
        if (response[0]['type'] == 'response_notice') {
            stopCommentAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    DeleteComments: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopCommentAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    MarkAs: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopCommentAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    SaveSettings: function(response) {
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
        $('filter').value,
        $('status').value
    );
    if (reset) {
        stopCommentAction();
        $(name).setCurrentPage(0);
        var total = CommentsAjax.callSync(
            'SizeOfCommentsSearch',
            $('gadgets_filter').value,
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
 * Clean the form
 *
 */
function stopCommentAction()
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
    $('btn_save').style.display        = 'none';
    $('btn_reply').style.display       = 'none';
    $('btn_cancel').style.display      = 'none';
    $('name').disabled                 = false;
    $('email').disabled                = false;
    $('url').disabled                  = false;
    $('message').disabled              = false;
    $('comment_status').disabled       = false;

    unselectGridRow('comments_datagrid');
    $('name').focus();
}

/**
 * Edit a Comment
 *
 */
function editComment(rowElement, id)
{
    stopCommentAction();
    selectGridRow('comments_datagrid', rowElement.parentNode.parentNode);
    var comment = CommentsAjax.callSync('GetComment', id);
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
    $('btn_save').style.display   = 'inline';
    $('btn_reply').style.display  = 'inline';
    $('btn_cancel').style.display = 'inline';

    if(comment['reply']!=null) {
        $('reply').value          = comment['reply'].defilter();
    }
}

/**
 * Update a Comment
 */
function updateComment(sendEmail) {
    CommentsAjax.callAsync('UpdateComment',
        $('gadget').value,
        $('id').value,
        $('name').value,
        $('email').value,
        $('url').value,
        $('message').value,
        $('reply').value,
        $('comment_status').value,
        sendEmail);
}

/**
 * Delete comment
 *
 */
function commentDelete(id)
{
    stopCommentAction();
    if (confirm(confirmCommentDelete)) {
        CommentsAjax.callAsync('DeleteComments', new Array(id));
    }
    unselectGridRow('comments_datagrid');
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
function SaveSettings()
{
    CommentsAjax.callAsync('SaveSettings', $('allow_comments').value, $('allow_duplicate').value);
}

var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
    selectedRow = null,
    selectedRowColor = null;
