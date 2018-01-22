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

    // checkbox, allow & deny icons
    chkImages : [],

    //Cached form variables
    cachedPersonalForm : null,
    cachedContactsForm : null,
    cachedUserGroupsForm : null,
    cachedGroupUsersForm : null,
    cachedACLForm : null,
    cachedUserForm : '',
    cachedGroupForm : '',
    SettingsInUsersAjax: null,

    // ASync callback method
    AjaxCallback : {
        AddUser: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
                $('#users_datagrid')[0].addItem();
                $('#users_datagrid')[0].lastPage();
                getDG('users_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateUser: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
                getDG('users_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateUserACL: function(response) {
            this.gadget.ajax.showResponse(response);
        },

        AddUserToGroups: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdatePersonal: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateContacts: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateExtra: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateMyAccount: function(response) {
            $('#pass1').val('');
            $('#pass2').val('');
            this.gadget.ajax.showResponse(response);
        },

        DeleteUser: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopUserAction();
                $('#users_datagrid')[0].deleteItem();
                getDG('users_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        AddGroup: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groups_datagrid')[0].addItem();
                $('#groups_datagrid')[0].lastPage();
                getDG('groups_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateGroup: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopGroupAction();
                getDG('groups_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        DeleteGroup: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopGroupAction();
                $('#groups_datagrid')[0].deleteItem();
                getDG('groups_datagrid');
            }
            this.gadget.ajax.showResponse(response);
        },

        UpdateGroupACL: function(response) {
            this.gadget.ajax.showResponse(response);
        },

        AddUsersToGroup: function(response) {
            if (response[0]['type'] == 'alert-success') {
                this.stopGroupAction();
            }
            this.gadget.ajax.showResponse(response);
        },

        DeleteSession: function(response) {
            if (response[0]['type'] == 'alert-success') {
                clearTimeout(this.fTimeout);
                getDG('onlineusers_datagrid', $('#onlineusers_datagrid')[0].getCurrentPage(), true);
            }
            this.gadget.ajax.showResponse(response);
        },

        IPBlock: function(response) {
            this.gadget.ajax.showResponse(response);
        },

        AgentBlock: function(response) {
            this.gadget.ajax.showResponse(response);
        },

        UpdateSettings: function(response) {
            this.gadget.ajax.showResponse(response);
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
            "Jaws_Gadget.getInstance('Users').getOnlineUsers('onlineusers_datagrid');",
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
                if ($('#pass1').val() != $('#pass2').val()) {
                    alert(this.gadget.defines.wrongPassword);
                    return false;
                }

                if (!$('#username').val() ||
                    !$('#nickname').val() ||
                    (!$('#email').val() && !$('#mobile').val())
                ) {
                    alert(this.gadget.defines.incompleteUserFields);
                    return false;
                }

                var password = $('#pass1').val();
                $.loadScript('libraries/js/jsencrypt.min.js', function() {
                    if ($('#pubkey').length) {
                        var objRSACrypt = new JSEncrypt();
                        objRSACrypt.setPublicKey($('#pubkey').val());
                        password = objRSACrypt.encrypt($('#pass1').val());
                    }

                    if ($('#uid').val() == 0) {
                        if (!$('#pass1').val()) {
                            alert(this.gadget.defines.incompleteUserFields);
                            return false;
                        }

                        var formData = $.unserialize(
                            $('#users-form input, #users-form select,#users-form textarea').serialize()
                        );
                        formData['password'] = password;
                        delete formData['prev_status'];
                        delete formData['pass1'];
                        delete formData['pass2'];
                        delete formData['length'];
                        delete formData['modulus'];
                        delete formData['exponent'];
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
    editUser: function(rowElement, uid) {
        $('#uid').val(uid);
        this.currentAction = 'UserAccount';
        $('#legend_title').html(this.gadget.defines.editUser_title);
        $('#workarea').html(this.cachedUserForm);
        initDatePicker('expiry_date');
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var userInfo = this.gadget.ajax.callSync('GetUser', [uid, true]);
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
        $('#prev_status').val(userInfo['status']);
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
    editUserGroups: function(rowElement, uid) {
        $('#uid').val(uid);
        this.currentAction = 'UserGroups';
        $('#legend_title').html(this.gadget.defines.editUserGroups_title);
        if (this.cachedUserGroupsForm == null) {
            this.cachedUserGroupsForm = this.gadget.ajax.callSync('UserGroupsUI');
        }
        $('#workarea').html(this.cachedUserGroupsForm);
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var uGroups = this.gadget.ajax.callSync('GetUserGroups', uid);
        $.each(uGroups, function(index, gid) {
            if ($('#group_' + gid).length) {
                $('#group_' + gid).prop('checked', true);
            }
        });
    },

    /**
     * Edit user's personal information
     */
    editPersonal: function(rowElement, uid) {
        $('#uid').val(uid);
        this.currentAction = 'UserPersonal';
        $('#legend_title').html(this.gadget.defines.editPersonal_title);
        if (this.cachedPersonalForm == null) {
            this.cachedPersonalForm = this.gadget.ajax.callSync('PersonalUI');
        }
        $('#workarea').html(this.cachedPersonalForm);
        initDatePicker('dob');
        selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

        var uInfo = this.gadget.ajax.callSync('GetUser', [uid, false, true]);
        $('#fname').val(uInfo['fname']);
        $('#lname').val(uInfo['lname']);
        $('#gender').val(Number(uInfo['gender']));
        $('#ssn').val(uInfo['ssn']);
        $('#dob').val(uInfo['dob']);
        $('#url').val(uInfo['url']);
        $('#about').val(uInfo['about']);
        $('#avatar').val('false');
        $('#image').attr('src', uInfo['avatar']+ '?'+ (new Date()).getTime());
        $('#privacy').val(Number(uInfo['privacy']));
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
            changeProvince(cInfo['province_home'], 'city_home');
            changeProvince(cInfo['province_work'], 'city_work');
            changeProvince(cInfo['province_other'], 'city_other');

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
            $('#image').attr('src', this.gadget.ajax.baseScript + '?gadget=Users&action=LoadAvatar&file=' + filename);
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
        $('#uid').val(0);
        this.currentAction = 'UserAccount';
        unselectGridRow('users_datagrid');
        $('#legend_title').html(this.gadget.defines.addUser_title);
        $('#workarea').html(this.cachedUserForm);
        initDatePicker('expiry_date');
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
    submitLoginForm: function(form) {
        if ($('#usecrypt').prop('checked')) {
            $.loadScript('libraries/js/jsencrypt.min.js', function() {
                if (!$('#loginkey').length) {
                    var objRSACrypt = new JSEncrypt();
                    objRSACrypt.setPublicKey(form.pubkey.value);
                    form.password.value = objRSACrypt.encrypt(form.password.value);
                }
                form.submit();
            }, this.gadget);

            return false;
        }

        return true;
    },
    //-------------------------------
    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        this.SettingsInUsersAjax = new JawsAjax('Settings');

        // init login box action
        if (this.gadget.actions.indexOf('LoginBox') >= 0) {
            if ($('#loginkey').length) {
                $('#loginkey').focus();
            } else {
                $('#username').focus();
                $('#username').select();
            }
        }

        // init users action
        if (this.gadget.actions.indexOf('Users') >= 0) {
            this.cachedUserForm  = $('#workarea').html();
            $('#filter_term').val('');
            $('#filter_group').prop('selectedIndex', 0);
            $('#filter_type').prop('selectedIndex', 0);
            $('#filter_status').prop('selectedIndex', 0);
            $('#order_type').prop('selectedIndex', 0);
            this.currentAction = 'UserAccount';
            this.stopUserAction();
            initDataGrid('users_datagrid', this.gadget, this.getUsers);
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
