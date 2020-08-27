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
    },
    DeleteMessage: function (response) {
        if (response.type == 'alert-success') {
            $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }
    },
    TrashMessage: function (response) {
        if (response.type == 'alert-success') {
            $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }
    },
    ArchiveMessage: function (response) {
        if (response.type == 'alert-success') {
            $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }
    }
}

/**
 * get Selected DataGrid Items
 */
function getSelectedDatagridItems(dgHelpers)
{
    var ids = new Array();
    if (dgHelpers.length > 1) {
        dgHelpers.forEach(function(entry) {
            ids.push(entry.rowData.id);
        });
    } else {
        ids.push(dgHelpers.rowData.id);
    }
    return ids;
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
    if (jaws.PrivateMessage.Defines.recipient_user == "" || jaws.PrivateMessage.Defines.recipient_user.length == 0) {
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
        var recipient_users = jaws.PrivateMessage.Defines.recipient_user;
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
            'body':$('#body').val(),
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
        $(obj).find("img").attr('src', jaws.PrivateMessage.Defines.toggleMin);
        $(obj).attr('toggle-status', 'max');
    } else {
        $(obj).find("img").attr('src', jaws.PrivateMessage.Defines.toggleMax);
        $(obj).attr('toggle-status', 'min');
    }
}

function toggleCheckboxes(){
    do_check = !do_check;
    $('.table-checkbox').each(function(el, data) { data.checked = do_check; });
}
var do_check = false;

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
 * Delete Message
 */
function deleteMessage(ids) {
    if (confirm(jaws.PrivateMessage.Defines.confirmDelete)) {
        PrivateMessageAjax.callAsync(
            'DeleteMessage',
            {'ids': ids}
        );
    }
}

/**
 * trash Message
 */
function trashMessage(ids) {
    if (confirm(jaws.PrivateMessage.Defines.confirmDelete)) {
        PrivateMessageAjax.callAsync(
            'TrashMessage',
            {'ids': ids}
        );
    }
}

/**
 * Restore trash message
 */
function restoreTrashMessage(ids) {
    PrivateMessageAjax.callAsync(
        'RestoreTrashMessage',
        {'ids': ids}
    );
}

/**
 * Archive Message
 */
function archiveMessage(ids, doArchive) {
    PrivateMessageAjax.callAsync(
        'ArchiveMessage',
        {'ids': ids, 'archive': doArchive}
    );
}

/**
 * Mark Message (as read or unread)
 */
function markMessage(ids, read) {
    PrivateMessageAjax.callAsync(
        'ChangeMessageRead',
        {'ids': ids, 'read': read}
    );
}

/**
 * Define the data to be displayed in the messages datagrid
 */
function messagesDataSource(options, callback) {
    var columns = jaws.PrivateMessage.Defines.grid.columns;

    // set sort property & direction
    if (options.sortProperty) {
        columns[options.sortProperty].sortDirection = options.sortDirection;
    }
    columns = Object.values(columns);

    PrivateMessageAjax.callAsync(
        'GetMessages', {
            'offset': options.pageIndex * options.pageSize,
            'limit': options.pageSize,
            'sortDirection': options.sortDirection,
            'sortBy': options.sortProperty,
            'folder': jaws.PrivateMessage.Defines.folder,
            'filters': {
                'term': $('#filter_term').val(),
                'read': ($('#filter_read').val() === undefined) ? null : $('#filter_read').val()
            }
        },
        function (response, status, callOptions) {
            var dataSource = {};
            if (response['type'] == 'alert-success') {
                callOptions.showIP = false;

                // processing end item index of page
                options.end = options.offset + options.pageSize;
                options.end = (options.end > response['data'].total) ? response['data'].total : options.end;
                dataSource = {
                    'page': options.pageIndex,
                    'pages': Math.ceil(response['data'].total / options.pageSize),
                    'count': response['data'].total,
                    'start': options.offset + 1,
                    'end': options.end,
                    'columns': columns,
                    'items': response['data'].records
                };
            } else {
                dataSource = {
                    'page': 0,
                    'pages': 0,
                    'count': 0,
                    'start': 0,
                    'end': 0,
                    'columns': columns,
                    'items': {}
                };
            }
            // pass the datasource back to the repeater
            callback(dataSource);
        }
    );
}

/**
 * initiate messages dataGrid
 */
function initiateMessagesDG() {
    var menu_items_default = [];

    var menu_item_delete = {
        name: 'delete',
        html: '<span class="glyphicon glyphicon-remove"></span> ' + jaws.PrivateMessage.Defines.lbl_delete,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.deleteMessage(getSelectedDatagridItems(helpers));
            callback();
        }, this)
    };
    var menu_item_archive = {
        name: 'archive',
        html: '<span class="glyphicon glyphicon-folder-close"></span> ' + jaws.PrivateMessage.Defines.lbl_archive,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.archiveMessage(getSelectedDatagridItems(helpers), true);
            callback();
        }, this)
    };
    var menu_item_unarchive = {
        name: 'unArchive',
        html: '<span class="glyphicon glyphicon-folder-open"></span> ' + jaws.PrivateMessage.Defines.lbl_unarchive,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.archiveMessage(getSelectedDatagridItems(helpers), false);
            callback();
        }, this)
    };
    var menu_item_trash = {
        name: 'trash',
        html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.PrivateMessage.Defines.lbl_trash,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.trashMessage(getSelectedDatagridItems(helpers));
            callback();
        }, this)
    };
    var menu_item_restore_trash = {
        name: 'trash',
        html: '<span class="glyphicon glyphicon-check"></span> ' + jaws.PrivateMessage.Defines.lbl_restore_trash,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.restoreTrashMessage(getSelectedDatagridItems(helpers));
            callback();
        }, this)
    };
    var menu_item_markAsRead = {
        name: 'markRead',
        html: '<span class="glyphicon glyphicon-eye-open"></span> ' + jaws.PrivateMessage.Defines.lbl_mark_as_read,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.markMessage(getSelectedDatagridItems(helpers), true);
            callback();
        }, this)
    };
    var menu_item_markAsUnRead = {
        name: 'markUnRead',
        html: '<span class="glyphicon glyphicon-eye-close"></span> ' + jaws.PrivateMessage.Defines.lbl_mark_as_unread,
        clickAction: $.proxy(function (helpers, callback, e) {
            e.preventDefault();
            this.markMessage(getSelectedDatagridItems(helpers), false);
            callback();
        }, this)
    };

    switch (jaws.PrivateMessage.Defines.folder) {
        case jaws.PrivateMessage.Defines.folders.inbox:
        case jaws.PrivateMessage.Defines.folders.notifications:
            menu_items_default.push(menu_item_archive);
            menu_items_default.push(menu_item_markAsRead);
            menu_items_default.push(menu_item_markAsUnRead);
            menu_items_default.push(menu_item_trash);
            break;
        case jaws.PrivateMessage.Defines.folders.outbox:
            menu_items_default.push(menu_item_archive);
            menu_items_default.push(menu_item_trash);
            break;
        case jaws.PrivateMessage.Defines.folders.archived:
            menu_items_default.push(menu_item_unarchive);
            menu_items_default.push(menu_item_trash);
            break;
        case jaws.PrivateMessage.Defines.folders.trash:
            menu_items_default.push(menu_item_restore_trash);
            menu_items_default.push(menu_item_delete);
            break;
        case jaws.PrivateMessage.Defines.folders.draft:
        default:
            menu_items_default.push(menu_item_delete);
            break;
    }

    var list_actions = {
        width: 50,
        items: menu_items_default
    };

    // initialize the repeater
    $('#messages-grid').repeater({
        dataSource: messagesDataSource,
        list_actions: list_actions,
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction')
    });

    // monitor required events
    $(".datagrid-filters select").change(function () {
        $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
    });
    $(".datagrid-filters input").keypress(function (e) {
        if (e.which == 13) {
            $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }
    });
    $("#messages-grid button.btn-refresh").on('click', function (e) {
        $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
    });
}


$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'Compose':
            initiateCompose();
            break;
        case 'Messages':
            initiateMessagesDG();
            break;
    }
});

var PrivateMessageAjax = new JawsAjax('PrivateMessage', PrivateMessageCallback);

var uploadedFiles = new Array();
var lastAttachment = 1;
var searchTimeout = null;
