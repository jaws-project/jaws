/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users() { return {
    // timeout id
    fTimeout : null,
        
    currentAction: null,

    // selected user/group ID
    selectedUser : 0,
    selectedGroup : 0,

    // checkbox, allow & deny icons
    chkImages : [],

    //Cached form variables
    SettingsInUsersAjax: null,

    // ASync callback method
    AjaxCallback : {
        AddUser: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#userModal').modal('hide');
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateUser: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#userModal').modal('hide');
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateUserACL: function(response) {
            if (response['type'] == 'alert-success') {
                $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteUserACLs: function(response) {
            if (response['type'] == 'alert-success') {
                $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteUserFromGroups: function(response) {
            if (response['type'] == 'alert-success') {
                $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteUsersFromGroup: function(response) {
            if (response['type'] == 'alert-success') {
                $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AddUserToGroup: function(response) {
            if (response['type'] == 'alert-success') {
                if ($('#group_combo').length) {
                    $('#group_combo').find('>input').val('');
                    $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                } else {
                    $('#user_combo').find('>input').val('');
                    $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            }
        },

        UpdatePersonal: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#personalModal').modal('hide');
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateUserPassword: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#passwordModal').modal('hide');
            }
        },

        UpdateUserContacts: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#contactsModal').modal('hide');
            }
        },

        UpdateUserExtra: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#extraModal').modal('hide');
            }
        },

        UpdateMyAccount: function(response) {
            $('#pass1').val('');
            $('#pass2').val('');
        },

        DeleteUsers: function(response) {
            if (response['type'] == 'alert-success') {
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AddGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groupModal').modal('hide');
                $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groupModal').modal('hide');
                $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateGroupACL: function(response) {
            if (response['type'] == 'alert-success') {
                $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteGroupACLs: function(response) {
            if (response['type'] == 'alert-success') {
                $('#item-acls-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteGroups: function(response) {
            if (response['type'] == 'alert-success') {
                $('#groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AddUsersToGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
            }
        },

        DeleteSessions: function(response) {
            if (response['type'] == 'alert-success') {
                clearTimeout(this.fTimeout);
                $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        IPsBlock: function(response) {
            if (response['type'] == 'alert-success') {
                clearTimeout(this.fTimeout);
                $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AgentsBlock: function(response) {
            if (response['type'] == 'alert-success') {
                clearTimeout(this.fTimeout);
                $('#online-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        }

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
     * On term key press, for compatibility Opera/IE with other browsers
     */
    OnTermKeypress: function(element, event) {
        if (event.keyCode == 13) {
            element.blur();
            element.focus();
        }
    },

    /**
     * Saves users data / changes
     */
    saveUser: function() {
        if (!$('#username').val() ||
            !$('#nickname').val() ||
            (!$('#email').val() && !$('#mobile').val())
        ) {
            alert(this.gadget.defines.incompleteUserFields);
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
                    alert(this.gadget.defines.incompleteUserFields);
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
            alert(this.gadget.defines.wrongPassword);
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
     * Delete user(s)
     */
    deleteUsers: function (uids) {
        if (confirm(this.gadget.defines.confirmUserDelete)) {
            this.gadget.ajax.callAsync('DeleteUsers', {'uids': uids});
        }
    },

    /**
     * Delete group(s)
     */
    deleteGroups: function(gids) {
        if (confirm(this.gadget.defines.confirmGroupDelete)) {
            this.gadget.ajax.callAsync('DeleteGroups', {'gids': gids});
        }
    },

    /**
     * Edit user
     */
    editUser: function(uid) {
        // $('#legend_title').html(this.gadget.defines.editUser_title);
        this.selectedUser = uid;

        this.ajax.callAsync('GetUser', {
                'id': this.selectedUser,
                'account': true
            }, function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
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
        $('#aclModal')
            .modal('show')
            .on('shown.bs.modal', $.proxy(function (e) {
            this.initiateUserACLsDG();
        }, this));

        this.chkImages = $('#aclModal .acl-images img').map(function() {
            return $(this).attr('src');
        }).toArray();
        this.chkImages[-1] = this.chkImages[2];
        delete this.chkImages[2];
    },

    //-------------------------------
    /**
     * Loads ACL data of the selected gadget/plugin
     */
    getACL: function() {
        function getValue(custom_acls, key, subkey) {
            var res = -1;
            $.each(custom_acls, function (index, acl) {
                if (acl.key_name === key && acl.key_subkey == subkey) {
                    res = acl.key_value;
                    return false; 
                }
            });

            return res;
        }

        if ($('#components').val() === '') {
            $('#acl_form').html('');
            return;
        }

        var action = 'UserACL';
        var id = this.selectedUser;
        if (this.gadget.actions.indexOf('Groups') >= 0) {
            action = 'GroupACL';
            id = this.selectedGroup;
        }

        this.ajax.callAsync('GetACLKeys', {
                'id': id,
                'comp': $('#components').val(),
                'action': action
            }, function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    var acls = response.data;
                    var form = $('#acl_form').html('');
                    $.each(acls.default_acls, $.proxy(function (index, acl) {
                        var key_unique = acl.key_name + ':' + acl.key_subkey;
                        var check = $('<img/>').attr('id', key_unique),
                            label = $('<label></label>').attr('for', key_unique),
                            div = $('<div></div>').append(check, label),
                            value = getValue(acls.custom_acls, acl.key_name, acl.key_subkey);

                        label.html(acl.key_desc);
                        check.attr('alt', value);
                        check.attr('src', this.chkImages[value]);
                        label.on('click', $.proxy(function (event) {
                            var check = $(event.target).prev('img'),
                                value = parseInt(check.attr('alt'));
                            check.attr('alt', (value == -1) ? 1 : value - 1);
                            check.attr('src', this.chkImages[check.attr('alt')]);
                        }, this));
                        check.on('click', $.proxy(function () {
                            $(event.target).attr('alt', (event.target.alt == -1) ? 1 : parseInt($(event.target).attr('alt')) - 1);
                            $(event.target).attr('src', this.chkImages[$(event.target).attr('alt')]);
                        }, this));
                        form.append(div);
                    }, this));
                }
            }
        );


    },

    /**
     * Edit the groups of user
     */
    editUserGroups: function(uid) {
        this.selectedUser = uid;
        this.currentAction = 'UserGroups';
        $('#userGroupsModal')
            .modal('show')
            .on('shown.bs.modal', $.proxy(function (e) {
                this.initiateUserGroupsDG();
            }, this));
    },

    /**
     * Add an user to a group
     */
    addUserToGroup: function (uid, gid) {
        this.gadget.ajax.callAsync('AddUserToGroup', {
            'uid': uid,
            'gid': gid
        });
    },

    /**
     * Delete user from groups
     */
    deleteUserFromGroups: function (groupIds) {
        this.gadget.ajax.callAsync('DeleteUserFromGroups', {'uid': this.selectedUser, 'groupIds': groupIds});
    },

    /**
     * Delete users from group
     */
    deleteUsersFromGroup: function (userIds) {
        this.gadget.ajax.callAsync('DeleteUsersFromGroup', {'gid': this.selectedGroup, 'userIds': userIds});
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
                if (response['type'] == 'alert-success') {
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
                if (response['type'] == 'alert-success') {
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
                if (response['type'] == 'alert-success') {
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
     * change province combo
     */
    changeProvince: function(province, cityElement) {
        var cities = this.SettingsInUsersAjax.callSync('GetCities', {'province': province, 'country': 364});
        $('#' + cityElement ).html('');
        $.each(cities, function (index, city) {
            $("#" + cityElement).append('<option value="' + city.city + '">' + city.title + '</option>');
        });
    },

    /**
     * Uploads the avatar
     */
    upload: function() {
        $('#workarea').append($('<iframe></iframe>').attr({'id': 'ifrm_upload', 'name':'ifrm_upload'}));
        $('#frm_avatar').submit();
    },

    /**
     * Removes the avatar
     */
    removeAvatar: function() {
        $('#avatar').val('');
        $('#frm_avatar')[0].reset();
        $('#image').attr('src', 'gadgets/Users/Resources/images/photo128px.png');
    },

    /**
     * Stops doing a certain action
     */
    stopUserAction: function() {
        this.selectedUser = 0;
        $('#users-form')[0].reset();
    },

    /**
     * Edit group
     */
    editGroup: function(gid) {
        this.selectedGroup = gid;
        $('#groupModal .modal-title').html(this.gadget.defines.LANGUAGE.edit_group_title);

        this.ajax.callAsync('GetGroup', {
                'gid': this.selectedGroup
            }, function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var gInfo = response.data;
                    if (gInfo) {
                        $('#name').val(gInfo.name);
                        $('#title').val(gInfo.title.defilter());
                        $('#description').val(gInfo.description === null ? '' : gInfo.description.defilter());
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
        $('#groupUsersModal')
            .modal('show')
            .on('shown.bs.modal', $.proxy(function (e) {
                this.initiateGroupUsersDG();

                $('#user_combo').combobox({
                    'showOptionsOnKeypress': true,
                    'noMatchesMessage': this.gadget.defines.noMatchesMessage
                });
                $('#user_combo').combobox('enable');
                $('#user_combo').find('>input').val('');
                $("#user_combo").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                    this.searchUsersAndFillCombo($('#user_combo'));
                }, this));
                $("#user_combo").trigger('keyup.fu.combobox');

            }, this));
    },

    /**
     * Saves data / changes on the group's form
     */
    saveGroup: function() {
        if (!$('#name').val() || !$('#title').val()) {
            alert(this.gadget.defines.incompleteGroupFields);
            return false;
        }

        if (this.selectedGroup == 0) {
            this.gadget.ajax.callAsync(
                'AddGroup', {
                    'data': $.unserialize($('#group-form input,#group-form select,#group-form textarea').serialize())
                });
        } else {
            this.gadget.ajax.callAsync(
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
        if ($('#components').val() === '') {
            return;
        }
        var acls = [];
        $.each($('#acl_form img[alt!="-1"]'), function (index, aclTag) {
            var keys = $(aclTag).attr('id').split(':');
            acls[index] = [keys[0], keys[1], $(aclTag).attr('alt')];
        });
        this.gadget.ajax.callAsync('UpdateGroupACL', {
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
        $('#aclModal')
            .modal('show')
            .on('shown.bs.modal', $.proxy(function (e) {
                this.initiateGroupACLsDG();
            }, this));

        this.chkImages = $('#aclModal .acl-images img').map(function() {
            return $(this).attr('src');
        }).toArray();
        this.chkImages[-1] = this.chkImages[2];
        delete this.chkImages[2];
    },

    /**
     * Delete group's ACL
     */
    deleteGroupACLs: function(acls) {
        this.gadget.ajax.callAsync('DeleteGroupACLs', {
            'gid': this.selectedGroup,
            'acls': acls
        });
    },


    /**
     * Stops doing a certain action
     */
    stopGroupAction: function() {
        this.selectedGroup = 0;
        $('form#group-form')[0].reset();
        $('#groupModal .modal-title').html(this.gadget.defines.LANGUAGE.add_group_title);
    },

    /**
     * Save settings
     */
    updateSettings: function() {
        this.gadget.ajax.callAsync(
            'UpdateSettings',
            $.unserialize($('#users_settings input,select,textarea').serialize())
        );
    },

    /**
     * Update myAccount
     */
    updateMyAccount: function() {
        if ($('#pass1').val() != $('#pass2').val()) {
            alert(this.gadget.defines.wrongPassword);
            return false;
        }

        if (!$('#username').val() ||
            !$('#nickname').val() ||
            !$('#email').val())
        {
            alert(this.gadget.defines.incompleteUserFields);
            return false;
        }

        $.loadScript('libraries/js/jsencrypt.min.js', function() {
            if ($('#pubkey').length) {
                var objRSACrypt = new JSEncrypt();
                objRSACrypt.setPublicKey($('#pubkey').val());
                $('#pass1').val(objRSACrypt.encrypt($('#pass1').val()));
                $('#pass2').val($('#pass1').val());
            }

            this.gadget.ajax.callAsync(
                'UpdateMyAccount',
                {'uid': $('#uid').val(),
                 'username': $('#username').val(),
                 'password': $('#pass1').val(),
                 'nickname': $('#nickname').val(),
                 'email'   : $('#email').val(),
                 'mobile'  : $('#mobile').val()
                }
            );
        }, this.gadget);

    },

    /**
     * view a acl permissions
     */
    viewACL: function(component, acl) {
        this.gadget.ajax.callAsync(
            'GetACLGroupsUsers',
            {component: component, acl:acl},
            function (response) {
                $("#groups_permission ul").html('');
                $.each(response.groups, function (key, group) {
                    var status = '<span class="glyphicon glyphicon-ok"></span>';
                    if(group.key_value==0) {
                        status = '<span class="glyphicon glyphicon-remove"></span>';
                    }
                    $("#groups_permission ul").append('<li>' + group.title + ' ' + status +'</li>');
                });

                $("#users_permission ul").html('');
                $.each(response.users, function (key, user) {
                    var status = '<span class="glyphicon glyphicon-ok"></span>';
                    if(user.key_value==0) {
                        status = '<span class="glyphicon glyphicon-remove"></span>';
                    }
                    $("#users_permission ul").append('<li>' + user.nickname  + ' ' + status +'</li>');
                });
            }
        );
    },

    /**
     * Categories tree data source
     */
    aclTreeDataSource: function(openedParentData, callback) {
        var childNodesArray = [];

        var pid = openedParentData.id == undefined ? 0 : openedParentData.id;
        if (pid == 0) {
            $.each(this.gadget.defines.GADGETS, function (gadget, title) {
                childNodesArray.push(
                    {
                        id: gadget,
                        name: title,
                        type: 'folder',
                        attr: {
                            id: 'gadget_' + gadget,
                            hasChildren: true,
                        },
                    }
                );
            });

            callback({
                data: childNodesArray
            });

        } else {
            this.gadget.ajax.callAsync('GetACLs', {component: pid}, function (response) {
                $.each(response, function (key, acl) {
                    childNodesArray.push(
                        {
                            id: acl.key_name,
                            name: acl.key_desc,
                            component: pid,
                            type: 'item',
                            attr: {
                                id: 'acl_' + acl.key_name,
                                hasChildren: false,
                            },
                        }
                    );
                });

                callback({
                    data: childNodesArray
                });
            });
        }
    },

    /**
     * Initiates ACLs tree
     */
    initiateACLsTree: function() {
        $('#aclTree').tree({
            dataSource: $.proxy(this.aclTreeDataSource, this),
            multiSelect: false,
            folderSelect: true
        }).on('selected.fu.tree', $.proxy(function (event, data) {
            if (data.selected[0].type == 'item') {
                this.viewACL(data.selected[0].component, data.selected[0].id);
            }
        }, this));
    },

    /**
     *
     */
    encryptFormSubmit: function(form, elements)
    {
        if ($('#usecrypt').prop('checked') && (elements.length > 0) && form.pubkey) {
            $.loadScript('libraries/js/jsencrypt.min.js', function() {
                var objRSACrypt = new JSEncrypt();
                objRSACrypt.setPublicKey(form.pubkey.value);
                $.each(elements, function( k, el ) {
                    form.elements[el].value = objRSACrypt.encrypt(form.elements[el].value);
                });
                form.submit();
            });

            return false;
        }

        return true;
    },

    /**
     * Delete online users
     */
    deleteOnlineUsers: function(ids) {
        if (confirm(this.gadget.defines.confirmThrowOut)) {
            this.gadget.ajax.callAsync('DeleteSessions', {'ids': ids});
        }
    },

    /**
     * Block online users IP address
     */
    blockOnlineUsersIP: function(ids) {
        if (confirm(this.gadget.defines.confirmBlockIP)) {
            this.gadget.ajax.callAsync('IPsBlock', {'ids': ids});
        }
    },

    /**
     * Block online users agent
     */
    blockOnlineUsersAgent: function(ids) {
        if (confirm(this.gadget.defines.confirmBlockAgent)) {
            this.gadget.ajax.callAsync('AgentsBlock', {'ids': ids});
        }
    },

    /**
     * view user info
     */
    viewUser: function (id) {
        this.ajax.callAsync('GetSyncError', {
                'id': id,
            }, function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
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
                'label': this.gadget.defines.LANGUAGE.nickname,
                'property': 'nickname'
            },
            'username': {
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username'
            },
            'email': {
                'label': this.gadget.defines.LANGUAGE.email,
                'property': 'email'
            },
            'mobile': {
                'label': this.gadget.defines.LANGUAGE.mobile,
                'property': 'mobile'
            },
            'status': {
                'label': this.gadget.defines.LANGUAGE.status,
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
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editUser(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'password',
                    html: '<span class="glyphicon glyphicon-lock"></span> ' + this.gadget.defines.LANGUAGE.password,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.changeUserPassword(helpers.rowData.username, helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'acl',
                    html: '<span class="glyphicon glyphicon-lock"></span> ' + this.gadget.defines.LANGUAGE.acls,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editUserACL(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'users_groups',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + this.gadget.defines.LANGUAGE.users_groups,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editUserGroups(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'personal',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + this.gadget.defines.LANGUAGE.personal,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editPersonal(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'contacts',
                    html: '<span class="glyphicon glyphicon-envelope"></span> ' + this.gadget.defines.LANGUAGE.contacts,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editContacts(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'extra',
                    html: '<span class="glyphicon glyphicon-th-large"></span> ' + this.gadget.defines.LANGUAGE.extra,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editUserExtra(helpers.rowData.id);
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
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
                'label': this.gadget.defines.LANGUAGE.components,
                'property': 'component_title',
                'width': '30%'
            },
            'acl_key_title': {
                'label': this.gadget.defines.LANGUAGE.acl_key_title,
                'property': 'key_title',
                'width': '50%'
            },
            'acl': {
                'label': this.gadget.defines.LANGUAGE.acl,
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
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
                    customMarkup = this.gadget.defines.LANGUAGE.acl_deny;
                } else if (rowData.key_value == 1) {
                    customMarkup = this.gadget.defines.LANGUAGE.acl_allow;
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
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
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
                'label': this.gadget.defines.LANGUAGE.name,
                'property': 'name'
            },
            'title': {
                'label': this.gadget.defines.LANGUAGE.title,
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
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#user-groups-grid button.btn-refresh").on('click', function (e) {
            $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * Define the data to be displayed in the groups datagrid
     */
    groupsDataSource: function (options, callback) {
        var columns = {
            'name': {
                'label': this.gadget.defines.LANGUAGE.name,
                'property': 'name',
                'width': '30%'
            },
            'title': {
                'label': this.gadget.defines.LANGUAGE.title,
                'property': 'title',
                'width': '55%'
            },
            'enabled': {
                'label': this.gadget.defines.LANGUAGE.enabled,
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

        this.gadget.ajax.callAsync(
            'GetGroups', {
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
     * groups Datagrid column renderer
     */
    groupsDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'enabled':
                if (helpers.item.text() == "true") {
                    customMarkup = this.gadget.defines.LANGUAGE.yes;
                } else {
                    customMarkup = this.gadget.defines.LANGUAGE.no;
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
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.editGroup(helpers.rowData.id);
                        callback();
                    }, this)

                },
                {
                    name: 'acl',
                    html: '<span class="glyphicon glyphicon-lock"></span> ' + this.gadget.defines.LANGUAGE.acls,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editGroupACL(helpers.rowData.id);
                        callback();
                    }, this)

                },
                {
                    name: 'group_members',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + this.gadget.defines.LANGUAGE.group_members,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editGroupUsers(helpers.rowData.id);
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
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
                'label': this.gadget.defines.LANGUAGE.components,
                'property': 'component_title',
                'width': '30%'
            },
            'acl_key_title': {
                'label': this.gadget.defines.LANGUAGE.acl_key_title,
                'property': 'key_title',
                'width': '50%'
            },
            'acl': {
                'label': this.gadget.defines.LANGUAGE.acl,
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
                'filters': {'id': this.selectedGroup, 'action':'Group'}
            },
            function(response, status, callOptions) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
                    customMarkup = this.gadget.defines.LANGUAGE.acl_deny;
                } else if (rowData.key_value == 1) {
                    customMarkup = this.gadget.defines.LANGUAGE.acl_allow;
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
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
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
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username'
            },
            'nickname': {
                'label': this.gadget.defines.LANGUAGE.nickname,
                'property': 'nickname'
            }
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetGroupUsers', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': {'gid': this.selectedGroup}
            },
            function(response, status, callOptions) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#group-users-grid button.btn-refresh").on('click', function (e) {
            $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * Define the data to be displayed in the online-user datagrid
     */
    onlineUsersDataSource: function (options, callback) {
        var columns = {
            'username': {
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username',
                'width': '15%'
            },
            'nickname': {
                'label': this.gadget.defines.LANGUAGE.nickname,
                'property': 'nickname',
                'width': '20%'
            },
            'superadmin': {
                'label': this.gadget.defines.LANGUAGE.superadmin,
                'property': 'superadmin',
                'width': '15%'
            },
            'ip': {
                'label': this.gadget.defines.LANGUAGE.ip,
                'property': 'ip',
                'width': '15%'
            },
            'type': {
                'label': this.gadget.defines.LANGUAGE.session_type,
                'property': 'type',
                'width': '15%'
            },
            'last_activetime': {
                'label': this.gadget.defines.LANGUAGE.last_activetime,
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
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

                    // processing end item index of page
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
                if(rowData.username==='') {
                    customMarkup = this.gadget.defines.LANGUAGE.anonymous;
                } else {
                    customMarkup = "<a href='" + rowData.user_profile_url + "' target='_blank'>" + rowData.username + "</a>";
                }
                break;
            case 'superadmin':
                customMarkup = rowData.superadmin ? this.gadget.defines.LANGUAGE.yes : this.gadget.defines.LANGUAGE.no;
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
                    customMarkup = '<span title="' + this.gadget.defines.LANGUAGE.active + '">' +
                        helpers.item.text() + '</span>';
                } else {
                    customMarkup = '<s title="' + this.gadget.defines.LANGUAGE.inactive + '">' +
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

                        this.deleteOnlineUsers(ids);
                        callback();
                    }, this)

                },
                {
                    name: 'block_ip',
                    html: '<span class="glyphicon glyphicon-ban-circle"></span> ' + this.gadget.defines.LANGUAGE.block_ip,
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
                    html: '<span class="glyphicon glyphicon-ban-circle"></span> ' + this.gadget.defines.LANGUAGE.block_agent,
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
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
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
     * Search users and fill combo
     */
    searchUsersAndFillCombo: function (comboElm) {
        console.log('searchUsersAndFillCombo');
        this.ajax.callAsync(
            'GetUsers',
            {'filters': {'filter_term': $(comboElm).find('>input').val()}, 'limit': 10},
            $.proxy(function (response, status) {
                $(comboElm).find('div.input-group-btn ul.dropdown-menu').html('');
                if (response['type'] == 'alert-success' && response.data.total > 0) {
                    console.log(response.data.records);
                    $.each(response.data.records, $.proxy(function (key, user) {
                        this.addOptionToCombo(comboElm, {'value': user.id, 'title': user.nickname});
                    }, this));
                }
            }, this)
        );
    },

    /**
     * Search groups and fill combo
     */
    searchGroupsAndFillCombo: function (comboElm) {
        this.ajax.callAsync(
            'GetGroups',
            {'filters': {'filter_term': $(comboElm).find('>input').val()}, 'limit': 10},
            $.proxy(function (response, status) {
                $(comboElm).find('div.input-group-btn ul.dropdown-menu').html('');
                if (response['type'] == 'alert-success' && response.data.total > 0) {
                    $.each(response.data.records, $.proxy(function (key, group) {
                        this.addOptionToCombo(comboElm, {'value': group.id, 'title': group.title});
                    }, this));
                }
            }, this)
        );
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        this.SettingsInUsersAjax = new JawsAjax('Settings');

        // init login box action
        if (this.gadget.actions.indexOf('Login') >= 0) {
            if ($('#loginkey').length) {
                $('#loginkey').focus();
            } else {
                $('#username').focus();
                $('#username').select();
            }
        }

        // init users action
        if (this.gadget.actions.indexOf('Users') >= 0) {
            this.currentAction = 'UserAccount';
            $('#filter_term').val('');
            $('#filter_type').prop('selectedIndex', 0);
            $('#filter_status').prop('selectedIndex', 0);
            this.stopUserAction();

            $('#filter_group').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': this.gadget.defines.noMatchesMessage
            }).combobox('enable')
                .find('>input').val('');
            $("#filter_group").on('keyup.fu.combobox', $.proxy(function (evt, data) {
                this.searchGroupsAndFillCombo($('#filter_group'));
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
                this.cancelSelectCombobox($('#' + cmbName));
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }, this));

            this.initiateUsersDG();

            $('#group_combo').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': this.gadget.defines.noMatchesMessage
            }).combobox('enable')
                .find('>input').val('')
                .on('keyup.fu.combobox', $.proxy(function (evt, data) {
                    this.searchGroupsAndFillCombo($('#group_combo'));
                }, this));
            $("#group_combo").trigger('keyup.fu.combobox');

            $('#userModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));
            $('#personalModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction();
            }, this));

            $('#components').on('change', $.proxy(function (e) {
                this.getACL();
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

        // init groups action
        if (this.gadget.actions.indexOf('Groups') >= 0) {
            this.stopGroupAction();
            this.initiateGroupsDG();

            $('#groupModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopGroupAction();
            }, this));

            $('#btnSaveGroup').on('click', $.proxy(function (e) {
                this.saveGroup();
            }, this));

            $('#btnSaveACLs').on('click', $.proxy(function (e) {
                this.saveGroupACL();
            }, this));

            $('#components').on('change', $.proxy(function (e) {
                this.getACL();
            }, this));

            $('#btnAddUserToGroup').on('click', $.proxy(function (e) {
                var uid = $('#user_combo').combobox('selectedItem').value;
                if ( uid === undefined) {
                    return false;
                }
                this.addUserToGroup(uid, this.selectedGroup);
            }, this));

            $('button.btn-cancel-select-user').on('click', $.proxy(function (e) {
                var cmbName = $(e.target).data('combo-name');
                if ($(e.target).is("span")) {
                    cmbName = $(e.target).parent().data('combo-name');
                }
                this.cancelSelectCombobox($('#' + cmbName));
            }, this));

        }

        // init ACLs action
        if (this.gadget.actions.indexOf('ACLs') >= 0) {
            this.currentAction = 'ACLs';
            this.initiateACLsTree();
        }

        // init online users action
        if (this.gadget.actions.indexOf('OnlineUsers') >= 0) {
            this.initiateOnlineUsersDG();
        }

        // init settings action
        if (this.gadget.actions.indexOf('Settings') >= 0) {
            $('#btnUpdateSettings').on('click', $.proxy(function (e) {
                this.updateSettings();
            }, this));
        }
    },

}};
