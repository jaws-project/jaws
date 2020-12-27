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
    selectedId : null,
    selectedUser : 0,

    // checkbox, allow & deny icons
    chkImages : [],

    //Cached form variables
    SettingsInUsersAjax: null,

    // ASync callback method
    AjaxCallback : {
        AddUser: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateUser: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        AddUserToGroups: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
            }
        },

        UpdatePersonal: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
            }
        },

        UpdateContacts: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
            }
        },

        UpdateExtra: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
            }
        },

        UpdateMyAccount: function(response) {
            $('#pass1').val('');
            $('#pass2').val('');
        },

        DeleteUser: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopUserAction();
                $('#users_datagrid')[0].deleteItem();
                getDG('users_datagrid');
            }
        },

        AddGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groups_datagrid')[0].addItem();
                $('#groups_datagrid')[0].lastPage();
                getDG('groups_datagrid');
            }
        },

        UpdateGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
                getDG('groups_datagrid');
            }
        },

        DeleteGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groups_datagrid')[0].deleteItem();
                getDG('groups_datagrid');
            }
        },

        AddUsersToGroup: function(response) {
            if (response['type'] == 'alert-success') {
                this.stopGroupAction();
            }
        },

        DeleteSession: function(response) {
            if (response['type'] == 'alert-success') {
                clearTimeout(this.fTimeout);
                getDG('onlineusers_datagrid', $('#onlineusers_datagrid')[0].getCurrentPage(), true);
            }
        }

    },

    /**
     * Get users list
     */
    getUsers: function(name, offset, reset) {
        var result = this.gadget.ajax.callSync(
            'GetUsers', [
                $('#filter_group').val(),
                $('#filter_type').val(),
                $('#filter_status').val(),
                $('#filter_term').val(),
                $('#order_type').val(),
                offset
            ]
        );
        if (reset) {
            $('#' + name)[0].setCurrentPage(0);
            var total = this.gadget.ajax.callSync(
                'GetUsersCount', [
                    $('#filter_group').val(),
                    $('#filter_domain').val(),
                    $('#filter_type').val(),
                    $('#filter_status').val(),
                    $('#filter_term').val()
                ]
            );
        }
        resetGrid(name, result, total);
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
     * Search user function
     */
    searchUser: function() {
        this.getUsers('users_datagrid', 0, true);
    },

    /**
     * Get users list
     */
    getUsers: function(name, offset, reset) {
        var result = this.gadget.ajax.callSync(
            'GetUsers', [
                $('#filter_group').val(),
                $('#filter_domain').val(),
                $('#filter_type').val(),
                $('#filter_status').val(),
                $('#filter_term').val(),
                $('#order_type').val(),
                offset
            ]
        );
        if (reset) {
            $('#' + name)[0].setCurrentPage(0);
            var total = this.gadget.ajax.callSync(
                'GetUsersCount', [
                    $('#filter_group').val(),
                    $('#filter_domain').val(),
                    $('#filter_type').val(),
                    $('#filter_status').val(),
                    $('#filter_term').val()
                ]
            );
        }
        resetGrid(name, result, total);
    },

    /**
     * Get groups list
     */
    getGroups: function(name, offset, reset) {
        var result = this.gadget.ajax.callSync('GetGroups', offset);
        if (reset) {
            $('#' + name)[0].setCurrentPage(0);
            var total = this.gadget.ajax.callSync('getgroupscount');
        }
        resetGrid(name, result, total);
    },

    /**
     * Get online users list
     */
    getOnlineUsers: function(name, offset, reset) {
        var result = this.gadget.ajax.callSync(
            'GetOnlineUsers', {
                'offset': offset,
                'active': $('#filter_active').val(),
                'logged': $('#filter_logged').val(),
                'session_type': $('#filter_session_type').val()
            }
        );
        if (reset) {
            var total = this.gadget.ajax.callSync(
                'GetOnlineUsersCount', {
                    'active': $('#filter_active').val(),
                    'logged': $('#filter_logged').val(),
                    'session_type': $('#filter_session_type').val()
                }
            );
        }
        resetGrid(name, result, total);

        this.fTimeout = setTimeout(
            "Jaws_Gadget.getInstance('Users', 'OnlineUsers').getOnlineUsers('onlineusers_datagrid');",
            30000
        );
    },

    /**
     * Search online users
     */
    searchOnlineUsers: function() {
        clearTimeout(this.fTimeout);
        this.getOnlineUsers('onlineusers_datagrid', 0, true);
    },

    /**
     * Executes an action on Online User
     */
    onlineUsersDGAction: function(combo) {
        var rows = $('#onlineusers_datagrid')[0].getSelectedRows();
        if (rows.length < 1) {
            return;
        }

        if (combo.val() == 'delete') {
            if (confirm(this.gadget.defines.confirmThrowOut)) {
                this.gadget.ajax.callAsync('DeleteSession', rows);
            }
        } else if (combo.val() == 'block_ip') {
            if (confirm(this.gadget.defines.confirmBlockIP)) {
                this.gadget.ajax.callAsync('IPBlock', rows);
            }
        } else if (combo.val() == 'block_agent') {
            if (confirm(this.gadget.defines.confirmBlockAgent)) {
                this.gadget.ajax.callAsync('AgentBlock', rows);
            }
        }
    },

    /**
     * Saves users data / changes
     */
    saveUser: function() {
        switch (this.currentAction) {
            case 'UserAccount':
                if (!$('#username').val() ||
                    !$('#nickname').val() ||
                    (!$('#email').val() && !$('#mobile').val())
                ) {
                    alert(this.gadget.defines.LANGUAGE.incompleteUserFields);
                    return false;
                }

                var password = $('#password').val();
                $.loadScript('libraries/js/jsencrypt.min.js', function() {
                    if ($('#pubkey').length) {
                        var objRSACrypt = new JSEncrypt();
                        objRSACrypt.setPublicKey($('#pubkey').val());
                        password = objRSACrypt.encrypt($('#pass1').val());
                    }

                    if (this.selectedUser == 0) {
                        if (!$('#password').val()) {
                            alert(this.gadget.defines.LANGUAGE.incompleteUserFields);
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
                        this.gadget.ajax.callAsync('UpdateUser', {'uid':  $('#uid').val(), 'data': formData});
                    }
                }, this.gadget);

                break;

            case 'UserACL':
                if ($('#components').val() === '') {
                    return;
                }
                var acls = [];
                $.each($('#acl_form img[alt!="-1"]'), function(index, aclTag) {
                    var keys = $(aclTag).attr('id').split(':');
                    acls[index] = [keys[0], keys[1], $(aclTag).attr('alt')];
                });
                this.gadget.ajax.callAsync('UpdateUserACL', [this.selectedId, $('#components').val(), acls]);
                break;

            case 'UserGroups':
                var inputs  = $('#workarea input');
                var keys    = new Array();
                var counter = 0;
                for (var i = 0; i < inputs.length; i++) {
                    if (inputs[i].name.indexOf('user_groups') == -1) {
                        continue;
                    }

                    if (inputs[i].checked) {
                        keys[counter] = inputs[i].value;
                        counter++;
                    }
                }

                this.gadget.ajax.callAsync('AddUserToGroups', [$('#uid').val(), keys]);
                break;

            case 'UserPersonal':
                this.gadget.ajax.callAsync(
                    'UpdatePersonal', [
                        $('#uid').val(),
                        $('#fname').val(),
                        $('#lname').val(),
                        $('#gender').val(),
                        $('#ssn').val(),
                        $('#dob').val(),
                        $('#url').val(),
                        $('#about').val(),
                        $('#avatar').val(),
                        $('#privacy').val()
                    ]
                );
                break;

            case 'UserContacts':
                this.gadget.ajax.callAsync(
                    'UpdateContacts',
                    {
                        'uid': $('#uid').val(),
                        'data': $.unserialize($('form[name=contacts]').serialize())
                    }
                );
                break;

            case 'UserExtra':
                this.gadget.ajax.callAsync(
                    'UpdateExtra',
                    {
                        'uid': $('#uid').val(),
                        'data': $.unserialize($('form[name=extra]').serialize())
                    }
                );
                break;
        }

    },

    /**
     * Delete user
     */
    deleteUser: function(rowElement, uid) {
        this.stopUserAction();
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);
        if (confirm(this.gadget.defines.confirmUserDelete)) {
            this.gadget.ajax.callAsync('DeleteUser', uid);
        }
        unselectGridRow('users_datagrid');
    },

    /**
     * Delete group
     */
    deleteGroup: function(rowElement, gid) {
        this.stopGroupAction();
        selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
        if (confirm(this.gadget.defines.confirmGroupDelete)) {
            this.gadget.ajax.callAsync('DeleteGroup', gid);
        }
        unselectGridRow('groups_datagrid');
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
        $('#aclModal').modal('show');
    },

    /**
     * edit user/group ACL rules
     */
    editACL: function(rowElement, id, action) {
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);
        if (!this.cachedACLForm) {
            this.cachedACLForm = this.gadget.ajax.callSync('GetACLUI');
        }
        $('#workarea').html(this.cachedACLForm);
        $('#legend_title').html(this.gadget.defines.editACL_title);
        this.selectedId = id;
        this.currentAction = action;
        this.chkImages = $('#acl img').map(function() {
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
        function getValue(key, subkey) {
            var res = -1;
            $.each(acls.custom_acls, function (index, acl) {
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

        var form = $('#acl_form').html(''),
            acls = this.gadget.ajax.callSync(
                'GetACLKeys',
                [this.selectedId, $('#components').val(), this.currentAction]
            );

        $.each(acls.default_acls, $.proxy(function(index, acl) {
            var key_unique = acl.key_name + ':' + acl.key_subkey;
            var check = $('<img/>').attr('id', key_unique),
                label = $('<label></label>').attr('for', key_unique),
                div = $('<div></div>').append(check, label),
                value = getValue(acl.key_name, acl.key_subkey);

            label.html(acl.key_desc);
            check.attr('alt', value);
            check.attr('src', this.chkImages[value]);
            label.on('click', $.proxy(function (event) {
                var check = $(event.target).prev('img'),
                    value = parseInt(check.attr('alt'));
                check.attr('alt', (value == -1)? 1 : value - 1);
                check.attr('src', this.chkImages[check.attr('alt')]);
            }, this));
            check.on('click', $.proxy(function () {
                $(event.target).attr('alt', (event.target.alt == -1)? 1 : parseInt($(event.target).attr('alt')) - 1);
                $(event.target).attr('src', this.chkImages[$(event.target).attr('alt')]);
            }, this));
            form.append(div);
        }, this));
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

                    // $('#fname').val(uInfo['fname']);
                    // $('#lname').val(uInfo['lname']);
                    // $('#gender').val(Number(uInfo['gender']));
                    // $('#ssn').val(uInfo['ssn']);
                    // $('#dob').val(uInfo['dob']);
                    // $('#url').val(uInfo['url']);
                    // $('#about').val(uInfo['about']);
                    // $('#avatar').val('false');
                    // $('#image').attr('src', uInfo['avatar'] + '?' + (new Date()).getTime());
                    // $('#privacy').val(Number(uInfo['privacy']));

                    $('#personalModal').modal('show');
                }
            }
        );
    },

    /**
     * Edit user's contacts info
     */
    editContacts: function(rowElement, uid) {
        $('#uid').val(uid);
        this.currentAction = 'UserContacts';
        $('#legend_title').html(this.gadget.defines.editContacts_title);
        if (this.cachedContactsForm == null) {
            this.cachedContactsForm = this.gadget.ajax.callSync('ContactsUI');
        }
        $('#workarea').html(this.cachedContactsForm);
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var cInfo = this.gadget.ajax.callSync('GetUserContact', {'uid': uid});
        if (cInfo) {
            this.changeProvince(cInfo['province_home'], 'city_home');
            this.changeProvince(cInfo['province_work'], 'city_work');
            this.changeProvince(cInfo['province_other'], 'city_other');

            $('#contact-form input, #contact-form select, #contact-form textarea').each(
                function () {
                    $(this).val(cInfo[$(this).attr('name')]);
                }
            );
        }
    },

    /**
     * Edit user's extra attributes
     */
    editExtra: function(rowElement, uid) {
        $('#uid').val(uid);
        this.currentAction = 'UserExtra';
        $('#legend_title').html(this.gadget.defines.editExtra_title);
        if (this.cachedExtraForm == null) {
            this.cachedExtraForm = this.gadget.ajax.callSync('ExtraUI');
        }
        $('#workarea').html(this.cachedExtraForm);
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var exInfo = this.gadget.ajax.callSync('GetUserExtra', {'uid': uid});
        if (exInfo) {
            $('#extra-form input, #extra-form select, #extra-form textarea').each(
                function () {
                    $(this).val(exInfo[$(this).attr('name')]);
                }
            );
        }
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
     * Loads and sets the uploaded avatar
     */
    onUpload: function(response) {
        hideWorkingNotification();
        if (response.type === 'error') {
            alert(response.message);
            $('#frm_avatar')[0].reset();
        } else {
            var filename = response.message + '&' + (new Date()).getTime();
            $('#image').attr('src', this.gadget.ajax.baseScript + '?reqGadget=Users&reqAction=LoadAvatar&file=' + filename);
            $('#avatar').val(response.message);
        }
        $('#ifrm_upload').remove();
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
        // this.currentAction = 'UserAccount';
    },

    /**
     * Edit group
     */
    editGroup: function(rowElement, gid) {
        this.selectedId = gid;
        this.currentAction = 'Group';
        $('#legend_title').html(this.gadget.defines.editGroup_title);
        $('#workarea').html(this.cachedGroupForm);
        selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);

        var gInfo = this.gadget.ajax.callSync('GetGroup', gid);
        $('#name').val(gInfo['name']);
        $('#title').val(gInfo['title'].defilter());
        $('#description').val(gInfo['description'].defilter());
        $('#enabled').val(Number(gInfo['enabled']));
    },

    /**
     * Edit the members of group
     */
    editGroupUsers: function(rowElement, gid) {
        this.selectedId = gid;
        this.currentAction = 'GroupUsers';
        $('#legend_title').html(this.gadget.defines.editGroupUsers_title);
        if (this.cachedGroupUsersForm == null) {
            this.cachedGroupUsersForm = this.gadget.ajax.callSync('GroupUsersUI');
        }
        $('#workarea').html(this.cachedGroupUsersForm);
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var gUsers = this.gadget.ajax.callSync('GetGroupUsers', gid);
        $.each(gUsers, function(index, user) {
            if ($('#user_' + user['id']).length) {
                $('#user_' + user['id']).prop('checked', true);
            }
        });
    },

    /**
     * Saves data / changes on the group's form
     */
    saveGroup: function() {
        switch(this.currentAction) {
            case 'Group':
                if (!$('#name').val() || !$('#title').val()) {
                    alert(this.gadget.defines.incompleteGroupFields);
                    return false;
                }

                if (this.selectedId == 0) {
                    this.gadget.ajax.callAsync(
                        'AddGroup', [
                            $('#name').val(),
                            $('#title').val(),
                            $('#description').val(),
                            $('#enabled').val()
                        ]
                    );
                } else {
                    this.gadget.ajax.callAsync(
                        'UpdateGroup', [
                            this.selectedId,
                            $('#name').val(),
                            $('#title').val(),
                            $('#description').val(),
                            $('#enabled').val()
                        ]
                    );
                }

                break;

            case 'GroupACL':
                if ($('components').val() === '') {
                    return;
                }
                var acls = [];
                $.each($('#acl_form img[alt!="-1"]'), function(index, aclTag) {
                    var keys = $(aclTag).attr('id').split(':');
                    acls[index] = [keys[0], keys[1], $(aclTag).attr('alt')];
                });
                this.gadget.ajax.callAsync('UpdateGroupACL', [this.selectedId, $('#components').val(), acls]);
                break;

            case 'GroupUsers':
                var inputs  = $('#workarea input');
                var keys    = new Array();
                var counter = 0;
                for (var i=0; i<inputs.length; i++) {
                    if (inputs[i].name.indexOf('group_users') == -1) {
                        continue;
                    }

                    if (inputs[i].checked) {
                        keys[counter] = inputs[i].value;
                        counter++;
                    }
                }

                this.gadget.ajax.callAsync('AddUsersToGroup', [this.selectedId, keys]);
                break;
        }

    },

    /**
     * Stops doing a certain action
     */
    stopGroupAction: function() {
        this.selectedId = 0;
        this.currentAction = 'Group';
        unselectGridRow('groups_datagrid');
        $('#legend_title').html(this.gadget.defines.addGroup_title);
        $('#workarea').html(this.cachedGroupForm);
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
                    name: 'acl',
                    html: '<span class="glyphicon glyphicon-lock"></span> ' + this.gadget.defines.LANGUAGE.acl,
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
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        var ids = new Array();
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
     * Search groups and fill combo
     */
    searchGroupsAndFillCombo: function (comboElm) {
        this.ajax.callAsync(
            'GetGroups',
            {'filters': {'title': $(comboElm).find('>input').val()}, 'limit': 10},
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
            this.initiateUsersDG();

            $('#filter_group').combobox({
                'showOptionsOnKeypress': true,
                'noMatchesMessage': this.gadget.defines.noMatchesMessage
            }).combobox('enable')
                .find('>input').val('')
                .on('keyup.fu.combobox', $.proxy(function (evt, data) {
                    this.searchGroupsAndFillCombo($('#filter_group'));
                }, this));
            $("#filter_group").trigger('keyup.fu.combobox');

            $('#userModal').on('hidden.bs.modal', $.proxy(function (e) {
                this.stopUserAction()
            }, this));

            $('#components').on('click', $.proxy(function (e) {
                this.getACL();
            }, this));

            $('#btnSaveUser').on('click', $.proxy(function (e) {
                this.saveUser();
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
            this.currentAction = 'UserAccount';
            this.cachedGroupForm = $('#workarea').html();
            this.stopGroupAction();
            initDataGrid('groups_datagrid', this.gadget, this.getGroups);
        }

        // init ACLs action
        if (this.gadget.actions.indexOf('ACLs') >= 0) {
            this.currentAction = 'ACLs';
            this.initiateACLsTree();
        }

        // init online users action
        if (this.gadget.actions.indexOf('OnlineUsers') >= 0) {
            initDataGrid('onlineusers_datagrid', this.gadget, this.getOnlineUsers);
        }
    },

}};
