/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users_Action_OnlineUsers() {
    return {
        fTimeout : null,

        // ASync callback method
        AjaxCallback: {
            DeleteSessions: function(response) {
                if (response.type === 'alert-success') {
                    clearTimeout(this.fTimeout);
                    $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            IPsBlock: function(response) {
                if (response.type === 'alert-success') {
                    clearTimeout(this.fTimeout);
                    $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            AgentsBlock: function(response) {
                if (response.type === 'alert-success') {
                    clearTimeout(this.fTimeout);
                    $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            }
        },

        /**
         * Delete online users
         */
        deleteOnlineUsers: function(ids) {
            if (confirm(this.t('online_confirm_throwout'))) {
                this.gadget.ajax.callAsync('DeleteSessions', {'ids': ids});
            }
        },

        /**
         * Block online users IP address
         */
        blockOnlineUsersIP: function(ids) {
            if (confirm(this.t('online_confirm_blockip'))) {
                this.gadget.ajax.callAsync('IPsBlock', {'ids': ids});
            }
        },

        /**
         * Block online users agent
         */
        blockOnlineUsersAgent: function(ids) {
            if (confirm(this.t('online_confirm_blockagent'))) {
                this.gadget.ajax.callAsync('AgentsBlock', {'ids': ids});
            }
        },

        /**
         * Define the data to be displayed in the online-user datagrid
         */
        onlineUsersDataSource: function (options, callback) {
            var columns = {
                'username': {
                    'label': this.t('users_username'),
                    'property': 'username',
                    'width': '15%'
                },
                'nickname': {
                    'label': this.t('users_nickname'),
                    'property': 'nickname',
                    'width': '20%'
                },
                'superadmin': {
                    'label': this.t('online_admin'),
                    'property': 'superadmin',
                    'width': '15%'
                },
                'ip': {
                    'label': Jaws.t('ip'),
                    'property': 'ip',
                    'width': '15%'
                },
                'type': {
                    'label': this.t('online_session_type'),
                    'property': 'type',
                    'width': '15%'
                },
                'last_activetime': {
                    'label': this.t('online_last_activetime'),
                    'property': 'last_activetime',
                    'width': '20%'
                },
            };

            var filters = $.unserialize($('#online-users-grid .datagrid-filters form').serialize());

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.gadget.ajax.callAsync(
                'GetOnlineUsers', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': filters
                },
                function(response, status, callOptions) {
                    var dataSource = {};
                    if (response.type === 'alert-success') {
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
         * online-users Datagrid column renderer
         */
        onlineUsersDGColumnRenderer: function (helpers, callback) {
            var column = helpers.columnAttr;
            var rowData = helpers.rowData;
            var customMarkup = '';

            switch (column) {
                case 'username':
                    if (rowData.username === '') {
                        customMarkup = this.t('online_anony');
                    } else {
                        customMarkup = "<a href='" + rowData.user_profile_url + "' target='_blank'>" + rowData.username + "</a>";
                    }
                    break;
                case 'superadmin':
                    customMarkup = rowData.superadmin ? Jaws.t('yess') :  Jaws.t('noo');
                    break;
                case 'ip':
                    if (rowData.proxy!=='') {
                        customMarkup = "<abbr title='" + rowData.agent_text + "'>" + rowData.proxy + '(' + rowData.client + ")</abbr>";
                    } else {
                        customMarkup = "<abbr title='" + rowData.agent_text + "'>(" + rowData.client + ")</abbr>";
                    }
                    break;
                case 'last_activetime':
                    helpers.item.addClass('ltr');
                    if (rowData.online) {
                        customMarkup = '<span title="' + this.t('online_active') + '">' +
                            helpers.item.text() + '</span>';
                    } else {
                        customMarkup = '<s title="' + this.t('online_inactive') + '">' +
                            helpers.item.text() + '</s>';
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
         * initiate online-user dataGrid
         */
        initiateOnlineUsersDG: function() {
            var list_actions = {
                width: 50,
                items: [
                    {
                        name: 'delete',
                        html: '<span class="glyphicon glyphicon-trash"></span> ' + Jaws.t('delete'),
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

                            this.deleteOnlineUsers(ids);
                            callback();
                        }, this)

                    },
                    {
                        name: 'block_ip',
                        html: '<span class="glyphicon glyphicon-ban-circle"></span> ' + this.t('online_blocking_ip'),
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

                            this.blockOnlineUsersIP(ids);
                            callback();
                        }, this)
                    },
                    {
                        name: 'block_agent',
                        html: '<span class="glyphicon glyphicon-ban-circle"></span> ' + this.t('online_blocking_agent'),
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

                            this.blockOnlineUsersAgent(ids);
                            callback();
                        }, this)
                    },
                ]
            };

            // initialize the repeater
            $('#online-users-grid').repeater({
                dataSource: $.proxy(this.onlineUsersDataSource, this),
                list_actions: list_actions,
                list_columnRendered: $.proxy(this.onlineUsersDGColumnRenderer, this),
                list_selectable: 'multi',
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#online-users-grid select").change(function () {
                $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
            $("#online-users-grid button.btn-refresh").on('click', function (e) {
                $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });

            this.fTimeout = setTimeout(
                "$('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});",
                30000
            );
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            this.initiateOnlineUsersDG();
        }
    }
};
