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
    if (jaws.gadgets.PrivateMessage.recipient_user == "" || jaws.gadgets.PrivateMessage.recipient_user.length == 0) {
        var recipient_users_array = new Array();
        var recipient_groups_array = new Array();

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
                recipient_groups_array.push(this.value);
            }
        });

        var recipient_users = recipient_users_array.join(',');
        var recipient_groups = recipient_groups_array.join(',');
    } else {
        var recipient_users = jaws.gadgets.PrivateMessage.recipient_user;
        var recipient_groups = "";
    }

    var attachments = uploadedFiles.concat(getSelectedAttachments());
    PrivateMessageAjax.callAsync(
        'SendMessage', {
            'id': $('#id').val(),
            'is_draft':isDraft,
            'recipient_users':recipient_users,
            'recipient_groups':recipient_groups,
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
    $("#recipient_groups option:selected").prop("selected", false);
}

/**
 *
 */
function ChangeToggleIcon(obj)
{
    if ($(obj).attr('toggle-status') == 'min') {
        $(obj).find("img").attr('src', jaws.gadgets.PrivateMessage.toggleMin);
        $(obj).attr('toggle-status', 'max');
    } else {
        $(obj).find("img").attr('src', jaws.gadgets.PrivateMessage.toggleMax);
        $(obj).attr('toggle-status', 'min');
    }
}

function toggleCheckboxes(){
    do_check = !do_check;
    $('.table-checkbox').each(function(el, data) { data.checked = do_check; });
}
var do_check = false;

function messagesDGAction() {
    var action = $("#messages_actions_combo").val();
    if (action == '' || $("input[type=checkbox][name='message_checkbox[]']:checked").length < 1) {
        return false;
    }

    if(action == 'unarchive') {
        $("#privatemessage input[type=hidden][name=action]").val('UnArchiveMessage');
    } else if(action == 'archive') {
        $("#privatemessage input[type=hidden][name=action]").val('ArchiveMessage');
    } else if(action == 'read') {
        $("#privatemessage input[type=hidden][name=action]").val('ChangeMessageRead');
        $("#privatemessage input[type=hidden][name=status]").val('read');
    } else if(action == 'unread') {
        $("#privatemessage input[type=hidden][name=action]").val('ChangeMessageRead');
        $("#privatemessage input[type=hidden][name=status]").val('unread');
    } else if(action == 'trash') {
        $("#privatemessage input[type=hidden][name=action]").val('TrashMessage');
    } else if(action == 'restore_trash') {
        $("#privatemessage input[type=hidden][name=action]").val('RestoreTrashMessage');
    } else if(action == 'delete') {
        if (confirm(jaws.gadgets.PrivateMessage.confirmDelete)) {
            $("#privatemessage input[type=hidden][name=action]").val('DeleteMessage');
        } else {
            return false;
        }
    }

    $("#privatemessage").submit();
    return true;
}

/**
 * Trash Message
 */
function trashMessage() {
    if (confirm(jaws.gadgets.PrivateMessage.confirmDelete)) {
        window.location.href = "{{trash_url}}";
    }
}

/**
 * Delete Message
 */
function deleteMessage() {
    if (confirm(jaws.gadgets.PrivateMessage.confirmDelete)) {
        window.location.href = "{{delete_url}}";
    }
}


/**
 * initiate Compose action
 */
function initiateCompose() {
    $('#attachment1').show();
    $('#attach_loading').hide();
    $('#btn_attach1').hide();
    $('#attachment_area').toggle();

    // initiate pillbox
    $('#recipientUsers').pillbox({
        onKeyDown: function (inputData, callback) {
            var term = inputData.value;
            var keyCode = inputData.event.keyCode;
            if (keyCode > 31) {
                term+= inputData.event.key;
            } else if(keyCode == 8) {
                term = term.slice(0, -1);
            } else {
                return false;
            }

            PrivateMessageAjax.callAsync(
                'GetUsers',
                {'term': term},
                function(response, status) {
                    var data = [];
                    if (response['type'] == 'alert-success' && response['data'].length) {
                        $.each(response['data'], function (key, user) {
                            data.push({text: user.nickname, value: user.id});
                        });
                    } else {
                        data.push({text: '', value: ''});
                    }

                    callback({
                        data: data
                    });
                }
            );

            if (keyCode == 8) {
                if (!$(inputData.event.target).is("input, textarea")) {
                    inputData.event.preventDefault();
                    return false;
                }
            }
        },

        // prevent duplicated item
        onAdd: function (data, callback) {
            var items = $('#recipientUsers').pillbox('items');
            var duplicated = false;

            if (items.length > 0) {
                $.each(items, function (key, item) {
                    if (data.value == item.value) {
                        duplicated = true;
                        return false;
                    }
                });
            }

            var userExist = PrivateMessageAjax.callSync('CheckUserExist', {'user': data.value});
            if (!duplicated && userExist) {
                callback(data);
            }
        }
    });

    $('#recipientUsers').pillbox('addItems', 1, recipientUsersInitiate);
    $( "#legend_attachments" ).click(function() {
        $('#attachment_area').toggle();
        ChangeToggleIcon(this);
    });
}

/**
 * initiate Messages action
 */
function initiateMessages() {
}


$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Compose':
            initiateCompose();
            break;
        case 'Messages':
            initiateMessages();
            break;
    }
});

var PrivateMessageAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback);

var uploadedFiles = new Array();
var lastAttachment = 1;
var searchTimeout = null;
