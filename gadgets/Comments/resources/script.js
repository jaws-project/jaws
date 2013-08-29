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
            stopCommentAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    DeleteComments: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopCommentAction();
            getDG('comments_datagrid');
        }
        showResponse(response);
    },

    MarkAs: function(response) {
        if (response[0]['css'] == 'notice-message') {
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
        _('gadgets_filter').value,
        _('filter').value,
        _('status').value
    );
    if (reset) {
        stopCommentAction();
        _(name).setCurrentPage(0);
        var total = CommentsAjax.callSync(
            'SizeOfCommentsSearch',
            _('gadgets_filter').value,
            _('filter').value,
            _('status').value
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
    _('id').value                      = 0;
    _('gadget').value                  = '';
    _('comment_ip').set('html', '');
    _('name').value                    = '';
    _('email').value                   = '';
    _('url').value                     = '';
    _('message').value                 = '';
    _('reply').value                   = '';
    _('comment_status').selectedIndex  = 0;
    _('btn_save').style.display        = 'none';
    _('btn_cancel').style.display      = 'none';
    _('name').disabled                 = false;
    _('email').disabled                = false;
    _('url').disabled                  = false;
    _('message').disabled              = false;
    _('comment_status').disabled       = false;

    unselectGridRow('comments_datagrid');
    _('name').focus();
}

/**
 * Edit a Comment
 *
 */
function editComment(rowElement, id)
{
    selectGridRow('comments_datagrid', rowElement.parentNode.parentNode);
    var comment = CommentsAjax.callSync('getcomment', id);
    _('name').disabled            = false;
    _('email').disabled           = false;
    _('url').disabled             = false;
    _('message').disabled         = false;
    _('comment_status').disabled  = false;
    _('id').value                 = comment['id'];
    _('gadget').value             = comment['gadget'];
    _('comment_ip').set('html', comment['ip']);
    _('name').value               = comment['name'];
    _('email').value              = comment['email'];
    _('url').value                = comment['url'];
    _('message').value            = comment['msg_txt'].defilter();
    _('comment_status').value     = comment['status'];
    _('btn_save').style.display   = 'inline';
    _('btn_cancel').style.display = 'inline';

    if(comment['reply']!=null) {
        _('reply').value          = comment['reply'].defilter();
    }
}

/**
 * Update a Comment
 */
function updateComment() {
    CommentsAjax.callAsync('UpdateComment',
        _('gadget').value,
        _('id').value,
        _('name').value,
        _('email').value,
        _('url').value,
        _('message').value,
        _('reply').value,
        _('comment_status').value);
}

/**
 * Delete comment
 *
 */
function commentDelete(id)
{
    stopCommentAction();
    if (confirm(confirmCommentDelete)) {
        CommentsAjax.callAsync('DeleteComments', id);
    }
    unselectGridRow('comments_datagrid');
}


/**
 * Executes an action on comments
 */
function commentDGAction(combo)
{
    var rows = _('comments_datagrid').getSelectedRows();
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
            CommentsAjax.callAsync('MarkAs', _('gadget').value, rows, combo.value);
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
    CommentsAjax.callAsync('SaveSettings', _('allow_comments').value, _('allow_duplicate').value);
}

var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
    selectedRow = null,
    selectedRowColor = null;
