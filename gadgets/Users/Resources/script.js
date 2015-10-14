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
            $('users_datagrid')[0].addItem();
            $('users_datagrid').lastPage();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    UpdateUser: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    UpdateUserACL: function(response) {
        showResponse(response);
    },

    AddUserToGroups: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        showResponse(response);
    },

    UpdateContacts: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        showResponse(response);
    },

    UpdatePersonal: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
        }
        showResponse(response);
    },

    UpdateMyAccount: function(response) {
        $('pass1').value = '';
        $('pass2').value = '';
        showResponse(response);
    },

    DeleteUser: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopUserAction();
            $('users_datagrid')[0].deleteItem();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    AddGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            $('groups_datagrid')[0].addItem();
            $('groups_datagrid').lastPage();
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    UpdateGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    DeleteGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
            $('groups_datagrid')[0].deleteItem();          
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    UpdateGroupACL: function(response) {
        showResponse(response);
    },

    AddUsersToGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopGroupAction();
        }
        showResponse(response);
    },

    DeleteSession: function(response) {
        if (response[0]['type'] == 'response_notice') {
            clearTimeout(fTimeout);
            getDG('onlineusers_datagrid', $('onlineusers_datagrid')[0].getCurrentPage(), true);
        }
        showResponse(response);
    },

    IPBlock: function(response) {
        showResponse(response);
    },

    AgentBlock: function(response) {
        showResponse(response);
    },

    SaveSettings: function(response) {
        showResponse(response);
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
            $('filter_group').value,
            $('filter_type').value,
            $('filter_status').value,
            $('filter_term').value,
            $('order_type').value,
            offset
        ]
    );
    if (reset) {
        $(name)[0].setCurrentPage(0);
        var total = UsersAjax.callSync('GetUsersCount',
                                       $('filter_group').value,
                                       $('filter_type').value,
                                       $('filter_status').value,
                                       $('filter_term').value);
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
            'active': $('filter_active').value,
            'logged': $('filter_logged').value
        }
    );
    if (reset) {
        var total = UsersAjax.callSync(
            'GetOnlineUsersCount', {
                'active': $('filter_active').value,
                'logged': $('filter_logged').value
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
    var rows = $('onlineusers_datagrid')[0].getSelectedRows();
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
            if ($('pass1').value != $('pass2').value) {
                alert(wrongPassword);
                return false;
            }

            if (!$('username').val() ||
                !$('nickname').val() ||
                !$('email').val())
            {
                alert(incompleteUserFields);
                return false;
            }

            if ($('exponent')) {
                setMaxDigits(256);
                var pub_key = new RSAKeyPair(
                    $('exponent').value,
                    '10001', $('modulus').value,
                    parseInt($('length').value)
                );
                var password = encryptedString(pub_key, $('pass1').value, RSAAPP.PKCS1Padding);
            } else {
                var password = $('pass1').value;
            }

            if ($('uid').value == 0) {
                if (!$('pass1').val()) {
                    alert(incompleteUserFields);
                    return false;
                }

                UsersAjax.callAsync(
                    'AddUser', {
                        'username': $('username').value,
                        'password': password,
                        'nickname': $('nickname').value,
                        'email'   : $('email').value,
                        'status'  : $('status').value,
                        'superadmin' : $('superadmin').value,
                        'concurrents': $('concurrents').value,
                        'expiry_date': $('expiry_date').value
                    }
                );
            } else {
                UsersAjax.callAsync(
                    'UpdateUser', {
                        'uid': $('uid').value,
                        'username': $('username').value,
                        'password': password,
                        'nickname': $('nickname').value,
                        'email'   : $('email').value,
                        'status'  : $('status').value,
                        'prev_status': $('prev_status').value,
                        'superadmin' : $('superadmin').value,
                        'concurrents': $('concurrents').value,
                        'expiry_date': $('expiry_date').value
                    }
                );
            }

            break;

        case 'UserACL':
            if ($('components').value === '') {
                return;
            }
            var acls = $('acl_form').getElements('img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('UpdateUserACL', [selectedId, $('components').value, acls]);
            break;

        case 'UserGroups':
            var inputs  = $('workarea').getElementsByTagName('input');
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

            UsersAjax.callAsync('AddUserToGroups', [$('uid').value, keys]);
            break;

        case 'UserPersonal':
            UsersAjax.callAsync(
                'UpdatePersonal', [
                    $('uid').value,
                    $('fname').value,
                    $('lname').value,
                    $('gender').value,
                    $('ssn').value,
                    $('dob').value,
                    $('url').value,
                    $('about').value,
                    $('avatar').value,
                    $('privacy').value
                ]
            );
            break;

        case 'UserContacts':
            UsersAjax.callAsync(
                'UpdateContacts', [
                    $('uid').value,
                    $('country').value,
                    $('city').value,
                    $('address').value,
                    $('postal_code').value,
                    $('phone_number').value,
                    $('mobile_number').value,
                    $('fax_number').value
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
    $('uid').value = uid;
    currentAction = 'UserAccount';
    $('legend_title').innerHTML  = editUser_title;
    $('workarea').innerHTML = cachedUserForm;
    initDatePicker('expiry_date');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, true]);
    $('username').value    = uInfo['username'];
    $('nickname').value    = uInfo['nickname'].defilter();
    $('email').value       = uInfo['email'];
    $('superadmin').value  = Number(uInfo['superadmin']);
    $('concurrents').value = uInfo['concurrents'];
    $('expiry_date').value = uInfo['expiry_date'];
    $('status').value      = uInfo['status'];
    $('prev_status').value = uInfo['status'];
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
    $('workarea').innerHTML = cachedACLForm;
    $('legend_title').innerHTML  = editACL_title;
    selectedId = id;
    currentAction = action;
    chkImages = $('acl').getElements('img').get('src');
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

    if ($('components').value === '') {
        $('acl_form').set('html', '');
        return;
    }

    var form = $('acl_form').set('html', ''),
        acls = UsersAjax.callSync(
            'GetACLKeys',
            [selectedId, $('components').value, currentAction]
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
    $('uid').value = uid;
    currentAction = 'UserGroups';
    $('legend_title').innerHTML  = editUserGroups_title;
    if (cachedUserGroupsForm == null) {
        cachedUserGroupsForm = UsersAjax.callSync('UserGroupsUI');
    }
    $('workarea').innerHTML = cachedUserGroupsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uGroups = UsersAjax.callSync('GetUserGroups', uid);
    uGroups.each(function(gid, index) {
        if ($('group_' + gid)) {
            $('group_' + gid).checked = true;
        }
    });
}

/**
 * Edit user's personal information
 */
function editPersonal(rowElement, uid)
{
    $('uid').value = uid;
    currentAction = 'UserPersonal';
    $('legend_title').innerHTML  = editPersonal_title;
    if (cachedPersonalForm == null) {
        cachedPersonalForm = UsersAjax.callSync('PersonalUI');
    }
    $('workarea').innerHTML = cachedPersonalForm;
    initDatePicker('dob');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, false, true]);
    $('fname').value   = uInfo['fname'];
    $('lname').value   = uInfo['lname'];
    $('gender').value  = Number(uInfo['gender']);
    $('ssn').value     = uInfo['ssn'];
    $('dob').value     = uInfo['dob'];
    $('url').value     = uInfo['url'];
    $('about').value   = uInfo['about'];
    $('avatar').value  = 'false';
    $('image').src     = uInfo['avatar']+ '?'+ (new Date()).getTime();
    $('privacy').value = Number(uInfo['privacy']);
}

/**
 * Edit user's contacts info
 */
function editContacts(rowElement, uid)
{
    $('uid').value = uid;
    currentAction = 'UserContacts';
    $('legend_title').innerHTML  = editContacts_title;
    if (cachedContactsForm == null) {
        cachedContactsForm = UsersAjax.callSync('ContactsUI');
    }
    $('workarea').innerHTML = cachedContactsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('GetUser', [uid, false, false, true]);
    $('country').value          = uInfo['country'];
    $('city').value             = uInfo['city'];
    $('address').value          = uInfo['address'];
    $('postal_code').value      = uInfo['postal_code'];
    $('phone_number').value     = uInfo['phone_number'];
    $('mobile_number').value    = uInfo['mobile_number'];
    $('fax_number').value       = uInfo['fax_number'];
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
    $('uid').value = 0;
    currentAction = 'UserAccount';
    unselectGridRow('users_datagrid');
    $('legend_title').innerHTML  = addUser_title;
    $('workarea').innerHTML = cachedUserForm;
    initDatePicker('expiry_date');
}

/**
 * Edit group
 */
function editGroup(rowElement, gid)
{
    selectedId = gid;
    currentAction = 'Group';
    $('legend_title').innerHTML   = editGroup_title;
    $('workarea').innerHTML = cachedGroupForm;
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
    $('legend_title').innerHTML  = editGroupUsers_title;
    if (cachedGroupUsersForm == null) {
        cachedGroupUsersForm = UsersAjax.callSync('GroupUsersUI');
    }
    $('workarea').innerHTML = cachedGroupUsersForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var gUsers = UsersAjax.callSync('GetGroupUsers', gid);
    gUsers.each(function(user, index) {
        if ($('user_' + user['id'])) {
            $('user_' + user['id']).checked = true;
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
            if (!$('name').val() || !$('title').val()) {
                alert(incompleteGroupFields);
                return false;
            }

            if (selectedId == 0) {
                UsersAjax.callAsync(
                    'AddGroup', [
                        $('name').value,
                        $('title').value,
                        $('description').value,
                        $('enabled').value
                    ]
                );
            } else {
                UsersAjax.callAsync(
                    'UpdateGroup', [
                        selectedId,
                        $('name').value,
                        $('title').value,
                        $('description').value,
                        $('enabled').value
                    ]
                );
            }

            break;

        case 'GroupACL':
            if ($('components').value === '') {
                return;
            }
            var acls = $('acl_form').getElements('img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('UpdateGroupACL', [selectedId, $('components').value, acls]);
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
    $('legend_title').innerHTML   = addGroup_title;
    $('workarea').innerHTML = cachedGroupForm;
}

/**
 * Save settings
 */
function saveSettings()
{
    var method     = $('authtype').value;
    var anon       = $('anon_register').value;
    var act        = $('anon_activation').value;
    var group      = $('anon_group').value;
    var recover    = $('password_recovery').value;

    UsersAjax.callAsync('SaveSettings', [method, anon, act, group, recover]);
}

/**
 * Update myAccount
 */
function updateMyAccount()
{
    if ($('pass1').value != $('pass2').value) {
        alert(wrongPassword);
        return false;
    }

    if (!$('username').val() ||
        !$('nickname').val() ||
        !$('email').val())
    {
        alert(incompleteUserFields);
        return false;
    }

    if ($('exponent')) {
        encryptedElement(
            $('pass1'),
            $('exponent').value,
            $('modulus').value,
            true,
            $('length').value
        );
        $('pass2').value = $('pass1').value;
    }
    UsersAjax.callAsync(
        'UpdateMyAccount',
        {'uid': $('uid').value,
         'username': $('username').value,
         'password': $('pass1').value,
         'nickname': $('nickname').value,
         'email'   : $('email').value
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
