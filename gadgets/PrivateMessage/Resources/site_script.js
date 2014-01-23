/**
 * PrivateMessage Javascript actions
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
var PrivateMessageCallback = {
    ComposeMessage: function (response) {
        if (response.type == 'response_notice') {
            if(response.data.published==true) {
                window.location.href = response.data.url;
                return;
            } else {
                $('id').value = response.data.message_id;
                resetAttachments(response.data.message_id);
            }
        }
        PrivateMessageAjax.showResponse(response);
    }
}

/**
 * Filter inbox
 */
function filterInbox()
{
    var result = PrivateMessageAjax.callSync(
        'InboxDataGridUI',
        $(document).getElement('form[name=inbox]').toQueryString().parseQueryString()
    );

    $('inbox-datagrid').innerHTML = result;
    return false;
}


/**
 * Reset attachments after save draft a message
 */
function resetAttachments(message_id) {
    var ui = PrivateMessageAjax.callSync('GetMessageAttachmentUI', {'id': message_id});
    $('attachment_area').set('html', ui);
    uploadedFiles = new Array();
    lastAttachment = 1;
    $('attachment1').show();
    $('attach_loading').hide();
    $('btn_attach1').hide();
}

/**
 * Removes the attachment
 */
function removeAttachment(id) {
    $('frm_file').reset();
    $('btn_attach' + id).hide();
    $('file_link' + id).set('html', '');
    $('file_size' + id).set('html', '');
    $('attachment' + lastAttachment).show();
    uploadedFiles[id] = false;
}

/**
 * Disables/Enables form elements
 */
function toggleDisableForm(disabled)
{
    $('subject').disabled           = disabled;
    $('body').disabled              = disabled;
    $('btn_back').disabled          = disabled;
    $('btn_save_draft').disabled    = disabled;
    $('btn_send').disabled          = disabled;
}


/**
 * Uploads the attachment file
 */
function uploadFile() {
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    $('compose').adopt(iframe);
    $('attachment_number').value = lastAttachment;
    $('attachment' + lastAttachment).hide();
    $('attach_loading').show();
    toggleDisableForm(true);
    $('frm_file').submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    toggleDisableForm(false);
    uploadedFiles[lastAttachment] = response.file_info;
    if (response.type === 'error') {
        alert(response.message);
        $('frm_file').reset();
        $('attachment' + lastAttachment).show();
    } else {
        $('file_link' + lastAttachment).set('html', response.file_info.title);
        $('file_size' + lastAttachment).set('html', response.file_info.filesize_format);
        $('btn_attach' + lastAttachment).show();
        $('attachment' + lastAttachment).dispose();
        addFileEntry();
    }
    $('attach_loading').hide();
    $('ifrm_upload').destroy();
}

/**
 * add a file entry
 */
function addFileEntry() {
    lastAttachment++;
    var id = lastAttachment;

    entry = '<div id="btn_attach' + id + '"> <img src="gadgets/Contact/Resources/images/attachment.png"/> <a id="file_link' + id + '"></a> ' +
        ' <small id="file_size' + id + '"></small> <a onclick="javascript:removeAttachment(' + id + ');" href="javascript:void(0);">' +
        '<img border="0" title="Remove" alt="Remove" src="images/stock/cancel.png"></a></div>';
    entry += ' <input type="file" onchange="uploadFile();" id="attachment' + lastAttachment + '" name="attachment' + lastAttachment + '" size="1" style="display: block;">';

    $('attachment_addentry' + id).innerHTML = entry + '<span id="attachment_addentry' + (id + 1) + '">' + $('attachment_addentry' + id).innerHTML + '</span>';

    $('attach_loading').hide();
    $('btn_attach' + id).hide();
}

/**
 * auto save draft message
 */
function autoDraftMessage() {
    if($('body').value.trim()!=='') {
        sendMessage(false);
    }
}

/**
 * send a message
 */
function sendMessage(published) {

    // detect pre load users or groups list
    if (recipient_user == "" || recipient_user.length == 0) {
        // check Announcement checkbox
        var recipient_users_array = new Array();
        var recipient_groups_array = new Array();
        $$('#recipient_users').getSelected()[0].each(function (i) {
            if (i.get('value').length > 0) {
                recipient_users_array.push(i.get('value'));
            }
        });
        $$('#recipient_groups').getSelected()[0].each(function (i) {
            if (i.get('value') != "") {
                recipient_groups_array.push(i.get('value'));
            }
        });
        var recipient_users = recipient_users_array.join(',');
        var recipient_groups = recipient_groups_array.join(',');
    } else {
        var recipient_users = recipient_user;
        var recipient_groups = "";
    }

    var attachments = uploadedFiles.concat(getSelectedAttachments());
    console.log($('body').value);
    PrivateMessageAjax.callAsync('ComposeMessage', {'id': $('id').value, 'published':published,
                     'recipient_users':recipient_users, 'recipient_groups':recipient_groups,
                     'subject':$('subject').value, 'body':getEditorValue('body'), 'attachments':attachments
    });
}

function getSelectedAttachments() {
    var files = [];
    $$("input[type=checkbox][name=selected_files[]]:checked").each(function(i){
        files.push( i.value );
    });
    return files;
}

var PrivateMessageAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback);

var uploadedFiles = new Array();
var lastAttachment = 1;
