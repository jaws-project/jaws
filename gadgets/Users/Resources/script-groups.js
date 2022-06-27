/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users_Action_Groups() {
    return {
        selectedGroup : 0,

        // ASync callback method
        AjaxCallback: {
            DeleteUsersFromGroup: function(response) {
                if (response.type === 'alert-success') {
                    $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            AddGroup: function(response) {
                if (response.type === 'alert-success') {
                    this.stopGroupAction();
                    $('#groupModal').modal('hide');
                    $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdateGroup: function(response) {
                if (response.type === 'alert-success') {
                    this.stopGroupAction();
                    $('#groupModal').modal('hide');
                    $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdateGroupACL: function(response) {
                if (response.type === 'alert-success') {
                    $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            DeleteGroupACLs: function(response) {
                if (response.type === 'alert-success') {
                    $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            DeleteGroups: function(response) {
                if (response.type === 'alert-success') {
                    $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },
        },

        /**
         * Delete group(s)
         */
        deleteGroups: function(gids) {
            if (confirm(this.t('user_confirm_delete'))) {
                this.ajax.callAsync('DeleteGroups', {'gids': gids});
            }
        },

        /**
         * Delete users from group
         */
        deleteUsersFromGroup: function (userIds) {
            this.ajax.callAsync('DeleteUsersFromGroup', {'gid': this.selectedGroup, 'userIds': userIds});
        },

        /**
         * Edit group
         */
        editGroup: function(gid) {
            this.selectedGroup = gid;
            $('#groupModal .modal-title').html(this.t('groups_edit'));

            this.ajax.callAsync('GetGroup', {
                    'gid': this.selectedGroup
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        callOptions.showMessage = false;
                        var gInfo = response.data;
                        if (gInfo) {
                            $('#name').val(gInfo.name);
                            $('#title').val(gInfo.title.defilter());
                            $('#email').val(gInfo.email);
                            $('#mobile').val(gInfo.mobile);
                            $('#description').val(gInfo.description === null ? '' : gInfo.description.defilter());
                            $('#department').val(Number(gInfo.department));
                            $('#removable').val(Number(gInfo.removable));
                            $('#enabled').val(Number(gInfo.enabled));

                            $('#groupModal').modal('show');
                        }
                    }
                }
            );
        },

        /**
         * Edit the members of group
         */
        editGroupUsers: function(gid) {
            this.selectedGroup = gid;
            $('#groupUsersModal').modal('show');
        },

        /**
         * Saves data / changes on the group's form
         */
        saveGroup: function() {
            if (!$('#name').val() || !$('#title').val()) {
                alert(this.t('myaccount_incomplete_fields'));
                return false;
            }

            if (this.selectedGroup == 0) {
                this.ajax.callAsync(
                    'AddGroup', {
                        'data': $.unserialize($('#group-form input,#group-form select,#group-form textarea').serialize())
                    });
            } else {
                this.ajax.callAsync(
                    'UpdateGroup', {
                        'gid': this.selectedGroup,
                        'data': $.unserialize($('#group-form input,#group-form select,#group-form textarea').serialize())
                    });
            }
        },

        /**
         * Save group's ACL
         */
        saveGroupACL: function() {
            if ($('#components').val() === '-1') {
                return;
            }
            var acls = [];
            $.each($('#acl_form img[alt!="-1"]'), function (index, aclTag) {
                var keys = $(aclTag).attr('id').split(':');
                acls[index] = [keys[0], keys[1], $(aclTag).attr('alt')];
            });
            this.ajax.callAsync('UpdateGroupACL', {
                'gid': this.selectedGroup,
                'component': $('#components').val(),
                'acls': acls
            });
        },

        /**
         * Edit group ACL
         */
        editGroupACL: function (uid) {
            this.selectedGroup = uid;
            $('#aclModal').modal('show');

            this.gadget.chkImages = $('#aclModal .acl-images img').map(function() {
                return $(this).attr('src');
            }).toArray();
            this.gadget.chkImages[-1] = this.gadget.chkImages[2];
            delete this.gadget.chkImages[2];
        },

        /**
         * Delete group's ACL
         */
        deleteGroupACLs: function(acls) {
            this.ajax.callAsync('DeleteGroupACLs', {
                'gid': this.selectedGroup,
                'acls': acls
            });
        },

        /**
         * Stops doing a certain action
         */
        stopGroupAction: function() {
            this.selectedGroup = 0;
            this.gadget.cancelSelectCombobox($('#user_combo'));
            $('form#group-form')[0].reset();
            $('#groupModal .modal-title').html(this.t('groups_add'));
        },

        /**
         * Define the data to be displayed in the groups datagrid
         */
        groupsDataSource: function (options, callback) {
            var columns = {
                'name': {
                    'label': Jaws.t('name'),
                    'property': 'name',
                    'width': '30%'
                },
                'title': {
                    'label': Jaws.t('title'),
                    'property': 'title',
                    'width': '55%'
                },
                'enabled': {
                    'label': Jaws.t('enabled'),
                    'property': 'enabled',
                    'width': '15%'
                },
            };

            var filters = $.unserialize($('#groups-grid .datagrid-filters form').serialize());

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.ajax.callAsync(
                'GetGroups', {
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
                            'count': Intl.NumberFormat().format(response['data'].total),
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
         * groups Datagrid column renderer
         */
        groupsDGColumnRenderer: function (helpers, callback) {
            var column = helpers.columnAttr;
            var rowData = helpers.rowData;
            var customMarkup = '';

            switch (column) {
                case 'enabled':
                    if (helpers.item.text() == "true") {
                        customMarkup = Jaws.t('yess');
                    } else {
                        customMarkup = Jaws.t('noo');
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
         * initiate Groups dataGrid
         */
        initiateGroupsDG: function() {
            var list_actions = {
                width: 50,
                items: [
                    {
                        name: 'edit',
                        html: '<span class="glyphicon glyphicon-pencil"></span> ' + Jaws.t('edit'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();

                            this.editGroup(helpers.rowData.id);
                            callback();
                        }, this)

                    },
                    {
                        name: 'acl',
                        html: '<span class="glyphicon glyphicon-lock"></span> ' + this.t('acls'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editGroupACL(helpers.rowData.id);
                            callback();
                        }, this)

                    },
                    {
                        name: 'group_members',
                        html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('groups_members'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editGroupUsers(helpers.rowData.id);
                            callback();
                        }, this)

                    },
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

                            this.deleteGroups(ids);
                            callback();
                        }, this)

                    },
                ]
            };

            // initialize the repeater
            $('#groups-grid').repeater({
                dataSource: $.proxy(this.groupsDataSource, this),
                list_actions: list_actions,
                list_columnRendered: $.proxy(this.groupsDGColumnRenderer, this),
                list_selectable: 'multi',
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#groups-grid input").keypress(function (e) {
                if (e.which == 13) {
                    $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            });
            $("#groups-grid button.btn-refresh").on('click', function (e) {
                $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        /**
         * Define the data to be displayed in the user ACLs datagrid
         */
        groupACLsDataSource: function (options, callback) {
            var columns = {
                'component': {
                    'label': this.t('acls_components'),
                    'property': 'component_title',
                    'width': '30%'
                },
                'acl_key_title': {
                    'label': this.t('acls_key_title'),
                    'property': 'key_title',
                    'width': '50%'
                },
                'acl': {
                    'label': this.t('acl'),
                    'property': 'key_value',
                    'width': '20%'
                },
            };

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.ajax.callAsync(
                'GetObjectACLs', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': {'id': this.selectedGroup, 'action':'Group'}
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
                            'count': Intl.NumberFormat().format(response['data'].total),
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
         * groupACLs Datagrid column renderer
         */
        groupACLsDGRowRenderer: function (helpers, callback) {
            switch (helpers.rowData.key_value) {
                case 0:
                    helpers.item.addClass('bg-danger');
                    break;
                case 1:
                    helpers.item.addClass('bg-success');
                    break;
            }

            callback();
        },

        /**
         * groupAcls Datagrid column renderer
         */
        groupACLsDGColumnRenderer: function (helpers, callback) {
            var column = helpers.columnAttr;
            var rowData = helpers.rowData;
            var customMarkup = '';

            switch (column) {
                case 'key_value':
                    if (rowData.key_value == 0) {
                        customMarkup = this.t('acls_access_no');
                    } else if (rowData.key_value == 1) {
                        customMarkup = this.t('acls_access_yes');
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
         * initiate User ACLs dataGrid
         */
        initiateGroupACLsDG: function() {
            if ($('#item-acls-grid').data('currentview') !== undefined) {
                return $('#item-acls-grid').repeater('render', {clearInfinite: true,pageIncrement: null});
            }

            var list_actions = {
                width: 50,
                items: [
                    {
                        name: 'delete',
                        html: '<span class="glyphicon glyphicon-trash"></span> ' + Jaws.t('delete'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();

                            var acls = new Array();
                            if (helpers.length > 1) {
                                helpers.forEach(function(entry) {
                                    acls.push({
                                        'component': entry.rowData.component,
                                        'key_name': entry.rowData.key_name,
                                        'subkey': entry.rowData.subkey,
                                    });
                                });
                            } else {
                                acls.push({
                                    'component': helpers.rowData.component,
                                    'key_name': helpers.rowData.key_name,
                                    'subkey': helpers.rowData.subkey,
                                });
                            }

                            this.deleteGroupACLs(acls);
                            callback();
                        }, this)

                    },
                ]
            };

            // initialize the repeater
            $('#item-acls-grid').repeater({
                dataSource: $.proxy(this.groupACLsDataSource, this),
                list_actions: list_actions,
                list_infiniteScroll: true,
                list_columnRendered: $.proxy(this.groupACLsDGColumnRenderer, this),
                list_rowRendered: $.proxy(this.groupACLsDGRowRenderer, this),
                list_selectable: 'multi',
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#item-acls-grid button.btn-refresh").on('click', function (e) {
                $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        /**
         * Define the data to be displayed in the group's users datagrid
         */
        groupUsersDataSource: function (options, callback) {
            var columns = {
                'username': {
                    'label': this.t('users_username'),
                    'property': 'username'
                },
                'nickname': {
                    'label': this.t('users_nickname'),
                    'property': 'nickname'
                }
            };

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.ajax.callAsync(
                'GetGroupUsers', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': {'gid': this.selectedGroup}
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
                            'count': Intl.NumberFormat().format(response['data'].total),
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
         * initiate Group's users dataGrid
         */
        initiateGroupUsersDG: function() {
            if ($('#group-users-grid').data('currentview') !== undefined) {
                return $('#group-users-grid').repeater('render', {clearInfinite: true,pageIncrement: null});
            }

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

                            this.deleteUsersFromGroup(ids);
                            callback();
                        }, this)

                    }
                ]
            };

            // initialize the repeater
            $('#group-users-grid').repeater({
                dataSource: $.proxy(this.groupUsersDataSource, this),
                list_actions: list_actions,
                list_selectable: 'multi',
                list_infiniteScroll: true,
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#group-users-grid button.btn-refresh").on('click', function (e) {
                $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            this.initiateGroupsDG();

            $('#groupModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopGroupAction();
            }, this));
            $('#aclModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopGroupAction();
                $('#components').val("-1").trigger('change');
            }, this)).on('shown.bs.modal', $.proxy(function (e) {
                this.initiateGroupACLsDG();
            }, this));

            $('#groupUsersModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopGroupAction();
            }, this)).on('shown.bs.modal', $.proxy(function (e) {
                this.initiateGroupUsersDG();
            }, this));

            $('#btnSaveGroup').on('click', $.proxy(function (e) {
                this.saveGroup();
            }, this));

            $('#btnSaveACLs').on('click', $.proxy(function (e) {
                this.saveGroupACL();
            }, this));

            $('#components').on('change', $.proxy(function (e) {
                this.gadget.getACL(this.selectedGroup, 'GroupACL');
            }, this));

            $('#btnAddUserToGroup').on('click', $.proxy(function (e) {
                var uid = $('#user_combo').combobox('selectedItem').value;
                if ( uid === undefined) {
                    return false;
                }
                this.gadget.addUserToGroup(uid, this.selectedGroup);
            }, this));

            $('button.btn-cancel-select-user').on('click', $.proxy(function (e) {
                var cmbName = $(e.target).data('combo-name');
                if ($(e.target).is("span")) {
                    cmbName = $(e.target).parent().data('combo-name');
                }
                this.gadget.cancelSelectCombobox($('#' + cmbName));
            }, this));

            // initiate #user_combo
            $('#user_combo').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': Jaws.t('combo_no_match_message')
            }).combobox('enable')
                .find('>input').val('');
            $("#user_combo").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                clearTimeout(this.gadget.searchTimer);
                this.gadget.searchTimer = setTimeout($.proxy(function() {
                    this.gadget.searchUsersAndFillCombo($('#user_combo'));
                }, this), 800);// milliseconds
            }, this)).trigger('keyup.fu.combobox');
        }
    }
};
