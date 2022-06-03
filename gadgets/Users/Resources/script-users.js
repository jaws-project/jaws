/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users_Action_Users() {
    return {
        currentAction: null,
        selectedUser : 0,

        // ASync callback method
        AjaxCallback: {
            AddUser: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#userModal').modal('hide');
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdateUser: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#userModal').modal('hide');
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdateUserACL: function(response) {
                if (response.type === 'alert-success') {
                    $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            DeleteUserACLs: function(response) {
                if (response.type === 'alert-success') {
                    $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            DeleteUserFromGroups: function(response) {
                if (response.type === 'alert-success') {
                    $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdatePersonal: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#personalModal').modal('hide');
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },

            UpdateUserPassword: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#passwordModal').modal('hide');
                }
            },

            UpdateUserContacts: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#contactsModal').modal('hide');
                }
            },

            UpdateUserExtra: function(response) {
                if (response.type === 'alert-success') {
                    this.stopUserAction();
                    $('#extraModal').modal('hide');
                }
            },

            DeleteUsers: function(response) {
                if (response.type === 'alert-success') {
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },
        },

        /**
         * Saves users data / changes
         */
        saveUser: function() {
            if (!$('#username').val() ||
                !$('#nickname').val() ||
                (!$('#email').val() && !$('#mobile').val())
            ) {
                alert(this.t('myaccount_incomplete_fields'));
                return false;
            }

            var password = $('#password').val();
            $.loadScript('libraries/js/jsencrypt.min.js', function() {
                if ($('#pubkey').length) {
                    var objRSACrypt = new JSEncrypt();
                    objRSACrypt.setPublicKey($('#pubkey').val());
                    password = objRSACrypt.encrypt($('#password').val());
                }

                if (this.selectedUser == 0) {
                    if (!$('#password').val()) {
                        alert(this.t('myaccount_incomplete_fields'));
                        return false;
                    }

                    var formData = $.unserialize(
                        $('#users-form input, #users-form select,#users-form textarea').serialize()
                    );
                    formData['password'] = password;
                    // delete formData['modulus'];
                    // delete formData['exponent'];
                    this.gadget.ajax.callAsync('AddUser', {'data': formData});
                } else {
                    var formData = $.unserialize(
                        $('#users-form input, #users-form select, #users-form textarea').serialize()
                    );
                    formData['password'] = password;
                    delete formData['pass1'];
                    delete formData['pass2'];
                    delete formData['length'];
                    delete formData['modulus'];
                    delete formData['exponent'];
                    this.gadget.ajax.callAsync('UpdateUser', {'id':  this.selectedUser, 'data': formData});
                }
            }, this.gadget);
        },

        /**
         * Save user's personal data
         */
        saveUserPersonal: function() {
            var formData = $.unserialize($('#user-personal-form').serialize());
            delete formData['reqGadget'];
            delete formData['reqAction'];
            this.gadget.ajax.callAsync('UpdatePersonal', {'id':  this.selectedUser, 'data': formData});
        },

        /**
         * Save user's ACL
         */
        saveUserACL: function() {
            if ($('#components').val() === '') {
                return;
            }
            var acls = [];
            $.each($('#acl_form img[alt!="-1"]'), function(index, aclTag) {
                var keys = $(aclTag).attr('id').split(':');
                acls[index] = [keys[0], keys[1], $(aclTag).attr('alt')];
            });
            this.gadget.ajax.callAsync('UpdateUserACL', {
                'uid': this.selectedUser,
                'component': $('#components').val(),
                'acls': acls
            });
        },

        /**
         * Delete user(s)
         */
        deleteUsers: function (uids) {
            if (confirm(this.t('user_confirm_delete'))) {
                this.gadget.ajax.callAsync('DeleteUsers', {'uids': uids});
            }
        },

        /**
         * Delete user's ACL
         */
        deleteUserACLs: function(acls) {
            this.gadget.ajax.callAsync('DeleteUserACLs', {
                'uid': this.selectedUser,
                'acls': acls
            });
        },

        /**
         * Save user's contact
         */
        saveUserContact: function() {
            this.gadget.ajax.callAsync(
                'UpdateUserContacts', {
                    'uid': this.selectedUser,
                    'data': $.unserialize($('#user-contacts-form').serialize())
                });
        },

        /**
         * Save user's extra data
         */
        saveUserExtra: function () {
            this.gadget.ajax.callAsync(
                'UpdateUserExtra', {
                    'uid': this.selectedUser,
                    'data': $.unserialize($('#user-extra-form').serialize())
                });
        },

        /**
         * Save user's password
         */
        saveUserPassword: function() {
            var password = $('#user-password-form input[name="password"]').val();
            if (password.blank()) {
                alert(this.t('myaccount_passwords_dont_match'));
                return false;
            }

            $.loadScript('libraries/js/jsencrypt.min.js', $.proxy(function() {
                if ($('#pubkey').length) {
                    var objRSACrypt = new JSEncrypt();
                    objRSACrypt.setPublicKey($('#pubkey').val());
                    password = objRSACrypt.encrypt(password);
                }

                this.gadget.ajax.callAsync(
                    'UpdateUserPassword', {
                        'uid': this.selectedUser,
                        'password': password,
                        'expired': $('#user-password-form #expired').prop('checked')
                    });
            }, this));
        },

        /**
         * Edit user
         */
        editUser: function(uid) {
            this.selectedUser = uid;

            this.ajax.callAsync('GetUser', {
                    'id': this.selectedUser,
                    'account': true
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        callOptions.showMessage = false;
                        var userInfo = response.data;
                        if (userInfo) {
                            $('#users-form input, #users-form select, #users-form textarea').each(
                                function () {
                                    if ($(this).is('select')) {
                                        if (userInfo[$(this).attr('name')] === true) {
                                            $(this).val('1');
                                        } else if (userInfo[$(this).attr('name')] === false) {
                                            $(this).val('0');
                                        } else {
                                            $(this).val(userInfo[$(this).attr('name')]);
                                        }
                                    } else {
                                        $(this).val(userInfo[$(this).attr('name')]);
                                    }
                                }
                            );
                            $("#password").prop('disabled', true);

                            $('#userModal').modal('show');
                        }
                    }
                }
            );
        },

        /**
         * Edit user ACL
         */
        editUserACL: function (uid) {
            this.selectedUser = uid;
            $('#aclModal').modal('show');

            this.gadget.chkImages = $('#aclModal .acl-images img').map(function() {
                return $(this).attr('src');
            }).toArray();
            this.gadget.chkImages[-1] = this.gadget.chkImages[2];
            delete this.gadget.chkImages[2];
        },

        /**
         * Edit the groups of user
         */
        editUserGroups: function(uid) {
            this.selectedUser = uid;
            this.currentAction = 'UserGroups';
            $('#userGroupsModal').modal('show');
        },

        /**
         * Delete user from groups
         */
        deleteUserFromGroups: function (groupIds) {
            this.gadget.ajax.callAsync('DeleteUserFromGroups', {'uid': this.selectedUser, 'groupIds': groupIds});
        },

        /**
         * Edit user's personal information
         */
        editPersonal: function (uid) {
            this.selectedUser = uid;
            this.currentAction = 'UserPersonal';

            this.ajax.callAsync('GetUser', {
                    'id': uid,
                    'account': true,
                    'personal': true,
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        var userInfo = response.data;
                        $('#user-personal-form input, #user-personal-form select, #user-personal-form textarea').each(
                            function () {
                                if ($(this).is('select')) {
                                    if (userInfo[$(this).attr('name')] === true) {
                                        $(this).val('1');
                                    } else if (userInfo[$(this).attr('name')] === false) {
                                        $(this).val('0');
                                    } else {
                                        $(this).val(userInfo[$(this).attr('name')]);
                                    }
                                } else {
                                    $(this).val(userInfo[$(this).attr('name')]);
                                }
                            }
                        );
                        $('#image').attr('src', userInfo['avatar']+ '?'+ (new Date()).getTime());

                        $('#personalModal').modal('show');
                    }
                }
            );
        },

        /**
         * Edit user's contacts info
         */
        editContacts: function(uid) {
            this.selectedUser = uid;
            this.currentAction = 'UserContacts';

            this.ajax.callAsync('GetUserContact', {
                    'uid': uid
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        var cInfo = response.data;
                        if (cInfo) {
                            this.changeProvince(cInfo['province_home'], 'city_home');
                            this.changeProvince(cInfo['province_work'], 'city_work');
                            this.changeProvince(cInfo['province_other'], 'city_other');

                            $('#user-contacts-form input, #user-contacts-form select, #user-contacts-form textarea').each(
                                function () {
                                    $(this).val(cInfo[$(this).attr('name')]);
                                }
                            );
                        }

                        $('#contactsModal').modal('show');
                    }
                }
            );
        },

        /**
         * Edit user's extra attributes
         */
        editUserExtra: function(uid) {
            this.selectedUser = uid;
            this.currentAction = 'UserExtra';

            this.ajax.callAsync('GetUserExtra', {
                    'uid': uid
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        var exInfo = response.data;
                        if (exInfo) {
                            $('#user-extra-form input, #user-extra-form select, #user-extra-form textarea').each(
                                function () {
                                    $(this).val(exInfo[$(this).attr('name')]);
                                }
                            );
                        }

                        $('#extraModal').modal('show');
                    }
                }
            );
        },

        /**
         * Change user's password
         */
        changeUserPassword: function(username, uid) {
            this.selectedUser = uid;
            this.currentAction = 'changeUserPassword';
            $('#user-password-form input[name="username"]').val(username);
            $('#passwordModal').modal('show');
        },

        /**
         * Stops doing a certain action
         */
        stopUserAction: function() {
            this.selectedUser = 0;
            this.gadget.cancelSelectCombobox($('#group_combo'));
            $("#password").prop('disabled', false);
            $('#users-form')[0].reset();
        },

        /**
         * view user info
         */
        viewUser: function (id) {
            this.ajax.callAsync('GetSyncError', {
                    'id': id,
                }, function (response, status, callOptions) {
                    if (response.type === 'alert-success') {
                        var syncError = response.data;
                        $('#sync-error-details-form span').each($.proxy(function (i, elem) {
                                if ($(elem).data('field') == 'data') {
                                    $(elem).html(this.gadget.syntaxHighlightJson(syncError[$(elem).data('field')]));
                                } else {
                                    $(elem).html(syncError[$(elem).data('field')]);
                                }
                            }, this)
                        );

                        $('#userModal').modal('show');
                    }
                }
            );
        },

        /**
         * Define the data to be displayed in the user datagrid
         */
        usersDataSource: function (options, callback) {
            var columns = {
                'nickname': {
                    'label': this.t('users_nickname'),
                    'property': 'nickname'
                },
                'username': {
                    'label': this.t('users_username'),
                    'property': 'username'
                },
                'email': {
                    'label': Jaws.t('email'),
                    'property': 'email'
                },
                'mobile': {
                    'label': this.t('contacts_mobile_number'),
                    'property': 'mobile'
                },
                'status': {
                    'label': Jaws.t('status'),
                    'property': 'status'
                },
            };

            var filters = $.unserialize($('#users-grid .datagrid-filters form').serialize());
            filters.filter_group = $('#filter_group').combobox('selectedItem').value === undefined ? 0 :
                $('#filter_group').combobox('selectedItem').value;

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.gadget.ajax.callAsync(
                'GetUsers', {
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
         * users Datagrid column renderer
         */
        usersDGColumnRenderer: function (helpers, callback) {
            var column = helpers.columnAttr;
            var rowData = helpers.rowData;
            var customMarkup = '';

            switch (column) {
                case 'status':
                    customMarkup = this.gadget.defines.statusItems[rowData.status];
                    break;
                default:
                    customMarkup = helpers.item.text();
                    break;
            }

            helpers.item.html(customMarkup);
            callback();
        },

        /**
         * initiate User dataGrid
         */
        initiateUsersDG: function() {
            var list_actions = {
                width: 50,
                items: [
                    {
                        name: 'edit',
                        html: '<span class="glyphicon glyphicon-pencil"></span> ' + Jaws.t('edit'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editUser(helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'password',
                        html: '<span class="glyphicon glyphicon-lock"></span> ' + this.t('users_password'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.changeUserPassword(helpers.rowData.username, helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'acl',
                        html: '<span class="glyphicon glyphicon-lock"></span> ' + this.t('acls'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editUserACL(helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'users_groups',
                        html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('users_groups'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editUserGroups(helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'personal',
                        html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('personal'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editPersonal(helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'contacts',
                        html: '<span class="glyphicon glyphicon-envelope"></span> ' + this.t('contacts'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editContacts(helpers.rowData.id);
                            callback();
                        }, this)
                    },
                    {
                        name: 'extra',
                        html: '<span class="glyphicon glyphicon-th-large"></span> ' + this.t('extra'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editUserExtra(helpers.rowData.id);
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

                            this.deleteUsers(ids);
                            callback();
                        }, this)

                    },
                ]
            };

            // initialize the repeater
            $('#users-grid').repeater({
                dataSource: $.proxy(this.usersDataSource, this),
                list_actions: list_actions,
                list_columnRendered: $.proxy(this.usersDGColumnRenderer, this),
                list_selectable: 'multi',
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#users-grid select").change(function () {
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
            $("#users-grid input").keypress(function (e) {
                if (e.which == 13) {
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            });
            $("#users-grid button.btn-refresh").on('click', function (e) {
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        /**
         * Define the data to be displayed in the user ACLs datagrid
         */
        userACLsDataSource: function (options, callback) {
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

            this.gadget.ajax.callAsync(
                'GetObjectACLs', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': {'id': this.selectedUser, 'action':'User'}
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
         * userACLs Datagrid column renderer
         */
        userACLsDGRowRenderer: function (helpers, callback) {
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
         * userAcls Datagrid column renderer
         */
        userACLsDGColumnRenderer: function (helpers, callback) {
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
        initiateUserACLsDG: function() {
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

                            var acls = [];
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

                            this.deleteUserACLs(acls);
                            callback();
                        }, this)

                    },
                ]
            };

            // initialize the repeater
            $('#item-acls-grid').repeater({
                dataSource: $.proxy(this.userACLsDataSource, this),
                list_actions: list_actions,
                list_infiniteScroll: true,
                list_columnRendered: $.proxy(this.userACLsDGColumnRenderer, this),
                list_rowRendered: $.proxy(this.userACLsDGRowRenderer, this),
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
         * Define the data to be displayed in the user datagrid
         */
        userGroupsDataSource: function (options, callback) {
            var columns = {
                'name': {
                    'label': Jaws.t('name'),
                    'property': 'name'
                },
                'title': {
                    'label': Jaws.t('title'),
                    'property': 'title'
                }
            };

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.gadget.ajax.callAsync(
                'GetUserGroups', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': {'uid': this.selectedUser}
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
         * initiate User's groups dataGrid
         */
        initiateUserGroupsDG: function() {
            if ($('#user-groups-grid').data('currentview') !== undefined) {
                return $('#user-groups-grid').repeater('render', {clearInfinite: true,pageIncrement: null});
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

                            this.deleteUserFromGroups(ids);
                            callback();
                        }, this)

                    }
                ]
            };

            // initialize the repeater
            $('#user-groups-grid').repeater({
                dataSource: $.proxy(this.userGroupsDataSource, this),
                list_actions: list_actions,
                list_selectable: 'multi',
                list_infiniteScroll: true,
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $("#user-groups-grid button.btn-refresh").on('click', function (e) {
                $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            this.currentAction = 'UserAccount';
            $('#filter_term').val('');
            $('#filter_type').prop('selectedIndex', 0);
            $('#filter_status').prop('selectedIndex', 0);
            this.stopUserAction();

            $('#filter_group').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': Jaws.t('combo_no_match_message')
            }).combobox('enable')
                .find('>input').val('');
            $("#filter_group").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                this.gadget.searchGroupsAndFillCombo($('#filter_group'));
            }, this)).on('changed.fu.combobox', $.proxy(function (evt, data) {
                if (data.value !== undefined) {
                    $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            }, this)).trigger('keyup.fu.combobox');

            $('button.btn-cancel-select-group').on('click', $.proxy(function (e) {
                var cmbName = $(e.target).data('combo-name');
                if ($(e.target).is("span")) {
                    cmbName = $(e.target).parent().data('combo-name');
                }
                this.gadget.cancelSelectCombobox($('#' + cmbName));
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }, this));

            this.initiateUsersDG();

            // initiate #group_combo
            $('#group_combo').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': Jaws.t('combo_no_match_message')
            }).combobox('enable')
                .find('>input').val('');
            $("#group_combo").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                this.gadget.searchGroupsAndFillCombo($('#group_combo'));
            }, this)).trigger('keyup.fu.combobox');

            $('#userModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));
            $('#passwordModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));
            $('#aclModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this)).on('shown.bs.modal', $.proxy(function (e) {
                this.initiateUserACLsDG();
            }, this));
            $('#userGroupsModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this)).on('shown.bs.modal', $.proxy(function (e) {
                this.initiateUserGroupsDG();
            }, this));
            $('#personalModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));
            $('#contactsModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));
            $('#extraModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));

            $('#components').on('change', $.proxy(function (e) {
                this.gadget.getACL(this.selectedUser, 'UserACL');
            }, this));

            $('#btnSaveUser').on('click', $.proxy(function (e) {
                this.saveUser();
            }, this));
            $('#btnSaveACLs').on('click', $.proxy(function (e) {
                this.saveUserACL();
            }, this));
            $('#btnAddUserToGroup').on('click', $.proxy(function (e) {
                var gid = $('#group_combo').combobox('selectedItem').value;
                if ( gid === undefined) {
                    return false;
                }
                this.addUserToGroup(this.selectedUser, gid);
            }, this));
            $('#btnSaveUserPersonal').on('click', $.proxy(function (e) {
                this.saveUserPersonal();
            }, this));
            $('#btnSaveUserContact').on('click', $.proxy(function (e) {
                this.saveUserContact();
            }, this));
            $('#btnSaveUserExtra').on('click', $.proxy(function (e) {
                this.saveUserExtra();
            }, this));
            $('#btnSaveUserPassword').on('click', $.proxy(function (e) {
                this.saveUserPassword();
            }, this));

            // toggle password between hide and show
            $("input[type='password']+span.input-group-addon").click(
                function() {
                    if ($(this).prev().attr('type') == 'password') {
                        $(this).prev().attr('type', 'text');
                    } else {
                        $(this).prev().attr('type', 'password');
                    }
                    $(this).find('i').toggleClass('glyphicon-eye-open glyphicon-eye-close');
                }
            );
        }
    }
};
