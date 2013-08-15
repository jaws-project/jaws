/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var UsersCallback = {
    adduser: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
            $('users_datagrid').addItem();
            $('users_datagrid').lastPage();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    updateuser: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    updateuseracl: function(response) {
        showResponse(response);
    },

    addusertogroups: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
        }
        showResponse(response);
    },

    updatepreferences: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
        }
        showResponse(response);
    },

    updatecontacts: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
        }
        showResponse(response);
    },

    updatepersonal: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
        }
        showResponse(response);
    },

    updatemyaccount: function(response) {
        $('pass1').value = '';
        $('pass2').value = '';
        showResponse(response);
    },

    deleteuser: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
            $('users_datagrid').deleteItem();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    addgroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopGroupAction();
            $('groups_datagrid').addItem();
            $('groups_datagrid').lastPage();
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    updategroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopGroupAction();
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    deletegroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopGroupAction();
            $('groups_datagrid').deleteItem();          
            getDG('groups_datagrid');
        }
        showResponse(response);
    },

    updategroupacl: function(response) {
        showResponse(response);
    },

    adduserstogroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopGroupAction();
        }
        showResponse(response);
    },

    deletesession: function(response) {
        if (response[0]['css'] == 'notice-message') {
            clearTimeout(fTimeout);
            getOnlineUsers('onlineusers_datagrid');
        }
        showResponse(response);
    },

    ipblock: function(response) {
        showResponse(response);
    },

    agentblock: function(response) {
        showResponse(response);
    },

    savesettings: function(response) {
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
    var result = UsersAjax.callSync('getusers',
                                    $('filter_group').value,
                                    $('filter_type').value,
                                    $('filter_status').value,
                                    $('filter_term').value,
                                    $('order_type').value,
                                    offset);
    if (reset) {
        $(name).setCurrentPage(0);
        var total = UsersAjax.callSync('getuserscount',
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
    var result = UsersAjax.callSync('getgroups', offset);
    if (reset) {
        $(name).setCurrentPage(0);
        var total = UsersAjax.callSync('getgroupscount');
    }
    resetGrid(name, result, total);
}

/**
 * Get online users list
 */
function getOnlineUsers(name, offset, reset)
{
    var result = UsersAjax.callSync('getonlineusers');
    resetGrid(name, result, result.length);
    fTimeout = setTimeout("getOnlineUsers('onlineusers_datagrid');", 30000);
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

            if ($('username').value.blank() ||
                $('nickname').value.blank() ||
                $('email').value.blank())
            {
                alert(incompleteUserFields);
                return false;
            }

            if ($('exponent')) {
                setMaxDigits(128);
                var pub_key = new RSAPublicKey($('exponent').value, $('modulus').value, 128);
                var password = encryptedString(pub_key, $('pass1').value);
            } else {
                var password = $('pass1').value;
            }

            if ($('uid').value == 0) {
                if ($('pass1').value.blank()) {
                    alert(incompleteUserFields);
                    return false;
                }

                UsersAjax.callAsync(
                    'adduser',
                    {'username': $('username').value,
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
                    'updateuser',
                    $('uid').value,
                    {'username': $('username').value,
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
            UsersAjax.callAsync('updateuseracl', selectedId, $('components').value, acls);
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

            UsersAjax.callAsync('addusertogroups', $('uid').value, keys);
            break;

        case 'UserPersonal':
            UsersAjax.callAsync('updatepersonal',
                                $('uid').value,
                                $('fname').value,
                                $('lname').value,
                                $('gender').value,
                                $('ssn').value,
                                $('dob').value,
                                $('url').value,
                                $('about').value,
                                $('avatar').value,
                                $('privacy').value);
            break;

        case 'UserPreferences':
            UsersAjax.callAsync('updatepreferences',
                                $('uid').value,
                                $('language').value,
                                $('theme').value,
                                $('editor').value,
                                $('timezone').value);
            break;

        case 'UserContacts':
            UsersAjax.callAsync('updatecontacts',
                                $('uid').value,
                                $('country').value,
                                $('city').value,
                                $('address').value,
                                $('postal_code').value,
                                $('phone_number').value,
                                $('mobile_number').value,
                                $('fax_number').value);
            break;
    }

}

/**
/**
 * Logout an user
 */
function deleteSession(rowElement, sid) {
    selectGridRow('onlineusers_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmThrowOut)) {
        UsersAjax.callAsync('deletesession', sid);
    }
    unselectGridRow('onlineusers_datagrid');
}

/**
 * User's IP block
 */
function ipBlock(rowElement, ip) {
    selectGridRow('onlineusers_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmBlockIP)) {
        UsersAjax.callAsync('ipblock', ip);
    }
    unselectGridRow('onlineusers_datagrid');
}

/**
 * User's Agent block
 */
function agentBlock(rowElement, agent) {
    selectGridRow('onlineusers_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmBlockAgent)) {
        UsersAjax.callAsync('agentblock', agent);
    }
    unselectGridRow('onlineusers_datagrid');
}

/**
 * Delete user
 */
function deleteUser(rowElement, uid)
{
    stopUserAction();
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);
    if (confirm(confirmUserDelete)) {
        UsersAjax.callAsync('deleteuser', uid);
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
        UsersAjax.callAsync('deletegroup', gid);
    }
    unselectGridRow('groups_datagrid');
}

/**
 * Save the group ACL keys
 */
function saveGroupACL()
{
    UsersAjax.callAsync('savegroupacl', selectedId, changedACLs);
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

    var uInfo = UsersAjax.callSync('getuser', uid, true);
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
        cachedACLForm = UsersAjax.callSync('getaclui');
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
            'getaclkeys',
            selectedId,
            $('components').value,
            currentAction
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
        cachedUserGroupsForm = UsersAjax.callSync('usergroupsui');
    }
    $('workarea').innerHTML = cachedUserGroupsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uGroups = UsersAjax.callSync('getusergroups', uid);
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
        cachedPersonalForm = UsersAjax.callSync('personalui');
    }
    $('workarea').innerHTML = cachedPersonalForm;
    initDatePicker('dob');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, true);
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
 * Edit user's preferences
 */
function editPreferences(rowElement, uid)
{
    $('uid').value = uid;
    currentAction = 'UserPreferences';
    $('legend_title').innerHTML  = editPreferences_title;
    if (cachedPreferencesForm == null) {
        cachedPreferencesForm = UsersAjax.callSync('preferencesui');
    }
    $('workarea').innerHTML = cachedPreferencesForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, false, true);
    $('language').value = uInfo['language'] == null? '-default-': uInfo['language'];
    $('theme').value    = uInfo['theme']    == null? '-default-': uInfo['theme'];
    $('editor').value   = uInfo['editor']   == null? '-default-': uInfo['editor'];
    $('timezone').value = uInfo['timezone'] == null? '-default-': uInfo['timezone'];
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
        cachedContactsForm = UsersAjax.callSync('contactsui');
    }
    $('workarea').innerHTML = cachedContactsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, false, false, true);
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
        $('image').src = base_script + '?gadget=Users&action=LoadAvatar&file=' + filename;
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
    $('image').src = 'gadgets/Users/images/avatar.png';
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

    var gInfo = UsersAjax.callSync('getgroup', gid);
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
        cachedGroupUsersForm = UsersAjax.callSync('groupusersui');
    }
    $('workarea').innerHTML = cachedGroupUsersForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var gUsers = UsersAjax.callSync('getgroupusers', gid);
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
            if ($('name').value.blank() || $('title').value.blank()) {
                alert(incompleteGroupFields);
                return false;
            }

            if (selectedId == 0) {
                UsersAjax.callAsync('addgroup', 
                                    $('name').value,
                                    $('title').value,
                                    $('description').value,
                                    $('enabled').value);
            } else {
                UsersAjax.callAsync('updategroup',
                                    selectedId,
                                    $('name').value,
                                    $('title').value,
                                    $('description').value,
                                    $('enabled').value);
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
            UsersAjax.callAsync('updategroupacl', selectedId, $('components').value, acls);
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

            UsersAjax.callAsync('adduserstogroup', selectedId, keys);
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
    var repetitive = $('anon_repetitive_email').value;
    var act        = $('anon_activation').value;
    var group      = $('anon_group').value;
    var recover    = $('password_recovery').value;

    UsersAjax.callAsync('savesettings', method, anon, repetitive, act, group, recover);
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

    if ($('username').value.blank() ||
        $('nickname').value.blank() ||
        $('email').value.blank())
    {
        alert(incompleteUserFields);
        return false;
    }

    if ($('exponent')) {
        encryptedElement($('pass1'), $('exponent').value, $('modulus').value, true, 128);
        $('pass2').value = $('pass1').value;
    }
    UsersAjax.callAsync(
        'updatemyaccount',
        $('uid').value,
        {'username': $('username').value,
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
    cachedPreferencesForm = null,
    cachedContactsForm = null,
    cachedUserGroupsForm = null,
    cachedGroupUsersForm = null,
    cachedACLForm = null;
