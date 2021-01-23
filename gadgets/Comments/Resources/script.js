/**
 * Comments Javascript actions
 *
 * @category    Ajax
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var CommentsCallback = {
    UpdateComment: function(response) {
        if (response['type'] == 'alert-success') {
            stopCommentAction();
            getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
        }
    },

    DeleteComments: function(response) {
        if (response['type'] == 'alert-success') {
            stopCommentAction();
            getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
        }
    },

    MarkAs: function(response) {
        if (response['type'] == 'alert-success') {
            stopCommentAction();
            getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
        }
    }

}

/**
 * Fetches comments data to fills the data grid
 */
function getCommentsDataGrid(name, offset, reset)
{
    var comments = CommentsAjax.callSync(
        'SearchComments', [
            CommentsAjax.mainRequest.gadget,
            $('#gadgets_filter').val(),
            $('#filter').val(),
            $('#status').val(),
            offset,
            2
        ]
    );
    if (reset) {
        stopCommentAction();
        $('#' + name)[0].setCurrentPage(0);
        var total = CommentsAjax.callSync(
            'SizeOfCommentsSearch', [
                $('#gadgets_filter').val(),
                $('#filter').val(),
                $('#status').val()
            ]
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
    $('#id').val(0);
    $('#gadget').val('');
    $('#comment_ip').html('');
    $('#insert_time').html('');
    $('#reference_link').html('');
    $('#name').val('');
    $('#email').val('');
    $('#url').val('');
    $('#message').val('');
    $('#reply').val('');
    $('#comment_status').prop('selectedIndex', 0);
    $('#btn_save').css('display', 'none');
    $('#btn_reply').css('display', 'none');
    $('#btn_cancel').css('display', 'none');
    $("#name").prop('disabled', false);
    $("#email").prop('disabled', false);
    $("#url").prop('disabled', false);
    $("#message").prop('disabled', false);
    $("#comment_status").prop('disabled', false);

    unselectGridRow('comments_datagrid');
    $('#name').focus();
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
    $("#name").prop('disabled', false);
    $("#email").prop('disabled', false);
    $("#url").prop('disabled', false);
    $("#message").prop('disabled', false);
    $("#comment_status").prop('disabled', false);
    $('#id').val(comment['id']);
    $('#gadget').val(comment['gadget']);
    $('#comment_ip').html(comment['ip']);
    $('#insert_time').html(comment['insert_time']);
    $('#name').val(comment['name']);
    $('#email').val(comment['email']);
    $('#url').val(comment['url']);
    $('#message').val(comment['msg_txt'].defilter());
    $('#comment_status').val(comment['status']);
    if (comment['reference_link'] != '') {
        $('#reference_link').html(
            '<a href="'
            + comment['reference_link']
            + '">'
            + comment['reference_title']
            + '</a>'
        );
    }
    $('#btn_save').css('display', 'inline');
    $('#btn_reply').css('display', 'inline');
    $('#btn_cancel').css('display', 'inline');

    if(comment['reply']!=null) {
        $('#reply').val(comment['reply'].defilter());
    }
}

/**
 * Update a Comment
 */
function updateComment(sendEmail) {
    CommentsAjax.callAsync(
        'UpdateComment', [
            $('#gadget').val(),
            $('#id').val(),
            $('#name').val(),
            $('#email').val(),
            $('#url').val(),
            $('#message').val(),
            $('#reply').val(),
            $('#comment_status').val(),
            sendEmail
        ]
    );
}

/**
 * Delete comment
 *
 */
function commentDelete(id)
{
    stopCommentAction();
    if (confirm(jaws.Comments.Defines.confirmCommentDelete)) {
        CommentsAjax.callAsync('DeleteComments', new Array(id));
    }
    unselectGridRow('comments_datagrid');
}


/**
 * Executes an action on comments
 */
function commentDGAction(combo)
{
    var rows = $('#comments_datagrid')[0].getSelectedRows();
    if (rows.length < 1) {
        return;
    }

    if (combo.val() == 'delete') {
        var confirmation = confirm(jaws.Comments.Defines.confirmCommentDelete);
        if (confirmation) {
            CommentsAjax.callAsync('DeleteComments', rows);
        }
    } else if (combo.val() != '') {
        CommentsAjax.callAsync('MarkAs', {
            'ids': rows,
            'status': combo.val()
        });
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
    CommentsAjax.callAsync(
        'SaveSettings', [
            $('#allow_comments').val(),
            $('#default_comment_status').val(),
            $('#order_type').val()
        ]
    );
}

$(document).ready(function() {
    if (jaws.Defines.mainGadget !== 'Comments' || jaws.Defines.mainAction === 'Comments') {
        $('#gadgets_filter').selectedIndex = 0;
        initDataGrid('comments_datagrid', CommentsAjax, getCommentsDataGrid);
    }
});

var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
    selectedRow = null,
    selectedRowColor = null;
