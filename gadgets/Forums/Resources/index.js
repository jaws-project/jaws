/**
 * Forums JS actions
 *
 * @category    Ajax
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ForumsCallback = {
};

/**
 * stop Action
 */
function stopAction()
{
    $('#postUIArea').hide();
    $('#update_reason_container').hide();
    $('#captcha_container').hide();
    setEditorValue('#message', '');
    $('#post_form #notification').prop('checked', 'checked');
    $('#post_form #pid').val(0);
}

/**
 * add a file entry
 */
function extraAttachment()
{
    $('#attachment_model').clone(true).prependTo("#btn_add_attachment").show();
}

/**
 * Remove a file entry
 */
function removeAttachment(element)
{
    $(element).parent().remove();
}

/**
 * display new post UI
 */
function newPost()
{
    stopAction();
    $('#captcha_container').show();
    $('#postUIArea').show();
    $('html, body').animate({
        scrollTop: $("#postUIArea").offset().top
    }, 1000);
}

/**
 * display edit post UI
 */
function editPost(pid)
{
    $('#postUIArea').show();
    $('#update_reason_container').show();
    $('#post_form #pid').val(pid);
    $('html, body').animate({
        scrollTop: $("#postUIArea").offset().top
    }, 1000);

    var postInfo = ForumsAjax.callSync('GetPost', {pid: pid});
    setEditorValue('#message', postInfo['message']);
    $('#post_form #update_reason').val(postInfo['update_reason']);
}

/**
 * display reply post UI
 */
function replyPost(pid)
{
    stopAction();
    $('#captcha_container').show();
    $('#postUIArea').show();
    $('html, body').animate({
        scrollTop: $("#postUIArea").offset().top
    }, 1000);

    var postInfo = ForumsAjax.callSync('GetPost', {pid: pid});
    setEditorValue('#message', '[quote=' + postInfo['nickname'] + "]\n"+ postInfo['message'] + "\n[/quote]\n");
}

/**
 * on document ready
 */
$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'NewPost':
            break;
    }
});

var ForumsAjax = new JawsAjax('Forums', ForumsCallback, 'index.php');