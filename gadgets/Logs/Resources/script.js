/**
 * Logs Javascript actions
 *
 * @category    Ajax
 * @package     Logs
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

function Jaws_Gadget_Logs() { return {
    // ASync callback method
    AjaxCallback: {
        DeleteLogs: function (response) {
            if (response['type'] == 'alert-success') {
                $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
    },

    /**
     * Add option to combo box
     */
    addOptionToCombo: function (comboElement, data, emptyCombo = false) {
        if (emptyCombo) {
            $(comboElement).find('div.input-group-btn ul.dropdown-menu').html('');
        }
        $(comboElement).find('div.input-group-btn ul.dropdown-menu').append(
            '<li data-value="' + data.value + '"><a href="#">' + data.title + '</a></li>'
        );
    },

    /**
     * Cancel select combobox
     */
    cancelSelectCombobox: function(comboElement) {
        $(comboElement).find('div.input-group-btn ul.dropdown-menu').html('');
        $(comboElement).find('>input').val('');
        $(comboElement).combobox('enable').combobox('selectByIndex', '0');
        $(comboElement).find('>input').val('');
        $(comboElement).trigger('keyup.fu.combobox');
    },

    /**
     * Define the data to be displayed in the log datagrid
     */
    logsDataSource: function (options, callback) {
        var columns = {
            'gadget': {
                'label': this.gadget.defines.LANGUAGE.gadget,
                'property': 'gadget'
            },
            'action': {
                'label': this.gadget.defines.LANGUAGE.action,
                'property': 'action'
            },
            'auth': {
                'label': this.gadget.defines.LANGUAGE.auth,
                'property': 'auth'
            },
            'username': {
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username'
            },
            'time': {
                'label': this.gadget.defines.LANGUAGE.time,
                'property': 'time'
            }
        };

        var filters = $.unserialize($('#logs-grid .datagrid-filters form').serialize());
        filters.user = $('#filter_user').combobox('selectedItem').value === undefined ? 0 :
            $('#filter_user').combobox('selectedItem').value;

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetLogs', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': filters
            },
            function(response, status, callOptions) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
                    options.offset = options.pageIndex*options.pageSize;
                    options.end = options.offset + options.pageSize;
                    options.end = (options.end > response['data'].total)? response['data'].total : options.end;
                    dataSource = {
                        'page': options.pageIndex,
                        'pages': Math.ceil(response['data'].total/options.pageSize),
                        'count': response['data'].total,
                        'start': options.offset + 1,
                        'end':   options.end,
                        'columns': columns,
                        'items': response['data'].records
                    };
                } else {
                    dataSource = {
                        'page': 0,
                        'pages': 0,
                        'count': 0,
                        'start': 0,
                        'end':   0,
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
     * logs Datagrid column renderer
     */
    logsDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'gadget':
                customMarkup = this.gadget.defines.gadgetList[rowData.gadget];
                break;
            default:
                customMarkup = helpers.item.text();
                break;
        }

        helpers.item.html(customMarkup);
        callback();
    },

    /**
     * initiate Logs dataGrid
     */
    initiateLogsDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'view',
                    html: '<span class="glyphicon glyphicon-eye-open"></span> ' + this.gadget.defines.LANGUAGE.view,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.viewLog(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        var ids = [];
                        if (helpers.length > 1) {
                            helpers.forEach(function(entry) {
                                ids.push(entry.rowData.id);
                            });
                        } else {
                            ids.push(helpers.rowData.id);
                        }

                        this.deleteLogs('selected', ids);
                        callback();
                    }, this)

                },
            ]
        };

        // initialize the repeater
        $('#logs-grid').repeater({
            dataSource: $.proxy(this.logsDataSource, this),
            list_actions: list_actions,
            list_columnRendered: $.proxy(this.logsDGColumnRenderer, this),
            list_selectable: 'multi',
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#logs-grid select").change(function () {
            $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
        $("#logs-grid input").keypress(function (e) {
            if (e.which == 13) {
                $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        });
        $("#logs-grid button.btn-refresh").on('click', function (e) {
            $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * view a log details
     *
     */
    viewLog: function (id) {
        this.ajax.callAsync('GetLog',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var logInfo = response.data;
                    if (logInfo) {
                        $('#log-form span').each(
                            $.proxy(function (key, elem) {
                                $(elem).html(response.data[$(elem).data('field')]);
                            }, this)
                        );

                        $('#logModal').modal('show');
                    }
                }
            }
        );
    },

    /**
     * export logs
     */
    exportLogs: function () {
        var filters = $.unserialize($('#logs-grid .datagrid-filters form').serialize());
        filters.user = $('#filter_user').combobox('selectedItem').value === undefined ? 0 :
            $('#filter_user').combobox('selectedItem').value;
        window.location = this.gadget.ajax.baseScript + '?reqGadget=Logs&reqAction=ExportLogs&' + $.param(filters);
    },

    /**
     * delete logs
     */
    deleteLogs: function (type, ids = []) {
        if (!confirm(this.gadget.defines.confirmLogsDelete)) {
            return false;
        }

        var params = {'ids': [], 'filters': []};
        if (type === 'filtered') {
            params.filters = $.unserialize($('#logs-grid .datagrid-filters form').serialize());
            params.filters.user = $('#filter_user').combobox('selectedItem').value === undefined ? 0 :
                $('#filter_user').combobox('selectedItem').value;
        } else {
            params.ids = ids;
        }
        this.ajax.callAsync('DeleteLogs', params);
    },

    /**
     * save properties
     */
    saveSettings: function () {
        this.ajax.callAsync(
            'SaveSettings', {
                'log_priority_level': $('#priority').val(),
                'log_parameters': $('#log_parameters').val()
            }
        );
    },

    /**
     * Search users and fill combo
     */
    searchUsersAndFillCombo: function (comboElm) {
        Jaws_Gadget.getInstance('Users').gadget.ajax.callAsync(
            'GetUsers',
            {'filters': {'filter_term': $(comboElm).find('>input').val()}, 'limit': 10},
            $.proxy(function (response, status) {
                $(comboElm).find('div.input-group-btn ul.dropdown-menu').html('');
                if (response['type'] == 'alert-success' && response.data.total > 0) {
                    $.each(response.data.records, $.proxy(function (key, user) {
                        this.addOptionToCombo(comboElm, {'value': user.id, 'title': user.nickname});
                    }, this));
                }
            }, this)
        );
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        switch (mainAction) {
            case 'Logs':
                $('#gadgets_filter').selectedIndex = 0;

                $('#filter_user').combobox({
                    'showOptionsOnKeypress': true,
                    'noMatchesMessage': this.gadget.defines.noMatchesMessage
                }).combobox('enable')
                    .find('>input').val('');
                $("#filter_user").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                    this.searchUsersAndFillCombo($('#filter_user'));
                }, this)).on('changed.fu.combobox', $.proxy(function (evt, data) {
                    if (data.value !== undefined) {
                        $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                    }
                }, this)).trigger('keyup.fu.combobox');

                $('button.btn-cancel-select-group').on('click', $.proxy(function (e) {
                    var cmbName = $(e.target).data('combo-name');
                    if ($(e.target).is("span")) {
                        cmbName = $(e.target).parent().data('combo-name');
                    }
                    this.cancelSelectCombobox($('#' + cmbName));
                    $('#logs-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }, this));


                this.initiateLogsDG();

                $('#btn-delete-filtered-logs').on('click', $.proxy(function (e) {
                    this.deleteLogs('filtered');
                }, this));
                $('#btn-export-filtered-logs').on('click', $.proxy(function (e) {
                    this.exportLogs();
                }, this));

                break;
            case 'Settings':
                $('#btnUpdateSettings').on('click', $.proxy(function (e) {
                    this.saveSettings();
                }, this));
                break;
        }
    },

}};