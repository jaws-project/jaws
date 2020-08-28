/**
 * PrivateMessage Javascript actions
 *
 * @category    Ajax
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_PrivateMessage() { return {
    // ASync callback method
    AjaxCallback : {
        SendMessage: function (response) {
            if (response.type == 'alert-success') {
                if (response.data && response.data.is_draft) {
                    $('#id').val(response.data.message_id);
                    this.resetAttachments(response.data.message_id);
                } else {
                    setTimeout(function() {window.location.href = response.data.url;}, 1000);
                }
            }
        },
        DeleteMessage: function (response) {
            if (response.type == 'alert-success') {
                $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                if (this.selectedMessage > 0) {
                    $('#messageModal').modal('hide');
                }
            }
        },
        TrashMessage: function (response) {
            if (response.type == 'alert-success') {
                $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                if (this.selectedMessage > 0) {
                    $('#messageModal').modal('hide');
                }
            }
        },
        ArchiveMessage: function (response) {
            if (response.type == 'alert-success') {
                $('#messages-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                if (this.selectedMessage > 0) {
                    $('#messageModal').modal('hide');
                }
            }
        }
    },

    selectedMessage: 0,
    uploadedFiles: [],
    lastAttachment: 1,
    searchTimeout: null,

    /**
     * get Selected DataGrid Items
     */
    getSelectedDatagridItems: function (dgHelpers) {
        var ids = [];
        if (dgHelpers.length > 1) {
            dgHelpers.forEach(function (entry) {
                ids.push(entry.rowData.id);
            });
        } else {
            ids.push(dgHelpers.rowData.id);
        }
        return ids;
    },

    /**
     * Reset attachments after save draft a message
     */
    resetAttachments: function (message_id) {
        var ui = this.ajax.callAsync('GetMessageAttachmentUI',
            {'id': message_id},
            function (response, status, callOptions) {
            if (response['type'] == 'alert-success') {
                $('#attachment_area').html(ui);
                this.uploadedFiles = [];
                this.lastAttachment = 1;
                $('#attachment1').show();
                $('#attach_loading').hide();
                $('#btn_attach1').hide();
            }
        });

    },

    /**
     * Removes the attachment
     */
    removeAttachment: function (id) {
        $('#frm_file').reset();
        $('#btn_attach' + id).hide();
        $('#file_link' + id).html('');
        $('#file_size' + id).html('');
        $('#attachment' + this.lastAttachment).show();
        this.uploadedFiles[id] = false;
    },

    /**
     * Disables/Enables form elements
     */
    toggleDisableForm: function (disabled) {
        $("#subject").prop('disabled', disabled);
        $("#body").prop('disabled', disabled);
        $("#btn_back").prop('disabled', disabled);
        $("#btn_save_draft").prop('disabled', disabled);
        $("#btn_send").prop('disabled', disabled);
    },

    /**
     * Uploads the attachment file
     */
    uploadFile: function () {
        $("#compose").append($('<iframe></iframe>').attr({'id': 'ifrm_upload', 'name': 'ifrm_upload'}));
        $('#attachment_number').val(this.lastAttachment);
        $('#attachment' + this.lastAttachment).hide();
        $('#attach_loading').show();
        this.toggleDisableForm(true);
        $('#frm_file').submit();
    },

    /**
     * add a file entry
     */
    addFileEntry: function () {
        this.lastAttachment++;
        var id = this.lastAttachment;

        entry = '<div id="btn_attach' + id + '"> <img src="gadgets/PrivateMessage/Resources/images/attachment.png"/> <a id="file_link' + id + '"></a> ' +
            ' <small id="file_size' + id + '"></small> <a onclick="javascript:Jaws_Gadget.getInstance("PrivateMessage").removeAttachment(' + id + ');" href="javascript:void(0);">' +
            '<img border="0" title="Remove" alt="Remove" src="images/stock/cancel.png"></a></div>';
        entry += ' <input type="file" onchange="javascript:Jaws_Gadget.getInstance("PrivateMessage").uploadFile();" id="attachment' + this.lastAttachment + '" name="attachment' + this.lastAttachment + '" size="1" style="display: block;">';

        $('#attachment_addentry' + id).html(entry + '<span id="attachment_addentry' + (id + 1) + '">' + $('#attachment_addentry' + id).html() + '</span>');

        $('#attach_loading').hide();
        $('#btn_attach' + id).hide();
    },

    /**
     * Sets the uploaded file as attachment
     */
    onUpload: function (response) {
        this.toggleDisableForm(false);
        this.uploadedFiles[this.lastAttachment] = response.file_info;
        if (response.type === 'error') {
            alert(response.message);
            $('#frm_file').reset();
            $('#attachment' + this.lastAttachment).show();
        } else {
            $('#file_link' + this.lastAttachment).html(response.file_info.title);
            $('#file_size' + this.lastAttachment).html(response.file_info.filesize_format);
            $('#btn_attach' + this.lastAttachment).show();
            $('#attachment' + this.lastAttachment).remove();
            this.addFileEntry();
        }
        $('#attach_loading').hide();
        $('#ifrm_upload').remove();
    },

    /**
     * send a message
     */
    sendMessage: function (defaultRecipientUser, isDraft) {

        // detect pre load users or groups list
        if (defaultRecipientUser === 0) {
            var recipient_users_array = [];
            var recipient_groups_array = [];

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
                if (this.value != "") {
                    recipient_groups_array.push(this.value);
                }
            });

            var recipient_users = recipient_users_array.join(',');
            var recipient_groups = recipient_groups_array.join(',');
        } else {
            var recipient_users = this.gadget.defines.recipient_user;
            var recipient_groups = "";
        }

        var attachments = this.uploadedFiles.concat(this.getSelectedAttachments());
        this.ajax.callAsync(
            'SendMessage', {
                'id': $('#id').val(),
                'is_draft': isDraft,
                'recipient_users': recipient_users,
                'recipient_groups': recipient_groups,
                'subject': $('#subject').val(),
                'body': $('#body').val(),
                'attachments': attachments
            }
        );
    },

    /**
     * Get selected attachments
     */
    getSelectedAttachments: function () {
        var files = [];
        $('input[name=selected_files\\[\\]] :checked').each(function (i, selected) {
            files.push($(selected).text());
        });
        return files;
    },

    /**
     * Search users step 1
     */
    searchUsersStart: function (term) {
        if (this.searchTimeout !== null) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = setTimeout("this.searchUsers('" + term + "');", 1000);
    },

    /**
     * Search users step 2
     */
    searchUsers: function (term) {
        this.searchTimeout = null;
        this.ajax.callAsync('GetUsers',
            {'term': term},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    var users = response.data;
                    if (users.length < 1) {
                        clearUsersSearch();
                        return;
                    }
                    $('#userSearchResult').show();
                    $('#userSearchResult').html('<a class="delete" href="javascript:Jaws_Gadget.getInstance("PrivateMessage").clearUsersSearch();"></a>');
                    for (var i = 0; i < users.length; i++) {
                        $("#userSearchResult").append('<div id="searchResult' + users[i]['id'] +
                            '" data-user-id="' + users[i]['id'] +
                            '" onclick="' + 'addUserToList(' + users[i]['id'] + ',\'' + users[i]['nickname'] + '\')' + '">' +
                            users[i]['nickname'] + '(' + users[i]['username'] + ')' + '</div>');
                    }
                }
            });
    },

    /**
     * Clear users search result
     */
    clearUsersSearch: function () {
        $('#userSearchResult').html(
            '<a class="delete" href="javascript:Jaws_Gadget.getInstance("PrivateMessage").clearUsersSearch();"></a>'
        ).hide();
    },

    /**
     * Add a user to recipient List
     */
    addUserToList: function (userId, title) {
        if ($('#recipient_users option[value=' + userId + ']').length > 0) {
            return;
        }

        $('#recipient_users').append($("<option></option>")
                .attr("value", userId)
                .text(title));
    },

    /**
     * Remove selected user from recipient list
     */
    removeUserFromList: function () {
        $("#recipient_users option:selected").remove();
    },

    /**
     * Unselect User Group
     */
    unselectUserGroup: function () {
        $("#recipient_groups option:selected").prop("selected", false);
    },

    /**
     * Change toggle icon
     */
    ChangeToggleIcon: function (obj) {
        if ($(obj).attr('toggle-status')=== 'min') {
            $(obj).find("img").attr('src', this.gadget.defines.toggleMin);
            $(obj).attr('toggle-status', 'max');
        } else {
            $(obj).find("img").attr('src', this.gadget.defines.toggleMax);
            $(obj).attr('toggle-status', 'min');
        }
    },

    /**
     * initiate Compose action
     */
    initiateCompose: function () {
        $('#attachment1').show();
        $('#attach_loading').hide();
        $('#btn_attach1').hide();
        $('#attachment_area').toggle();

        // initiate pillbox
        $('#recipientUsers').pillbox({
            onKeyDown: $.proxy(function (inputData, callback) {
                var term = inputData.value;
                var keyCode = inputData.event.keyCode;
                if (keyCode > 31) {
                    term += inputData.event.key;
                } else if (keyCode === 8) {
                    term = term.slice(0, -1);
                } else {
                    return false;
                }

                this.ajax.callAsync(
                    'GetUsers',
                    {'term': term},
                    function (response, status) {
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

                if (keyCode === 8) {
                    if (!$(inputData.event.target).is("input, textarea")) {
                        inputData.event.preventDefault();
                        return false;
                    }
                }
            }, this),

            // prevent duplicated item
            onAdd: $.proxy(function (data, callback) {
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

                var userExist = this.ajax.callSync('CheckUserExist', {'user': data.value});
                if (!duplicated && userExist) {
                    callback(data);
                }
            }, this)
        });

        $('#recipientUsers').pillbox('addItems', 1, recipientUsersInitiate);
        $("#legend_attachments").click($.proxy(function () {
            $('#attachment_area').toggle();
            this.ChangeToggleIcon(this);
        }, this));
    },

    /**
     * Compose message UI
     */
    composeMessage: function () {
        this.ajax.callAsync(
            'Compose',
            {},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#message-form').html(response.data.ui);
                    $('#messageModal .gadget_header').hide();
                    $('#messageModal').modal('show');

                    this.initiateCompose();
                }
            });
    },

    /**
     * View message
     */
    viewMessage: function (id) {
        this.selectedMessage = id;
        this.ajax.callAsync(
            'Message',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#message-form').html(response.data.ui);
                    $('#messageModal .gadget_header').hide();
                    $('#messageModalLabel').html(this.gadget.defines.lbl_view_message);
                    $('#messageModal').modal('show').on('hide.bs.modal', $.proxy(function (e) {
                        this.selectedMessage = null;
                    }, this));

                    this.initMessageForm(response.data.message.folder);
                }
            });
    },

    /**
     * init Message Form
     */
    initMessageForm: function (folder) {
        switch (folder) {
            case this.gadget.defines.folders.inbox:
            case this.gadget.defines.folders.notifications:
            case this.gadget.defines.folders.outbox:
                $('#btnArchiveMessage').show();
                $('#btnTrashMessage').show();
                break;
            case this.gadget.defines.folders.archived:
                $('#btnRestoreArchiveMessage').show();
                $('#btnTrashMessage').show();
                break;
            case this.gadget.defines.folders.trash:
                $('#btnRestoreTrashMessage').show();
                $('#btnDeleteMessage').show();
                break;
        }
    },

    /**
     * Delete message
     */
    deleteMessage: function (ids) {
        if (confirm(this.gadget.defines.confirmDelete)) {
            this.ajax.callAsync(
                'DeleteMessage',
                {'ids': ids}
            );
        }
    },

    /**
     * Trash message
     */
    trashMessage: function (ids) {
        if (confirm(this.gadget.defines.confirmDelete)) {
            this.ajax.callAsync(
                'TrashMessage',
                {'ids': ids}
            );
        }
    },

    /**
     * Restore trash message
     */
    restoreTrashMessage: function (ids) {
        this.ajax.callAsync(
            'RestoreTrashMessage',
            {'ids': ids}
        );
    },

    /**
     * Archive Message
     */
    archiveMessage: function (ids, doArchive) {
        this.ajax.callAsync(
            'ArchiveMessage',
            {'ids': ids, 'archive': doArchive}
        );
    },

    /**
     * Mark Message (as read or unread)
     */
    markMessage: function (ids, read) {
        this.ajax.callAsync(
            'ChangeMessageRead',
            {'ids': ids, 'read': read}
        );
    },

    /**
     * Define the data to be displayed in the messages datagrid
     */
    messagesDataSource: function (options, callback) {
        var columns = this.gadget.defines.grid.columns;

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.ajax.callAsync(
            'GetMessages', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'folder': this.gadget.defines.folder,
                'filters': {
                    'term': $('#filter_term').val(),
                    'read': ($('#filter_read').val() === undefined) ? null : $('#filter_read').val()
                }
            },function (response, status, callOptions) {
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
    },

    /**
     * messages Datagrid column renderer
     */
    messagesDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'have_attachment':
                if (helpers.item.text()) {
                    customMarkup = '<span class="glyphicon glyphicon-paperclip"></span>'
                } else {
                    customMarkup = '';
                }
                break;
            default:
                customMarkup = helpers.item.text();
                break;
        }

        helpers.item.html(customMarkup);

        callback();
    },

    /**
     * messages Datagrid column renderer
     */
    messagesDGRowRenderer: function (helpers, callback) {
        if (helpers.rowData.read) {
            helpers.item.css('font-weight', 'bold');
        }
        callback();
    },

    /**
     * initiate messages dataGrid
     */
    initiateMessagesDG: function () {
        var menu_items_default = [];

        var menu_item_view = {
            name: 'view',
            html: '<span class="glyphicon glyphicon-eye-open"></span> ' + this.gadget.defines.lbl_view,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.viewMessage(helpers.rowData.id);
                callback();
            }, this)
        };
        var menu_item_delete = {
            name: 'delete',
            html: '<span class="glyphicon glyphicon-remove"></span> ' + this.gadget.defines.lbl_delete,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.deleteMessage(this.getSelectedDatagridItems(helpers));
                callback();
            }, this)
        };
        var menu_item_archive = {
            name: 'archive',
            html: '<span class="glyphicon glyphicon-folder-close"></span> ' + this.gadget.defines.lbl_archive,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.archiveMessage(this.getSelectedDatagridItems(helpers), true);
                callback();
            }, this)
        };
        var menu_item_unarchive = {
            name: 'unArchive',
            html: '<span class="glyphicon glyphicon-folder-open"></span> ' + this.gadget.defines.lbl_unarchive,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.archiveMessage(this.getSelectedDatagridItems(helpers), false);
                callback();
            }, this)
        };
        var menu_item_trash = {
            name: 'trash',
            html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.lbl_trash,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.trashMessage(this.getSelectedDatagridItems(helpers));
                callback();
            }, this)
        };
        var menu_item_restore_trash = {
            name: 'trash',
            html: '<span class="glyphicon glyphicon-floppy-saved"></span> ' + this.gadget.defines.lbl_restore_trash,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.restoreTrashMessage(this.getSelectedDatagridItems(helpers));
                callback();
            }, this)
        };
        var menu_item_markAsRead = {
            name: 'markRead',
            html: '<span class="glyphicon glyphicon-check"></span> ' + this.gadget.defines.lbl_mark_as_read,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.markMessage(this.getSelectedDatagridItems(helpers), true);
                callback();
            }, this)
        };
        var menu_item_markAsUnRead = {
            name: 'markUnRead',
            html: '<span class="glyphicon glyphicon-unchecked"></span> ' + this.gadget.defines.lbl_mark_as_unread,
            clickAction: $.proxy(function (helpers, callback, e) {
                e.preventDefault();
                this.markMessage(this.getSelectedDatagridItems(helpers), false);
                callback();
            }, this)
        };

        menu_items_default.push(menu_item_view);
        switch (this.gadget.defines.folder) {
            case this.gadget.defines.folders.inbox:
            case this.gadget.defines.folders.notifications:
                menu_items_default.push(menu_item_archive);
                menu_items_default.push(menu_item_markAsRead);
                menu_items_default.push(menu_item_markAsUnRead);
                menu_items_default.push(menu_item_trash);
                break;
            case this.gadget.defines.folders.outbox:
                menu_items_default.push(menu_item_archive);
                menu_items_default.push(menu_item_trash);
                break;
            case this.gadget.defines.folders.archived:
                menu_items_default.push(menu_item_unarchive);
                menu_items_default.push(menu_item_trash);
                break;
            case this.gadget.defines.folders.trash:
                menu_items_default.push(menu_item_restore_trash);
                menu_items_default.push(menu_item_delete);
                break;
            case this.gadget.defines.folders.draft:
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
            dataSource: $.proxy(this.messagesDataSource, this),
            list_actions: list_actions,
            list_selectable: 'multi',
            list_direction: $('.repeater-canvas').css('direction'),
            list_columnRendered: $.proxy(this.messagesDGColumnRenderer, this),
            list_rowRendered: $.proxy(this.messagesDGRowRenderer, this),
            list_noItemsHTML: this.gadget.defines.datagridNoItems
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
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction)
    {
        switch (mainAction) {
            case 'Compose':
                this.initiateCompose();
                break;
            case 'Messages':
                this.initiateMessagesDG();

                $('#btnCompose').on('click', $.proxy(function (e) {
                    this.composeMessage();
                }, this));

                break;
            case 'Message':
                this.initMessageForm(this.gadget.defines.folder);
                break;
        }

        $(document).on('click', '#btnArchiveMessage', $.proxy(function (e) {
            this.archiveMessage([this.selectedMessage], true);
        }, this));
        $(document).on('click', '#btnRestoreArchiveMessage', $.proxy(function (e) {
            this.archiveMessage([this.selectedMessage], false);
        }, this));
        $(document).on('click', '#btnTrashMessage', $.proxy(function (e) {
            this.trashMessage([this.selectedMessage]);
        }, this));
        $(document).on('click', '#btnRestoreTrashMessage', $.proxy(function (e) {
            this.restoreTrashMessage([this.selectedMessage]);
        }, this));
        $(document).on('click', '#btnDeleteMessage', $.proxy(function (e) {
            this.deleteMessage([this.selectedMessage]);
        }, this));

    },

}};