/**
 * Notification Javascript actions
 *
 * @category    Ajax
 * @package     Notification
 */
function Jaws_Gadget_Notification() { return {
    //------------------------------------------------------------------------------------------------------------------
    /**
     * initialize gadget actions
     */
    //------------------------------------------------------------------------------------------------------------------
    init: function (mainGadget, mainAction) {
    }

}};

function Jaws_Gadget_Notification_Action_Messages() { return {
    // ASync callback method
    AjaxCallback: {
    },

    /**
     * Define the data to be displayed in the users datagrid
     */
    messagesDataSource: function(options, callback) {
        var columns = [
            {
                'label': this.gadget.defines.lbl_message_title,
                'property': 'message_title',
                'sortable': true
            },
            {
                'label': this.gadget.defines.lbl_message_type,
                'property': 'message_type',
            },
            {
                'label': this.gadget.defines.lbl_shouter,
                'property': 'shouter',
            },
            {
                'label': this.gadget.defines.lbl_insert_time,
                'property': 'time',
            },
            {
                'label': this.gadget.defines.lbl_status,
                'property': 'status',
            }
        ];

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
                'filters': {
                    status: $('#filter_status').val(),
                    shouter: $('#filter_shouter').val(),
                    driver: $('#filter_message_type').val(),
                    from_date: $('#datepicker_filter_from_date_input').val(),
                    to_date: $('#datepicker_filter_to_date_input').val(),
                    contact: $('#filter_contact').val(),
                    verbose: $('#filter_verbose').val(),
                }
            },
            function (response, status, callOptions) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

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
     * initiate Messages dataGrid
     */
    initiateMessagesDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'view',
                    html: '<span class="glyphicon glyphicon-eye-open"></span> ' + this.gadget.defines.lbl_view,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.viewMessage(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'deleteMessage',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.lbl_delete_message,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteMessage(helpers.rowData.id, false);
                        callback();
                    }, this)
                },
                {
                    name: 'deleteSimilarMessage',
                    html: '<span class="glyphicon glyphicon-remove"></span> ' + this.gadget.defines.lbl_delete_similar_message,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteMessage(helpers.rowData.id, true);
                        callback();
                    }, this)
                }
            ]
        };

        // initialize the repeater
        $('#messages-grid').repeater({
            dataSource: $.proxy(this.messagesDataSource, this),
            staticHeight: 700,
            list_actions: list_actions,
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
    },

    /**
     * View message details
     */
    viewMessage: function (id) {
        this.ajax.callAsync(
            'GetMessage',
            {'recipient_id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    $('#notification-message-form span').each(
                        $.proxy(function (key, elem) {
                            $(elem).html(response.data[$(elem).data('field')]);
                        }, this)
                    );

                    $('#messageModal').modal('show');
                }
            });
    },

    /**
     * Delete a message
     */
    deleteMessage: function (id, deleteSimilar) {
        var confirmMessage = '';
        if (deleteSimilar === true) {
            confirmMessage = this.gadget.defines.confirmDeleteSimilarMessage;
        } else {
            confirmMessage = this.gadget.defines.confirmDeleteMessage;
        }
        if (!confirm(confirmMessage)) {
            return false;
        }

        this.ajax.callAsync(
            'DeleteMessage',
            {'recipient_id': id, 'delete_similar': deleteSimilar},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#messages-grid').repeater('render');
                }
            });
    },

    //------------------------------------------------------------------------------------------------------------------
    /**
     * initialize gadget actions
     */
    //------------------------------------------------------------------------------------------------------------------
    init: function (mainGadget, mainAction) {
        this.initiateMessagesDG();
    }
}};

function Jaws_Gadget_Notification_Action_NotificationDrivers() { return {
    selectedDriver : null,

    // ASync callback method
    AjaxCallback: {
        InstallNotificationDriver: function(response) {
            if (response['type'] == 'alert-success') {
                getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
                this.stopAction();
            }
        },
        UninstallNotificationDriver: function(response) {
            if (response['type'] == 'alert-success') {
                getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
                this.stopAction();
            }
        },
        UpdateNotificationDriver: function(response) {
            if (response['type'] == 'alert-success') {
                getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
                this.stopAction();
            }
        },
    },

    /**
     * Clears the form
     */
    stopAction: function()
    {
        this.selectedDriver = null;
        unselectGridRow('notification_drivers_datagrid');
        $('#driver_settings_ui').hide();
        $('#title').val('');
        $('#enabled').val(1);
        $('#title').focus();
    },

    /**
     * Get product exports items (invoice items)
     *
     */
    getNotificationDrivers: function(name, offset, reset) {
    var result = this.ajax.callSync('GetNotificationDrivers');
    resetGrid(name, result);
},

    /**
     * Install a notification driver
     */
    installDriver: function (rowElement, dName) {
        selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
        this.ajax.callAsync('InstallNotificationDriver', {'driver': dName});
    },

    /**
     * Uninstall a notification driver
     */
    uninstallDriver: function (rowElement, dName) {
        selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
        this.ajax.callAsync('UninstallNotificationDriver', {'driver': dName});
    },

    /**
     * Edits a notification driver
     */
    editNotificationDriver: function (rowElement, id) {
        $('#driver_settings_ui').show();

        selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
        this.selectedDriver = id;

        var driver = this.ajax.callSync('GetNotificationDriver', {'id': id});
        $('#title').val(driver['title'].defilter());
        $('#enabled').val(driver['enabled'] ? 1 : 0);

        var settingsUI = this.ajax.callSync('GetNotificationDriverSettingsUI', {'id': id});
        $('#driver_settings_area').html(settingsUI);
    },

    /**
     * Updates the notification driver
     */
    updateNotificationDriver: function () {
        if ($('#title').val().blank() || this.selectedDriver == null) {
            alert(this.gadget.defines.incompleteFields);
            return;
        }

        var data = $.unserialize($('#driver_form').serialize());
        var settings = $.unserialize($('#driver_settings_ui').serialize());
        this.ajax.callAsync('UpdateNotificationDriver', {
            'id': this.selectedDriver,
            'data': data,
            'settings': settings
        })
    },


    //------------------------------------------------------------------------------------------------------------------
    /**
     * initialize gadget actions
     */
    //------------------------------------------------------------------------------------------------------------------
    init: function (mainGadget, mainAction) {
        // this.ajax.defaultOptions.showMessage = false;
        initDataGrid('notification_drivers_datagrid', this.gadget, this.getNotificationDrivers);
    }
}};

function Jaws_Gadget_Notification_Action_Settings() { return {
    // ASync callback method
    AjaxCallback: {
    },

    /**
     * save gadget settings
     */
    saveSettings: function(form) {
    this.ajax.callAsync(
        'SaveSettings',
        {
            'gadgets_drivers': $.unserialize($('#gadgets_drivers select').serialize())
        }
    );
},

    //------------------------------------------------------------------------------------------------------------------
    /**
     * initialize gadget actions
     */
    //------------------------------------------------------------------------------------------------------------------
    init: function (mainGadget, mainAction) {
        // this.ajax.defaultOptions.showMessage = false;
    }
}};
