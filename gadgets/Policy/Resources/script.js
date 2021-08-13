/**
 * Policy Javascript actions
 *
 * @category   Ajax
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_Policy() { return {
    //Which action are we running?
    currentAction : null,

    //Which row selected in DataGrid
    selectedRow : null,
    selectedRowColor : null,

    selectedZone : 0,
    selectedZoneAction : 0,
    selectedZoneRange : 0,
    zoneRangesDataGrid : null,

    // ASync callback method
    AjaxCallback: {
        InsertZone: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zoneModal').modal('hide');
                $('#zones-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        UpdateZone: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zoneModal').modal('hide');
                $('#zones-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        DeleteZone: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zones-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        InsertZoneRange: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zone-ranges-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                $('#from').val('');
                $('#to').val('');
            }
        },
        UpdateZoneRange: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zone-ranges-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                $('#from').val('');
                $('#to').val('');
            }
        },
        DeleteZoneRange: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zone-ranges-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        InsertZoneAction: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zoneActionModal').modal('hide');
                $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        UpdateZoneAction: function(response) {
            if (response['type'] == 'alert-success') {
                $('#zoneActionModal').modal('hide');
                $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },
        DeleteZoneAction: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopAction();
                $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AddIPRange: function(response) {
            if (response['type'] == 'alert-success') {
                $('#blocked_ips_datagrid')[0].addItem();
                $('#blocked_ips_datagrid')[0].setCurrentPage(0);
                getDG();
                this.stopAction();
            }
        },

        EditIPRange: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopAction();
                getDG();
            }
        },

        DeleteIPRange: function(response) {
            if (response['type'] == 'alert-success') {
                $('#blocked_ips_datagrid')[0].deleteItem();
                getDG();
            }
        },

        AddAgent: function(response) {
            if (response['type'] == 'alert-success') {
                $('#blocked_agents_datagrid')[0].addItem();
                $('#blocked_agents_datagrid')[0].setCurrentPage(0);
                getDG();
                this.stopAction();
            }
        },

        EditAgent: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopAction();
                getDG();
            }
        },

        DeleteAgent: function(response) {
            if (response['type'] == 'alert-success') {
                $('#blocked_agents_datagrid')[0].deleteItem();
                getDG();
            }
        },

        IPBlockingBlockUndefined: function(response) {
            //
        },

        AgentBlockingBlockUndefined: function(response) {
            //
        },

        UpdateEncryptionSettings: function(response) {
            //
        },

        UpdateAntiSpamSettings: function(response) {
            //
        },

        UpdateAdvancedPolicies: function (response) {
            //
        }

    },

    /**
     * Select DataGrid row
     *
     */
    selectDataGridRow: function(rowElement)
    {
        if (this.selectedRow) {
            this.selectedRow.style.backgroundColor = this.selectedRowColor;
        }
        this.selectedRowColor = rowElement.style.backgroundColor;
        rowElement.style.backgroundColor = '#ffffcc';
        this.selectedRow = rowElement;
    },

    /**
     * Unselect DataGrid row
     *
     */
    unselectDataGridRow: function()
    {
        if (this.selectedRow) {
            this.selectedRow.style.backgroundColor = this.selectedRowColor;
        }
        this.selectedRow = null;
        this.selectedRowColor = null;
    },

    toggleCaptcha: function(field)
    {
        if ($('#' + field + '_captcha').val() == 'DISABLED') {
            $('#' + field + '_captcha_driver').prop('disabled', true);
        } else {
            $('#' + field + '_captcha_driver').prop('disabled', false);
        }
    },

    /**
     * Add/Edit Blocked a IP Range
     */
    saveIPRange: function()
    {
        if (!$('#from_ipaddress').val()) {
            alert(this.gadget.defines.incompleteFields);
            return false;
        }

        if ($('#id').val() == 0) {
            this.gadget.ajax.callAsync(
                'AddIPRange', [
                    $('#from_ipaddress').val(),
                    $('#to_ipaddress').val(),
                    $('#script').val(),
                    $('#order').val(),
                    $('#blocked').val()
                ]
            );
        } else {
            this.gadget.ajax.callAsync(
                'EditIPRange', [
                    $('#id').val(),
                    $('#from_ipaddress').val(),
                    $('#to_ipaddress').val(),
                    $('#script').val(),
                    $('#order').val(),
                    $('#blocked').val()
                ]
            );
        }
    },

    /**
     * Edit an IP range
     *
     */
    editIPRange: function(element, id)
    {
        this.currentAction = 'IPBlocking';
        this.selectDataGridRow($(element).parent().parent()[0]);
        var ipRange = this.gadget.ajax.callSync('GetIPRange', id);

        $('#id').val(ipRange['id']);
        $('#from_ipaddress').val(ipRange['from_ip']);
        $('#to_ipaddress').val(ipRange['to_ip']);
        $('#script').val(ipRange['script']? ipRange['script'] : '');
        $('#order').val(ipRange['order']);
        $('#blocked').prop('selectedIndex', ipRange['blocked']? 1 : 0);
    },

    /**
     * Delete an IP range
     */
    deleteIPRange: function(element, id)
    {
        this.stopAction();
        this.selectDataGridRow($(element).parent().parent()[0]);
        var answer = confirm(this.gadget.defines.confirmIPRangeDelete);
        if (answer) {
            this.gadget.ajax.callAsync('DeleteIPRange', id);
        }
        this.unselectDataGridRow();
    },

    /**
     * Add/Edit Blocked Agent
     */
    saveAgent: function()
    {
        if (!$('#agent').val()) {
            alert(this.gadget.defines.incompleteFields);
            return false;
        }

        if ($('#id').val() == 0) {
            this.gadget.ajax.callAsync('AddAgent', [$('#agent').val(), $('#script').val(), $('#blocked').val()]);
        } else {
            this.gadget.ajax.callAsync(
                'EditAgent',
                [$('#id').val(), $('#agent').val(), $('#script').val(), $('#blocked').val()]
            );
        }
    },

    /**
     * Edit a Agent
     *
     */
    editAgent: function(element, id)
    {
        this.currentAction = 'AgentBlocking';
        this.selectDataGridRow($(element).parent().parent()[0]);
        var agent = this.gadget.ajax.callSync('GetAgent', id);

        $('#id').val(agent['id']);
        $('#agent').val(agent['agent'].defilter());
        $('#script').val(agent['script']? agent['script'] : '');
        $('#blocked').prop('selectedIndex', agent['blocked']? 1 : 0);
    },

    /**
     * Delete an Agent
     */
    deleteAgent: function(element, id)
    {
        this.stopAction();
        this.selectDataGridRow($(element).parent().parent()[0]);
        var answer = confirm(this.gadget.defines.confirmAgentDelete);
        if (answer) {
            this.gadget.ajax.callAsync('DeleteAgent', id);
        }
        this.unselectDataGridRow();
    },

    /**
     * setIPBlockAnonymous
     */
    setBlockUndefinedIP: function()
    {
        try {
            this.gadget.ajax.callAsync('IPBlockingBlockUndefined', $('#block_undefined_ip').prop('checked'));
        } catch(e) {
            alert(e);
        }
    },

    /**
     * setAgentBlockUndefined
     */
    setBlockUndefinedAgent: function()
    {
        try {
            this.gadget.ajax.callAsync(
                'AgentBlockingBlockUndefined',
                $('#block_undefined_agent').prop('checked')
            );
        } catch(e) {
            alert(e);
        }
    },

    /**
     * save encryption settings
     */
    saveEncryptionSettings: function()
    {
        try {
            this.gadget.ajax.callAsync(
                'UpdateEncryptionSettings', [
                    $('#enabled').val(),
                    $('#key_age').val(),
                    $('#key_len').val()
                ]
            );
        } catch(e) {
            alert(e);
        }
    },

    /**
     * save AntiSpam settings
     */
    saveAntiSpamSettings: function()
    {
        try {
            this.gadget.ajax.callAsync(
                'UpdateAntiSpamSettings', [
                    $('#filter').val(),
                    $('#default_captcha').val(),
                    $('#default_captcha_driver').val(),
                    $('#obfuscator').val(),
                    $('#blocked_domains').val()
                ]
            );
        } catch(e) {
            alert(e);
        }
    },

    /**
     * save Advanced Policies
     */
    saveAdvancedPolicies: function()
    {
        try {
            this.gadget.ajax.callAsync(
                'UpdateAdvancedPolicies', [
                    $('#password_complexity').val(),
                    $('#password_bad_count').val(),
                    $('#password_lockedout_time').val(),
                    $('#password_max_age').val(),
                    $('#password_min_length').val(),
                    $('#zonein_captcha').val(),
                    $('#zonein_captcha_driver').val(),
                    $('#xss_parsing_level').val(),
                    $('#session_online_timeout').val(),
                    $('#session_anony_remember_timeout').val(),
                    $('#session_zonein_remember_timeout').val()
                ]
            );
        } catch(e) {
            alert(e);
        }
    },

    /**
     * Submit the form
     */
    submitForm: function(form)
    {
        switch (form.elements['action'].value) {
            case 'AddIPBand':
                this.addIPBand(form);
                break;
            case 'AddAgent':
                this.addAgent(form);
                break;
            case 'UpdateProperties':
                this.updateProperties(form);
                break;
            default:
                break;
        }
    },

    /**
     * Clean the form
     */
    stopAction: function()
    {
        switch (this.currentAction) {
            case 'Zones':
                this.selectedZone = 0;
                this.selectedZoneRange = 0;
                $('#zoneModal .modal-title').html(this.gadget.defines.LANGUAGE.addNewZone);
                $('#title').val('');
                $('#from').val('');
                $('#to').val('');
                break;
            case 'ZoneActions':
                this.selectedZoneAction = 0;
                $('#zoneActionModal .modal-title').html(this.gadget.defines.LANGUAGE.addNewZoneAction);
                $('#zone-action-form')[0].reset();
                break;
            case 'IPBlocking':
                $('#id').val(0);
                $('#from_ipaddress').val('');
                $('#to_ipaddress').val('');
                $('#script').val('index');
                $('#order').val('');
                this.unselectDataGridRow();
                break;
            case 'AgentBlocking':
                $('#id').val(0);
                $('#agent').val('');
                $('#script').val('index');
                this.unselectDataGridRow();
                break;
            default:
                break;
        }
    },

    /**
     * Define the data to be displayed in the zone datagrid
     */
    zonesDataSource: function (options, callback) {
        var columns = {
            'title': {
                'label': this.gadget.defines.LANGUAGE.title,
                'property': 'title'
            },
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetZones', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': []
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
     * initiate Zones dataGrid
     */
    initiateZonesDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-edit"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editZone(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'manageZoneRange',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.gadget.defines.LANGUAGE.editZoneRange,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.manageZoneRange(helpers.rowData.id);
                        callback();
                    }, this)

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteZone(helpers.rowData.id);
                        callback();
                    }, this)

                },
            ]
        };

        // initialize the repeater
        $('#zones-grid').repeater({
            dataSource: $.proxy(this.zonesDataSource, this),
            list_actions: list_actions,
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#zones-grid button.btn-refresh").on('click', function (e) {
            $('#zones-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * Edit a zone
     */
    editZone: function (id) {
        this.selectedZone = id;
        this.ajax.callAsync('GetZone',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var zoneInfo = response.data;
                    $('#zoneModal .modal-title').html(this.gadget.defines.LANGUAGE.editZone);
                    if (zoneInfo) {
                        $('#title').val(zoneInfo.title);
                        $('#zoneModal').modal('show');
                    }
                }
            }
        );
    },

    /**
     * Manage zone range
     */
    manageZoneRange: function (id) {
        this.selectedZone = id;
        $('#zoneRangeModal').modal('show');
    },

    /**
     * Edit zone range
     */
    editZoneRange: function (id) {
        this.selectedZoneRange = id;
        this.ajax.callAsync('GetZoneRange',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var zoneRangeInfo = response.data;
                    if (zoneRangeInfo) {
                        $('#from').val(zoneRangeInfo.from);
                        $('#to').val(zoneRangeInfo.to);
                    }
                }
            }
        );
    },

    /**
     * Save a zone info
     */
    saveZone: function () {
        var data = {'title': $('#title').val()};
        if (this.selectedZone === 0) {
            this.ajax.callAsync('InsertZone', {'data': data});
        } else {
            this.ajax.callAsync('UpdateZone', {'id': this.selectedZone, 'data': data});
        }
    },

    /**
     * Save a zone range info
     */
    saveZoneRange: function () {
        var data = {'zone': this.selectedZone, 'from': $('#from').val(), 'to': $('#to').val()};
        if (this.selectedZoneRange === 0) {
            this.ajax.callAsync('InsertZoneRange', {'data': data});
        } else {
            this.ajax.callAsync('UpdateZoneRange', {'id': this.selectedZoneRange, 'data': data});
        }
    },

    /**
     * delete a zone
     */
    deleteZone: function (id) {
        if (!confirm(this.gadget.defines.confirmDelete)) {
            return false;
        }

        this.ajax.callAsync('DeleteZone', {'id': id});
    },

    /**
     * delete a zone range
     */
    deleteZoneRange: function (rangeId) {
        if (!confirm(this.gadget.defines.confirmDelete)) {
            return false;
        }

        this.ajax.callAsync('DeleteZoneRange', {'id': rangeId});
    },

    /**
     * Define the data to be displayed in the zone datagrid
     */
    zoneRangesDataSource: function (options, callback) {
        var columns = {
            'from': {
                'label': this.gadget.defines.LANGUAGE.from,
                'property': 'from'
            },
            'to': {
                'label': this.gadget.defines.LANGUAGE.to,
                'property': 'to'
            },
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetZoneRanges', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': {'zone':this.selectedZone}
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
     * initiate ZoneRanges dataGrid
     */
    initiateZoneRangesDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-edit"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editZoneRange(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteZoneRange(helpers.rowData.id);
                        callback();
                    }, this)

                },
            ]
        };

        // initialize the repeater
        this.zoneRangesDataGrid = $('#zone-ranges-grid').repeater({
            dataSource: $.proxy(this.zoneRangesDataSource, this),
            list_actions: list_actions,
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#zone-ranges-grid button.btn-refresh").on('click', function (e) {
            $('#zone-ranges-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * Edit a zone action
     */
    editZoneAction: function (id) {
        this.selectedZoneAction = id;
        this.ajax.callAsync('GetZoneAction',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var zoneActionInfo = response.data;
                    if (zoneActionInfo) {
                        $('#zoneActionModal .modal-title').html(this.gadget.defines.LANGUAGE.editZoneAction);

                        $('#zone-action-form').find('input, select, textarea').each(
                            function (i, el) {
                                value = zoneActionInfo[$(el).attr('name')];
                                switch (typeof(value)) {
                                    case 'boolean':
                                        value = value? '1' : '0'
                                        break;
                                    default:
                                    // do nothing
                                }

                                $(el).val(value);

                                if ($(el).is("select")) {
                                    $(el).trigger('change');
                                }
                            }
                        );

                        $('#zoneActionModal').modal('show');
                    }
                }
            }
        );
    },

    /**
     * delete a zone action
     */
    deleteZoneAction: function (id) {
        if (!confirm(this.gadget.defines.confirmDelete)) {
            return false;
        }

        this.ajax.callAsync('DeleteZoneAction', {'id': id});
    },

    /**
     * Save a zone action info
     */
    saveZoneAction: function () {
        var data = $.unserialize($('#zone-action-form').serialize());
        if (this.selectedZoneAction === 0) {
            this.ajax.callAsync('InsertZoneAction', {'data': data});
        } else {
            this.ajax.callAsync('UpdateZoneAction', {'id': this.selectedZoneAction, 'data': data});
        }
    },

    /**
     * change script combo
     */
    changeScript: function (gadget) {
        $("select#gadget").prop('selectedIndex', 0);
        $('#action').html('').append('<option value="" > </option>');
    },


    /**
     * change gadget combo
     */
    changeGadget: function (gadget) {
        var elem = $('#action');
        $(elem).html('');

        this.ajax.callAsync('GetGadgetActions', {'gadget': gadget}, function (response, status, callOptions) {
            if (response['type'] == 'alert-success') {
                callOptions.showMessage = false;
                var actions = {};

                switch ($('#script').val()) {
                    case "0":
                        actions = {...response.data.index, ...response.data.admin};
                        break;
                    case "1":
                        actions = response.data.index;
                        break;
                    case "2":
                        actions = response.data.admin;
                        break;

                }

                $.each(actions, function (index, action) {
                    $(elem).append('<option value="' + index + '" >' + index + '</option>');
                });
            }

        });
    },

    /**
     * Define the data to be displayed in the zone datagrid
     */
    zoneActionsDataSource: function (options, callback) {
        var columns = {
            'zone_title': {
                'label': this.gadget.defines.LANGUAGE.zone,
                'property': 'zone_title'
            },
            'gadget': {
                'label': this.gadget.defines.LANGUAGE.gadget,
                'property': 'gadget'
            },
            'action': {
                'label': this.gadget.defines.LANGUAGE.action,
                'property': 'action'
            },
            'order': {
                'label': this.gadget.defines.LANGUAGE.order,
                'property': 'order'
            },
            'access': {
                'label': this.gadget.defines.LANGUAGE.access,
                'property': 'access'
            },
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetZoneActions', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': $.unserialize($('#zone-actions-grid .datagrid-filters form').serialize())
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
     * zoneActions Datagrid column renderer
     */
    zoneActionsDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'gadget':
                customMarkup = this.gadget.defines.gadgetList[rowData.gadget];
                break;
            case 'access':
                customMarkup = (rowData.access === true) ?
                    this.gadget.defines.LANGUAGE.yes : this.gadget.defines.LANGUAGE.no;
                break;
            default:
                customMarkup = helpers.item.text();
                break;
        }

        helpers.item.html(customMarkup);
        callback();
    },

    /**
     * initiate ZoneActions dataGrid
     */
    initiateZoneActionsDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-edit"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editZoneAction(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteZoneAction(helpers.rowData.id);
                        callback();
                    }, this)

                },
            ]
        };

        // initialize the repeater
        $('#zone-actions-grid').repeater({
            dataSource: $.proxy(this.zoneActionsDataSource, this),
            list_actions: list_actions,
            list_columnRendered: $.proxy(this.zoneActionsDGColumnRenderer, this),
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#zone-actions-grid select").change(function () {
            $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
        $("#zone-actions-grid input").keypress(function (e) {
            if (e.which == 13) {
                $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        });
        $("#zone-actions-grid button.btn-refresh").on('click', function (e) {
            $('#zone-actions-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        switch (mainAction) {
            case 'Zones':
                this.currentAction = 'Zones';
                this.initiateZonesDG()

                $('#btnSaveZone').on('click', $.proxy(function (e) {
                    this.saveZone();
                }, this));

                $('#btnSaveZoneRange').on('click', $.proxy(function (e) {
                    this.saveZoneRange();
                }, this));

                $('#zoneModal').on('hidden.bs.modal', $.proxy(function (e) {
                    this.stopAction();
                }, this));

                $('#zoneRangeModal').on('shown.bs.modal', $.proxy(function (e) {
                    if (this.zoneRangesDataGrid ===null) {
                        this.initiateZoneRangesDG();
                    } else {
                        $('#zone-ranges-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                    }
                }, this));

                break;

            case 'ZoneActions':
                this.currentAction = 'ZoneActions';
                this.initiateZoneActionsDG()

                $('#script').on('change', $.proxy(function (e) {
                    this.changeScript($('#gadget').val());
                }, this));
                $('#gadget').on('change', $.proxy(function (e) {
                    this.changeGadget($('#gadget').val());
                }, this));

                $('#btnSaveZoneAction').on('click', $.proxy(function (e) {
                    this.saveZoneAction();
                }, this));

                $('#zoneActionModal').on('hidden.bs.modal', $.proxy(function (e) {
                    this.stopAction();
                }, this));

                break;

            case 'IPBlocking':
                this.currentAction = 'IPBlocking';
                initDataGrid('blocked_ips_datagrid', this.gadget.ajax);
                break;

            case 'AgentBlocking':
                this.currentAction = 'AgentBlocking';
                initDataGrid('blocked_agents_datagrid', this.gadget.ajax);
                break;

            case 'Encryption':
                this.currentAction = 'Encryption';
                break;

            case 'AntiSpam':
                this.currentAction = 'AntiSpam';
                break;

            case 'AdvancedPolicies':
                this.currentAction = 'AdvancedPolicies';
                break;
        }
    },

}};
