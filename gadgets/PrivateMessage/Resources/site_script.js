/**
 * PrivateMessage Javascript actions
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
var PrivateMessageCallback = {
    SendMessage: function (response) {
        if (response.type == 'response_notice') {
            if (response.data && response.data.is_draft) {
                document.id('id').value = response.data.message_id;
                resetAttachments(response.data.message_id);
            } else {
                setTimeout(function() {window.location.href = response.data.url;}, 1000);
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
        $.unserialize($('form[name=inbox]').serialize())
    );

    document.id('inbox-datagrid').innerHTML = result;
    return false;
}


/**
 * Reset attachments after save draft a message
 */
function resetAttachments(message_id) {
    var ui = PrivateMessageAjax.callSync('GetMessageAttachmentUI', {'id': message_id});
    document.id('attachment_area').set('html', ui);
    uploadedFiles = new Array();
    lastAttachment = 1;
    document.id('attachment1').show();
    document.id('attach_loading').hide();
    document.id('btn_attach1').hide();
}

/**
 * Removes the attachment
 */
function removeAttachment(id) {
    document.id('frm_file').reset();
    document.id('btn_attach' + id).hide();
    document.id('file_link' + id).set('html', '');
    document.id('file_size' + id).set('html', '');
    document.id('attachment' + lastAttachment).show();
    uploadedFiles[id] = false;
}

/**
 * Disables/Enables form elements
 */
function toggleDisableForm(disabled)
{
    document.id('subject').disabled           = disabled;
    document.id('body').disabled              = disabled;
    document.id('btn_back').disabled          = disabled;
    document.id('btn_save_draft').disabled    = disabled;
    document.id('btn_send').disabled          = disabled;
}


/**
 * Uploads the attachment file
 */
function uploadFile() {
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    document.id('compose').adopt(iframe);
    document.id('attachment_number').value = lastAttachment;
    document.id('attachment' + lastAttachment).hide();
    document.id('attach_loading').show();
    toggleDisableForm(true);
    document.id('frm_file').submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    toggleDisableForm(false);
    uploadedFiles[lastAttachment] = response.file_info;
    if (response.type === 'error') {
        alert(response.message);
        document.id('frm_file').reset();
        document.id('attachment' + lastAttachment).show();
    } else {
        document.id('file_link' + lastAttachment).set('html', response.file_info.title);
        document.id('file_size' + lastAttachment).set('html', response.file_info.filesize_format);
        document.id('btn_attach' + lastAttachment).show();
        document.id('attachment' + lastAttachment).dispose();
        addFileEntry();
    }
    document.id('attach_loading').hide();
    document.id('ifrm_upload').destroy();
}

/**
 * add a file entry
 */
function addFileEntry() {
    lastAttachment++;
    var id = lastAttachment;

    entry = '<div id="btn_attach' + id + '"> <img src="gadgets/PrivateMessage/Resources/images/attachment.png"/> <a id="file_link' + id + '"></a> ' +
        ' <small id="file_size' + id + '"></small> <a onclick="javascript:removeAttachment(' + id + ');" href="javascript:void(0);">' +
        '<img border="0" title="Remove" alt="Remove" src="images/stock/cancel.png"></a></div>';
    entry += ' <input type="file" onchange="uploadFile();" id="attachment' + lastAttachment + '" name="attachment' + lastAttachment + '" size="1" style="display: block;">';

    document.id('attachment_addentry' + id).innerHTML = entry + '<span id="attachment_addentry' + (id + 1) + '">' + document.id('attachment_addentry' + id).innerHTML + '</span>';

    document.id('attach_loading').hide();
    document.id('btn_attach' + id).hide();
}

/**
 * send a message
 */
function sendMessage(isDraft) {

    // detect pre load users or groups list
    if (recipient_user == "" || recipient_user.length == 0) {
        var recipient_users_array = new Array();
        var recipient_groups_array = new Array();
        $('#recipient_users option').each(function (i) {
            if (i.get('value').length > 0) {
                recipient_users_array.push(i.get('value'));
            }
        });
        $('#recipient_groups').getSelected()[0].each(function (i) {
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
    PrivateMessageAjax.callAsync(
        'SendMessage', {
            'id': document.id('id').value,
            'is_draft':isDraft,
            'recipient_users':recipient_users,
            'recipient_groups':recipient_groups,
            'subject':document.id('subject').value,
            'body':getEditorValue('body'),
            'attachments':attachments
        }
    );
}

function getSelectedAttachments() {
    var files = [];
    $("input[type=checkbox][name=selected_files[]]:checked").each(function(i){
        files.push( i.value );
    });
    return files;
}

/**
 * Search users step 1
 */
function searchUsersStart(term) {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout("searchUsers('" + term + "');", 1000);
}

/**
 * Search users step 2
 */
function searchUsers(term) {
    searchTimeout = null;
    var users = PrivateMessageAjax.callSync('GetUsers', {'term': term});
    if (users.length < 1) {
        clearUsersSearch();
        return;
    }
    document.id('userSearchResult').show();
    document.id('userSearchResult').innerHTML = '<a class="delete" href="javascript:clearUsersSearch();"></a>';
    for (var i = 0; i < users.length; i++) {
        new Element('div#searchResult' + users[i]['id'], {
            'html': users[i]['nickname'] + '(' +users[i]['username'] + ')',
            'data-user-id': users[i]['id'],
            'onClick': 'addUserToList(' + users[i]['id'] + ',\'' + users[i]['nickname'] +'\')'
        }).inject('userSearchResult');
    }
}

/**
 * Clear users search result
 */
function clearUsersSearch() {
    document.id('userSearchResult').innerHTML = '<a class="delete" href="javascript:clearUsersSearch();"></a>';
    document.id('userSearchResult').hide();
}

/**
 * Add a user to recipient List
 */
function addUserToList(userId, title) {
    if ($('#recipient_users option[value=' + userId + ']').length > 0) {
        return;
    }
    var box = document.id('recipient_users');
    box.options[box.options.length] = new Option(title, userId);
}

/**
 * Remove selected user from recipient list
 */
function removeUserFromList() {
    document.id('recipient_users').options[document.id('recipient_users').selectedIndex] = null;
}

var PrivateMessageAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback);

var uploadedFiles = new Array();
var lastAttachment = 1;
var searchTimeout = null;