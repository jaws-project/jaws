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
            staticHeight: 650,
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
    },

    /**
     * Get product exports items (invoice items)
     *
     */
    getNotificationDrivers: function (name, offset, reset) {
        var result = this.ajax.callSync('GetNotificationDrivers');
        resetGrid(name, result);
    },

    /**
     * Install a notification driver
     */
    installDriver: function (dName) {
        this.ajax.callAsync(
            'InstallNotificationDriver', {'driver': dName},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#drivers-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            });
    },

    /**
     * Uninstall a notification driver
     */
    uninstallDriver: function (dName) {
        this.ajax.callAsync(
            'UninstallNotificationDriver', {'driver': dName},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#drivers-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            });
    },

    /**
     * Edits a notification driver
     */
    editNotificationDriver: function (driverInfo) {
        this.selectedDriver = driverInfo.id;

        $('#title').val(driverInfo.title.defilter());
        $('#enabled').val(driverInfo.enabled ? 1 : 0);

        this.ajax.callAsync(
            'GetNotificationDriverSettings',
            {'id': driverInfo.id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    $('#driver-settings-container').html('');
                    $.each(response.data, $.proxy(function (optName, optValue) {
                        $('<div class="col-md-6 col-xs-12">' +
                            '<div class="form-group">' +
                            '    <label class="col-sm-3 control-label">' + optName + ':</label>' +
                            '    <div class="col-sm-9">' +
                            '        <input type="text" class="form-control ltr" id="' + optName + '" name="' + optName +
                            '" title="' + optName + '" value="' + optValue + '">' +
                            '    </div>' +
                            '</div>' +
                            '</div>').appendTo($('#driver-settings-container'));
                        }, this)
                    );
                }

                $('#driverSettingsModal').modal('show');
            });
    },

    /**
     * Updates the notification driver
     */
    updateNotificationDriver: function () {
        if ($('#title').val().blank() || this.selectedDriver == null) {
            alert(this.gadget.defines.incompleteFields);
            return;
        }

        var data = $.unserialize($('#driver-form').serialize());
        var settings = $.unserialize($('#driver-settings-form').serialize());
        this.ajax.callAsync(
            'UpdateNotificationDriver', {
                'id': this.selectedDriver,
                'data': data,
                'settings': settings
            },
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    $('#driverSettingsModal').modal('hide');
                    $('#drivers-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                    this.selectedDriver = null;
                }
            });
    },

    /**
     * Define the data to be displayed in the users datagrid
     */
    driversDataSource: function(options, callback) {
        var columns = [
            {
                'label': this.gadget.defines.lbl_title,
                'property': 'title',
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
            'GetNotificationDrivers', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': {}
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
     * initiate Drivers dataGrid
     */
    initiateDriversDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'install',
                    html: '<span class="glyphicon glyphicon-ok"></span> ' + this.gadget.defines.lbl_install,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        if (helpers.rowData.installed === true) {
                            return;
                        }
                        this.installDriver(helpers.rowData.name);
                        callback();
                    }, this)
                },
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-edit"></span> ' + this.gadget.defines.lbl_edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editNotificationDriver(helpers.rowData);
                        callback();
                    }, this)
                },
                {
                    name: 'uninstall',
                    html: '<span class="glyphicon glyphicon-remove"></span> ' + this.gadget.defines.lbl_uninstall,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        if (helpers.rowData.installed === false) {
                            return;
                        }
                        this.uninstallDriver(helpers.rowData.id);
                        callback();
                    }, this)
                }
            ]
        };

        // initialize the repeater
        $('#drivers-grid').repeater({
            dataSource: $.proxy(this.driversDataSource, this),
            staticHeight: 400,
            list_actions: list_actions,
            list_direction: $('.repeater-canvas').css('direction')
        });

        $("#drivers-grid button.btn-refresh").on('click', function (e) {
            $('#drivers-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    //------------------------------------------------------------------------------------------------------------------
    /**
     * initialize gadget actions
     */
    //------------------------------------------------------------------------------------------------------------------
    init: function (mainGadget, mainAction) {
        this.initiateDriversDG();

        $('#btn-save-notification-driver').on('click', $.proxy(function (e) {
            this.updateNotificationDriver();
        }, this));
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
