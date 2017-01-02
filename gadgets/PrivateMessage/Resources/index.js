/**
 * PrivateMessage Javascript actions
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
var PrivateMessageCallback = {
    SendMessage: function (response) {
        if (response.type == 'alert-success') {
            if (response.data && response.data.is_draft) {
                $('#id').val(response.data.message_id);
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

    $('#inbox-datagrid').html(result);
    return false;
}


/**
 * Reset attachments after save draft a message
 */
function resetAttachments(message_id) {
    var ui = PrivateMessageAjax.callSync('GetMessageAttachmentUI', {'id': message_id});
    $('#attachment_area').html(ui);
    uploadedFiles = new Array();
    lastAttachment = 1;
    $('#attachment1').show();
    $('#attach_loading').hide();
    $('#btn_attach1').hide();
}

/**
 * Removes the attachment
 */
function removeAttachment(id) {
    $('#frm_file').reset();
    $('#btn_attach' + id).hide();
    $('#file_link' + id).html('');
    $('#file_size' + id).html('');
    $('#attachment' + lastAttachment).show();
    uploadedFiles[id] = false;
}

/**
 * Disables/Enables form elements
 */
function toggleDisableForm(disabled) {
    $("#subject").prop('disabled', disabled);
    $("#body").prop('disabled', disabled);
    $("#btn_back").prop('disabled', disabled);
    $("#btn_save_draft").prop('disabled', disabled);
    $("#btn_send").prop('disabled', disabled);
}


/**
 * Uploads the attachment file
 */
function uploadFile() {
    $("#compose").append($('<iframe></iframe>').attr({'id': 'ifrm_upload', 'name':'ifrm_upload'}));
    $('#attachment_number').val(lastAttachment);
    $('#attachment' + lastAttachment).hide();
    $('#attach_loading').show();
    toggleDisableForm(true);
    $('#frm_file').submit();
}

/**
 * Sets the uploaded file as attachment
 */
function onUpload(response) {
    toggleDisableForm(false);
    uploadedFiles[lastAttachment] = response.file_info;
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_file').reset();
        $('#attachment' + lastAttachment).show();
    } else {
        $('#file_link' + lastAttachment).html(response.file_info.title);
        $('#file_size' + lastAttachment).html(response.file_info.filesize_format);
        $('#btn_attach' + lastAttachment).show();
        $('#attachment' + lastAttachment).remove();
        addFileEntry();
    }
    $('#attach_loading').hide();
    $('#ifrm_upload').remove();
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

    $('#attachment_addentry' + id).html( entry + '<span id="attachment_addentry' + (id + 1) + '">' + $('#attachment_addentry' + id).html() + '</span>');

    $('#attach_loading').hide();
    $('#btn_attach' + id).hide();
}

/**
 * send a message
 */
function sendMessage(isDraft) {

    // detect pre load users or groups list
    if (recipient_user == "" || recipient_user.length == 0) {
        var recipient_users_array = new Array();
        var recipient_friends_array = new Array();

        var users = $('#recipientUsers').pillbox('items');
        if (users.length > 0) {
            $.each(users, function (key, user) {
                recipient_users_array.push(user.value);
            });
        }

        $("#recipient_users > option").each(function () {
            if (this.value.length > 0) {
                recipient_users_array.push(this.value);
            }
        });

        $("input[type=checkbox][name=friends]:checked").each(function () {
            if (this.value!="") {
                recipient_friends_array.push(this.value);
            }
        });

        var recipient_users = recipient_users_array.join(',');
        var recipient_friends = recipient_friends_array.join(',');
    } else {
        var recipient_users = recipient_user;
        var recipient_friends = "";
    }

    var attachments = uploadedFiles.concat(getSelectedAttachments());
    PrivateMessageAjax.callAsync(
        'SendMessage', {
            'id': $('#id').val(),
            'is_draft':isDraft,
            'recipient_users':recipient_users,
            'recipient_friends':recipient_friends,
            'subject':$('#subject').val(),
            'body':getEditorValue('#body'),
            'attachments':attachments
        }
    );
}

function getSelectedAttachments() {
    var files = [];
    $('input[name=selected_files\\[\\]] :checked').each(function(i, selected){
        files.push($(selected).text() );
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
    $('#userSearchResult').show();
    $('#userSearchResult').html('<a class="delete" href="javascript:clearUsersSearch();"></a>');
    for (var i = 0; i < users.length; i++) {
        $("#userSearchResult").append('<div id="searchResult' + users[i]['id'] +
            '" data-user-id="' + users[i]['id'] +
            '" onclick="' + 'addUserToList(' + users[i]['id'] + ',\'' + users[i]['nickname'] +'\')' + '">' +
            users[i]['nickname'] + '(' +users[i]['username'] + ')'+ '</div>');
    }
}

/**
 * Clear users search result
 */
function clearUsersSearch() {
    $('#userSearchResult').html('<a class="delete" href="javascript:clearUsersSearch();"></a>');
    $('#userSearchResult').hide();
}

/**
 * Add a user to recipient List
 */
function addUserToList(userId, title) {
    if ($('#recipient_users option[value=' + userId + ']').length > 0) {
        return;
    }

    $('#recipient_users')
        .append($("<option></option>")
            .attr("value",userId)
            .text(title));

    //var box = $('#recipient_users');
    //box.options[box.options.length] = new Option(title, userId);
}

/**
 * Remove selected user from recipient list
 */
function removeUserFromList() {
    $("#recipient_users option:selected").remove();
}

function unselectUserGroup () {
    $("#recipient_friends option:selected").prop("selected", false);
}

var PrivateMessageAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback);

var uploadedFiles = new Array();
var lastAttachment = 1;
var searchTimeout = null;