/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var UsersCallback = {
    AddUser: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
            $('#users_datagrid')[0].addItem();
            $('#users_datagrid')[0].lastPage();
            getDG('users_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    UpdateUser: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
            getDG('users_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    UpdateUserACL: function(response) {
        UsersAjax.showResponse(response);
    },

    AddUserToGroups: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        UsersAjax.showResponse(response);
    },

    UpdateContacts: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        UsersAjax.showResponse(response);
    },

    UpdatePersonal: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        UsersAjax.showResponse(response);
    },

    UpdateMyAccount: function(response) {
        $('#pass1').val('');
        $('#pass2').val('');
        UsersAjax.showResponse(response);
    },

    DeleteUser: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
            $('#users_datagrid')[0].deleteItem();
            getDG('users_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    AddGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            $('#groups_datagrid')[0].addItem();
            $('#groups_datagrid')[0].lastPage();
            getDG('groups_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            getDG('groups_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    DeleteGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            $('#groups_datagrid')[0].deleteItem();
            getDG('groups_datagrid');
        }
        UsersAjax.showResponse(response);
    },

    UpdateGroupACL: function(response) {
        UsersAjax.showResponse(response);
    },

    AddUsersToGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
        }
        UsersAjax.showResponse(response);
    },

    DeleteSession: function(response) {
        if (response[0]['type'] == 'response_notice') {
            clearTimeout(fTimeout);
            getDG('onlineusers_datagrid', $('#onlineusers_datagrid')[0].getCurrentPage(), true);
        }
        UsersAjax.showResponse(response);
    },

    IPBlock: function(response) {
        UsersAjax.showResponse(response);
    },

    AgentBlock: function(response) {
        UsersAjax.showResponse(response);
    },

    SaveSettings: function(response) {
        UsersAjax.showResponse(response);
    }
}

/**
 * On term key press, for compatibility Opera/IE with other browsers
 */
function OnTermKeypress(element, event)
{
    if (event.keyCode == 13) {
        element.blur();
        element.focus();
    }
}

/**
 * Search user function
 */
function searchUser()
{
    getUsers('users_datagrid', 0, true);
}

/**
 * Get users list
 */
function getUsers(name, offset, reset)
{
    var result = UsersAjax.callSync(
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
        $(name)[0].setCurrentPage(0);
        var total = UsersAjax.callSync(
            'GetUsersCount', [
                $('#filter_group').val(),
                $('#filter_type').val(),
                $('#filter_status').val(),
                $('#filter_term').value
            ]
        );
    }
    resetGrid(name, result, total);
}

/**
 * Get groups list
 */
function getGroups(name, offset, reset)
{
    var result = UsersAjax.callSync('GetGroups', offset);
    if (reset) {
        $(name)[0].setCurrentPage(0);
        var total = UsersAjax.callSync('getgroupscount');
    }
    resetGrid(name, result, total);
}

/**
 * Get online users list
 */
function getOnlineUsers(name, offset, reset)
{
    var result = UsersAjax.callSync(
        'GetOnlineUsers', {
            'offset': offset,
            'active': $('#filter_active').val(),
            'logged': $('#filter_logged').val()
        }
    );
    if (reset) {
        var total = UsersAjax.callSync(
            'GetOnlineUsersCount', {
                'active': $('#filter_active').val(),
                'logged': $('#filter_logged').val()
            }
        );
    }
    resetGrid(name, result, total);

    fTimeout = setTimeout("getOnlineUsers('onlineusers_datagrid');", 30000);
}


/**
 * Search online users
 */
function searchOnlineUsers()
{
    clearTimeout(fTimeout);
    getOnlineUsers('onlineusers_datagrid', 0, true);
}


/**
 * Executes an action on Online User
 */
function onlineUsersDGAction(combo)
{
    var rows = $('#onlineusers_datagrid')[0].getSelectedRows();
    if (rows.length < 1) {
        return;
    }

    if (combo.value == 'delete') {
        if (confirm(confirmThrowOut)) {
            UsersAjax.callAsync('DeleteSession', rows);
        }
    } else if (combo.value == 'block_ip') {
        if (confirm(confirmBlockIP)) {
            UsersAjax.callAsync('IPBlock', rows);
        }
    } else if (combo.value == 'block_agent') {
        if (confirm(confirmBlockAgent)) {
            UsersAjax.callAsync('AgentBlock', rows);
        }
    }
}

/**
 * Saves users data / changes
 */
function saveUser()
{
    switch (currentAction) {
        case 'UserAccount':
            if ($('#pass1').val() != $('#pass2').val()) {
                alert(wrongPassword);
                return false;
            }

            if (!$('#username').val() ||
                !$('#nickname').val() ||
                !$('#email').val())
            {
                alert(incompleteUserFields);
                return false;
            }

            if ($('#exponent').length) {
                setMaxDigits(256);
                var pub_key = new RSAKeyPair(
                    $('#exponent').val(),
                    '10001', $('#modulus').val(),
                    parseInt($('#length').val())
                );
                var password = encryptedString(pub_key, $('#pass1').val(), RSAAPP.PKCS1Padding);
            } else {
                var password = $('#pass1').val();
            }

            if ($('#uid').val() == 0) {
                if (!$('#pass1').val()) {
                    alert(incompleteUserFields);
                    return false;
                }

                UsersAjax.callAsync(
                    'AddUser', {
                        'username': $('#username').val(),
                        'password': password,
                        'nickname': $('#nickname').val(),
                        'email'   : $('#email').val(),
                        'status'  : $('#status').val(),
                        'superadmin' : $('#superadmin').val(),
                        'concurrents': $('#concurrents').val(),
                        'expiry_date': $('#expiry_date').val()
                    }
                );
            } else {
                UsersAjax.callAsync(
                    'UpdateUser', {
                        'uid': $('#uid').val(),
                        'username': $('#username').val(),
                        'password': password,
                        'nickname': $('#nickname').val(),
                        'email'   : $('#email').val(),
                        'status'  : $('#status').val(),
                        'prev_status': $('#prev_status').val(),
                        'superadmin' : $('#superadmin').val(),
                        'concurrents': $('#concurrents').val(),
                        'expiry_date': $('#expiry_date').val()
                    }
                );
            }

            break;

        case 'UserACL':
            if ($('#components').val() === '') {
                return;
            }
            var acls = $('#acl_form img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('UpdateUserACL', [selectedId, $('#components').val(), acls]);
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

            UsersAjax.callAsync('AddUserToGroups', [$('#uid').val(), keys]);
            break;

        case 'UserPersonal':
            UsersAjax.callAsync(
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
            UsersAjax.callAsync(
                'UpdateContacts', [
                    $('#uid').val(),
                    $('#country').val(),
                    $('#city').val(),
                    $('#address').val(),
                    $('#postal_code').val(),
                    $('#phone_number').val(),
                    $('#mobile_number').val(),
                    $('#fax_number').val()
                ]
            );
            break;
    }

}

/**
 * Delete user
 */
function deleteUser(rowElement, uid)
{
    stopUserAction();
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmUserDelete)) {
        UsersAjax.callAsync('DeleteUser', uid);
    }
    unselectGridRow('users_datagrid');
}

/**
 * Delete group
 */
function deleteGroup(rowElement, gid)
{
    stopGroupAction();
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmGroupDelete)) {
        UsersAjax.callAsync('DeleteGroup', gid);
    }
    unselectGridRow('groups_datagrid');
}

/**
 * Edit user
 */
function editUser(rowElement, uid)
{
    $('#uid').val(uid);
    currentAction = 'UserAccount';
    $('#legend_title').html(editUser_title);
    $('#workarea').html(cachedUserForm);
    initDatePicker('expiry_date');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, true]);
    $('#username').val(uInfo['username']);
    $('#nickname').val(uInfo['nickname'].defilter());
    $('#email').val(uInfo['email']);
    $('#superadmin').val(Number(uInfo['superadmin']));
    $('#concurrents').val(uInfo['concurrents']);
    $('#expiry_date').val(uInfo['expiry_date']);
    $('#status').val(uInfo['status']);
    $('#prev_status').val(uInfo['status']);
}

/**
 * edit user/group ACL rules
 */
function editACL(rowElement, id, action)
{
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);
    if (!cachedACLForm) {
        cachedACLForm = UsersAjax.callSync('GetACLUI');
    }
    $('#workarea').html(cachedACLForm);
    $('#legend_title').html(editACL_title);
    selectedId = id;
    currentAction = action;
    chkImages = $('#acl img').attr('src');
    chkImages[-1] = chkImages[2];
    delete chkImages[2];
}

/**
 * Loads ACL data of the selected gadget/plugin
 */
function getACL()
{
    function getValue(key, subkey) {
        var res = -1;
        try {
            acls.custom_acls.each(function (acl) {
                if (acl.key_name === key && acl.key_subkey == subkey) {
                    res = acl.key_value;
                    //there is no way to break each() in MooTools
                    throw 'break';
                }
            });
        } catch (e) {
            if(e != 'break') throw e;
        }

        return res;
    }

    if ($('#components').val() === '') {
        $('#acl_form').html('');
        return;
    }

    var form = $('#acl_form').html(''),
        acls = UsersAjax.callSync(
            'GetACLKeys',
            [selectedId, $('#components').val(), currentAction]
        );
    acls.default_acls.each(function (acl) {
        var key_unique = acl.key_name + ':' + acl.key_subkey;
        var check = new Element('img', {id: key_unique}),
            label = new Element('label', {'for': key_unique}),
            div = new Element('div').adopt(check, label),
            value = getValue(acl.key_name, acl.key_subkey);
        label.set('html', acl.key_desc);
        check.set('alt', value);
        check.set('src', chkImages[value]);
        label.addEvent('click', function () {
            var check = this.getPrevious('img'),
                value = parseInt(check.alt);
            check.alt = (value == -1)? 1 : value - 1;
            check.src = chkImages[check.alt];
        });
        check.addEvent('click', function () {
            this.alt = (this.alt == -1)? 1 : parseInt(this.alt) - 1;
            this.src = chkImages[this.alt];
        });
        form.grab(div);
    });
}

/**
 * Edit the groups of user
 */
function editUserGroups(rowElement, uid)
{
    $('#uid').val(uid);
    currentAction = 'UserGroups';
    $('#legend_title').html(editUserGroups_title);
    if (cachedUserGroupsForm == null) {
        cachedUserGroupsForm = UsersAjax.callSync('UserGroupsUI');
    }
    $('#workarea').html(cachedUserGroupsForm);
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uGroups = UsersAjax.callSync('GetUserGroups', uid);
    uGroups.each(function(gid, index) {
        if ($('#group_' + gid).length) {
            $('#group_' + gid).checked = true;
        }
    });
}

/**
 * Edit user's personal information
 */
function editPersonal(rowElement, uid)
{
    $('#uid').val(uid);
    currentAction = 'UserPersonal';
    $('#legend_title').html(editPersonal_title);
    if (cachedPersonalForm == null) {
        cachedPersonalForm = UsersAjax.callSync('PersonalUI');
    }
    $('#workarea').html(cachedPersonalForm);
    initDatePicker('dob');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, false, true]);
    $('#fname').val(uInfo['fname']);
    $('#lname').val(uInfo['lname']);
    $('#gender').value  = Number(uInfo['gender']));
    $('#ssn').val(uInfo['ssn']);
    $('#dob').val(uInfo['dob']);
    $('#url').val(uInfo['url']);
    $('#about').val(uInfo['about']);
    $('#avatar').val('false');
    $('#image').attr('src', uInfo['avatar']+ '?'+ (new Date()).getTime());
    $('#privacy').val(Number(uInfo['privacy']));
}

/**
 * Edit user's contacts info
 */
function editContacts(rowElement, uid)
{
    $('#uid').val(uid);
    currentAction = 'UserContacts';
    $('#legend_title').html(editContacts_title);
    if (cachedContactsForm == null) {
        cachedContactsForm = UsersAjax.callSync('ContactsUI');
    }
    $('#workarea').html(cachedContactsForm);
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, false, false, true]);
    $('#country').val(uInfo['country']);
    $('#city').val(uInfo['city']);
    $('#address').val(uInfo['address']);
    $('#postal_code').val(uInfo['postal_code']);
    $('#phone_number').val(uInfo['phone_number']);
    $('#mobile_number').val(uInfo['mobile_number']);
    $('#fax_number').val(uInfo['fax_number']);
}

/**
 * Uploads the avatar
 */
function upload() {
    showWorkingNotification();
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    $('workarea').adopt(iframe);
    $('frm_avatar').submit();
}

/**
 * Loads and sets the uploaded avatar
 */
function onUpload(response) {
    hideWorkingNotification();
    if (response.type === 'error') {
        alert(response.message);
        $('frm_avatar').reset();
    } else {
        var filename = response.message + '&' + (new Date()).getTime();
        $('image').src = UsersAjax.baseScript + '?gadget=Users&action=LoadAvatar&file=' + filename;
        $('avatar').value = response.message;
    }
    $('ifrm_upload').destroy();
}

/**
 * Removes the avatar
 */
function removeAvatar() {
    $('avatar').value = '';
    $('frm_avatar').reset();
    $('image').src = 'gadgets/Users/Resources/images/photo128px.png';
}

/**
 * Stops doing a certain action
 */
function stopUserAction()
{
    $('#uid').val(0);
    currentAction = 'UserAccount';
    unselectGridRow('users_datagrid');
    $('#legend_title').html(addUser_title);
    $('#workarea').html(cachedUserForm);
    initDatePicker('expiry_date');
}

/**
 * Edit group
 */
function editGroup(rowElement, gid)
{
    selectedId = gid;
    currentAction = 'Group';
    $('#legend_title').html(editGroup_title);
    $('#workarea').html(cachedGroupForm);
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);

    var gInfo = UsersAjax.callSync('GetGroup', gid);
    $('name').value        = gInfo['name'];
    $('title').value       = gInfo['title'].defilter();
    $('description').value = gInfo['description'].defilter();
    $('enabled').value     = Number(gInfo['enabled']);
}

/**
 * Edit the members of group
 */
function editGroupUsers(rowElement, gid)
{
    selectedId = gid;
    currentAction = 'GroupUsers';
    $('#legend_title').html(editGroupUsers_title);
    if (cachedGroupUsersForm == null) {
        cachedGroupUsersForm = UsersAjax.callSync('GroupUsersUI');
    }
    $('#workarea').html(cachedGroupUsersForm);
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var gUsers = UsersAjax.callSync('GetGroupUsers', gid);
    gUsers.each(function(user, index) {
        if ($('#user_' + user['id']).length) {
            $('#user_' + user['id']).checked = true;
        }
    });
}

/**
 * Saves data / changes on the group's form
 */
function saveGroup()
{
    switch(currentAction) {
        case 'Group':
            if (!$('#name').val() || !$('#title').val()) {
                alert(incompleteGroupFields);
                return false;
            }

            if (selectedId == 0) {
                UsersAjax.callAsync(
                    'AddGroup', [
                        $('#name').val(),
                        $('#title').val(),
                        $('#description').val(),
                        $('#enabled').val()
                    ]
                );
            } else {
                UsersAjax.callAsync(
                    'UpdateGroup', [
                        selectedId,
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
            var acls = $('#acl_form img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('UpdateGroupACL', [selectedId, $('#components').val(), acls]);
            break;

        case 'GroupUsers':
            var inputs  = $('workarea').getElementsByTagName('input');
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

            UsersAjax.callAsync('AddUsersToGroup', [selectedId, keys]);
            break;
    }

}

/**
 * Stops doing a certain action
 */
function stopGroupAction()
{
    selectedId = 0;
    currentAction = 'Group';
    unselectGridRow('groups_datagrid');
    $('#legend_title').html(addGroup_title);
    $('#workarea').html(cachedGroupForm);
}

/**
 * Save settings
 */
function saveSettings()
{
    var method     = $('#authtype').val();
    var anon       = $('#anon_register').val();
    var act        = $('#anon_activation').val();
    var group      = $('#anon_group').val();
    var recover    = $('#password_recovery').val();

    UsersAjax.callAsync('SaveSettings', [method, anon, act, group, recover]);
}

/**
 * Update myAccount
 */
function updateMyAccount()
{
    if ($('#pass1').val() != $('#pass2').val()) {
        alert(wrongPassword);
        return false;
    }

    if (!$('#username').val() ||
        !$('#nickname').val() ||
        !$('#email').val())
    {
        alert(incompleteUserFields);
        return false;
    }

    if ($('exponent')) {
        encryptedElement(
            $('#pass1')[0],
            $('exponent').val(),
            $('#modulus').val(),
            true,
            $('#length').val()
        );
        $('#pass2').val() = $('#pass1').val();
    }
    UsersAjax.callAsync(
        'UpdateMyAccount',
        {'uid': $('uid').val(),
         'username': $('#username').val(),
         'password': $('#pass1').val(),
         'nickname': $('#nickname').val(),
         'email'   : $('#email').val()
        }
    );
}

var UsersAjax = new JawsAjax('Users', UsersCallback);

// timeout id
var fTimeout = null;
    
var currentAction = null;

// selected user/group ID
var selectedId = null;

// checkbox, allow & deny icons
var chkImages = [];

//Cached form variables
var cachedPersonalForm = null,
    cachedContactsForm = null,
    cachedUserGroupsForm = null,
    cachedGroupUsersForm = null,
    cachedACLForm = null;
