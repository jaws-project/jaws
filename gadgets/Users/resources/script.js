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
            _('users_datagrid').addItem();
            _('users_datagrid').lastPage();
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
        _('pass1').value = '';
        _('pass2').value = '';
        showResponse(response);
    },

    deleteuser: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopUserAction();
            _('users_datagrid').deleteItem();
            getDG('users_datagrid');
        }
        showResponse(response);
    },

    addgroup: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopGroupAction();
            _('groups_datagrid').addItem();
            _('groups_datagrid').lastPage();
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
            _('groups_datagrid').deleteItem();          
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
                                    _('filter_group').value,
                                    _('filter_type').value,
                                    _('filter_status').value,
                                    _('filter_term').value,
                                    _('order_type').value,
                                    offset);
    if (reset) {
        _(name).setCurrentPage(0);
        var total = UsersAjax.callSync('getuserscount',
                                       _('filter_group').value,
                                       _('filter_type').value,
                                       _('filter_status').value,
                                       _('filter_term').value);
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
        _(name).setCurrentPage(0);
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
            if (_('pass1').value != _('pass2').value) {
                alert(wrongPassword);
                return false;
            }

            if (_('username').value.blank() ||
                _('nickname').value.blank() ||
                _('email').value.blank())
            {
                alert(incompleteUserFields);
                return false;
            }

            if (_('exponent')) {
                setMaxDigits(128);
                var pub_key = new RSAPublicKey(_('exponent').value, _('modulus').value, 128);
                var password = encryptedString(pub_key, _('pass1').value);
            } else {
                var password = _('pass1').value;
            }

            if (_('uid').value == 0) {
                if (_('pass1').value.blank()) {
                    alert(incompleteUserFields);
                    return false;
                }

                UsersAjax.callAsync(
                    'adduser',
                    {'username': _('username').value,
                     'password': password,
                     'nickname': _('nickname').value,
                     'email'   : _('email').value,
                     'status'  : _('status').value,
                     'superadmin' : _('superadmin').value,
                     'concurrents': _('concurrents').value,
                     'expiry_date': _('expiry_date').value
                    }
                );
            } else {
                UsersAjax.callAsync(
                    'updateuser',
                    _('uid').value,
                    {'username': _('username').value,
                     'password': password,
                     'nickname': _('nickname').value,
                     'email'   : _('email').value,
                     'status'  : _('status').value,
                     'prev_status': _('prev_status').value,
                     'superadmin' : _('superadmin').value,
                     'concurrents': _('concurrents').value,
                     'expiry_date': _('expiry_date').value
                    }
                );
            }

            break;

        case 'UserACL':
            if (_('components').value === '') {
                return;
            }
            var acls = _('acl_form').getElements('img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('updateuseracl', selectedId, _('components').value, acls);
            break;

        case 'UserGroups':
            var inputs  = _('workarea').getElementsByTagName('input');
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

            UsersAjax.callAsync('addusertogroups', _('uid').value, keys);
            break;

        case 'UserPersonal':
            UsersAjax.callAsync('updatepersonal',
                                _('uid').value,
                                _('fname').value,
                                _('lname').value,
                                _('gender').value,
                                _('ssn').value,
                                _('dob').value,
                                _('url').value,
                                _('about').value,
                                _('avatar').value,
                                _('privacy').value);
            break;

        case 'UserPreferences':
            UsersAjax.callAsync('updatepreferences',
                                _('uid').value,
                                _('language').value,
                                _('theme').value,
                                _('editor').value,
                                _('timezone').value);
            break;

        case 'UserContacts':
            UsersAjax.callAsync('updatecontacts',
                                _('uid').value,
                                _('country').value,
                                _('city').value,
                                _('address').value,
                                _('postal_code').value,
                                _('phone_number').value,
                                _('mobile_number').value,
                                _('fax_number').value);
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
    _('uid').value = uid;
    currentAction = 'UserAccount';
    _('legend_title').innerHTML  = editUser_title;
    _('workarea').innerHTML = cachedUserForm;
    initDatePicker('expiry_date');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, true);
    _('username').value    = uInfo['username'];
    _('nickname').value    = uInfo['nickname'].defilter();
    _('email').value       = uInfo['email'];
    _('superadmin').value  = Number(uInfo['superadmin']);
    _('concurrents').value = uInfo['concurrents'];
    _('expiry_date').value = uInfo['expiry_date'];
    _('status').value      = uInfo['status'];
    _('prev_status').value = uInfo['status'];
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
    _('workarea').innerHTML = cachedACLForm;
    _('legend_title').innerHTML  = editACL_title;
    selectedId = id;
    currentAction = action;
    chkImages = _('acl').getElements('img').get('src');
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

    if (_('components').value === '') {
        _('acl_form').set('html', '');
        return;
    }

    var form = _('acl_form').set('html', ''),
        acls = UsersAjax.callSync(
            'getaclkeys',
            selectedId,
            _('components').value,
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
    _('uid').value = uid;
    currentAction = 'UserGroups';
    _('legend_title').innerHTML  = editUserGroups_title;
    if (cachedUserGroupsForm == null) {
        cachedUserGroupsForm = UsersAjax.callSync('usergroupsui');
    }
    _('workarea').innerHTML = cachedUserGroupsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uGroups = UsersAjax.callSync('getusergroups', uid);
    uGroups.each(function(gid, index) {
        if (_('group_' + gid)) {
            _('group_' + gid).checked = true;
        }
    });
}

/**
 * Edit user's personal information
 */
function editPersonal(rowElement, uid)
{
    _('uid').value = uid;
    currentAction = 'UserPersonal';
    _('legend_title').innerHTML  = editPersonal_title;
    if (cachedPersonalForm == null) {
        cachedPersonalForm = UsersAjax.callSync('personalui');
    }
    _('workarea').innerHTML = cachedPersonalForm;
    initDatePicker('dob');
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, true);
    _('fname').value   = uInfo['fname'];
    _('lname').value   = uInfo['lname'];
    _('gender').value  = Number(uInfo['gender']);
    _('ssn').value     = uInfo['ssn'];
    _('dob').value     = uInfo['dob'];
    _('url').value     = uInfo['url'];
    _('about').value   = uInfo['about'];
    _('avatar').value  = 'false';
    _('image').src     = uInfo['avatar']+ '?'+ (new Date()).getTime();
    _('privacy').value = Number(uInfo['privacy']);
}

/**
 * Edit user's preferences
 */
function editPreferences(rowElement, uid)
{
    _('uid').value = uid;
    currentAction = 'UserPreferences';
    _('legend_title').innerHTML  = editPreferences_title;
    if (cachedPreferencesForm == null) {
        cachedPreferencesForm = UsersAjax.callSync('preferencesui');
    }
    _('workarea').innerHTML = cachedPreferencesForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, false, true);
    _('language').value = uInfo['language'] == null? '-default-': uInfo['language'];
    _('theme').value    = uInfo['theme']    == null? '-default-': uInfo['theme'];
    _('editor').value   = uInfo['editor']   == null? '-default-': uInfo['editor'];
    _('timezone').value = uInfo['timezone'] == null? '-default-': uInfo['timezone'];
}

/**
 * Edit user's contacts info
 */
function editContacts(rowElement, uid)
{
    _('uid').value = uid;
    currentAction = 'UserContacts';
    _('legend_title').innerHTML  = editContacts_title;
    if (cachedContactsForm == null) {
        cachedContactsForm = UsersAjax.callSync('contactsui');
    }
    _('workarea').innerHTML = cachedContactsForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var uInfo = UsersAjax.callSync('getuser', uid, false, false, false, true);
    _('country').value          = uInfo['country'];
    _('city').value             = uInfo['city'];
    _('address').value          = uInfo['address'];
    _('postal_code').value      = uInfo['postal_code'];
    _('phone_number').value     = uInfo['phone_number'];
    _('mobile_number').value    = uInfo['mobile_number'];
    _('fax_number').value       = uInfo['fax_number'];
}

/**
 * Uploads the avatar
 */
function upload() {
    showWorkingNotification();
    var iframe = new Element('iframe', {id:'ifrm_upload', name:'ifrm_upload'});
    _('workarea').adopt(iframe);
    _('frm_avatar').submit();
}

/**
 * Loads and sets the uploaded avatar
 */
function onUpload(response) {
    hideWorkingNotification();
    if (response.type === 'error') {
        alert(response.message);
        _('frm_avatar').reset();
    } else {
        var filename = response.message + '&' + (new Date()).getTime();
        _('image').src = base_script + '?gadget=Users&action=LoadAvatar&file=' + filename;
        _('avatar').value = response.message;
    }
    _('ifrm_upload').destroy();
}

/**
 * Removes the avatar
 */
function removeAvatar() {
    _('avatar').value = '';
    _('frm_avatar').reset();
    _('image').src = 'gadgets/Users/images/photo128px.png';
}

/**
 * Stops doing a certain action
 */
function stopUserAction()
{
    _('uid').value = 0;
    currentAction = 'UserAccount';
    unselectGridRow('users_datagrid');
    _('legend_title').innerHTML  = addUser_title;
    _('workarea').innerHTML = cachedUserForm;
    initDatePicker('expiry_date');
}

/**
 * Edit group
 */
function editGroup(rowElement, gid)
{
    selectedId = gid;
    currentAction = 'Group';
    _('legend_title').innerHTML   = editGroup_title;
    _('workarea').innerHTML = cachedGroupForm;
    selectGridRow('groups_datagrid', rowElement.parentNode.parentNode);

    var gInfo = UsersAjax.callSync('getgroup', gid);
    _('name').value        = gInfo['name'];
    _('title').value       = gInfo['title'].defilter();
    _('description').value = gInfo['description'].defilter();
    _('enabled').value     = Number(gInfo['enabled']);
}

/**
 * Edit the members of group
 */
function editGroupUsers(rowElement, gid)
{
    selectedId = gid;
    currentAction = 'GroupUsers';
    _('legend_title').innerHTML  = editGroupUsers_title;
    if (cachedGroupUsersForm == null) {
        cachedGroupUsersForm = UsersAjax.callSync('groupusersui');
    }
    _('workarea').innerHTML = cachedGroupUsersForm;
    selectGridRow('users_datagrid', rowElement.parentNode.parentNode);

    var gUsers = UsersAjax.callSync('getgroupusers', gid);
    gUsers.each(function(user, index) {
        if (_('user_' + user['id'])) {
            _('user_' + user['id']).checked = true;
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
            if (_('name').value.blank() || _('title').value.blank()) {
                alert(incompleteGroupFields);
                return false;
            }

            if (selectedId == 0) {
                UsersAjax.callAsync('addgroup', 
                                    _('name').value,
                                    _('title').value,
                                    _('description').value,
                                    _('enabled').value);
            } else {
                UsersAjax.callAsync('updategroup',
                                    selectedId,
                                    _('name').value,
                                    _('title').value,
                                    _('description').value,
                                    _('enabled').value);
            }

            break;

        case 'GroupACL':
            if (_('components').value === '') {
                return;
            }
            var acls = _('acl_form').getElements('img[alt!=-1]').map(function (img) {
                var keys = img.id.split(':');
                return [keys[0], keys[1], img.alt];
            });
            UsersAjax.callAsync('updategroupacl', selectedId, _('components').value, acls);
            break;

        case 'GroupUsers':
            var inputs  = _('workarea').getElementsByTagName('input');
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
    _('legend_title').innerHTML   = addGroup_title;
    _('workarea').innerHTML = cachedGroupForm;
}

/**
 * Save settings
 */
function saveSettings()
{
    var method     = _('authtype').value;
    var anon       = _('anon_register').value;
    var repetitive = _('anon_repetitive_email').value;
    var act        = _('anon_activation').value;
    var group      = _('anon_group').value;
    var recover    = _('password_recovery').value;

    UsersAjax.callAsync('savesettings', method, anon, repetitive, act, group, recover);
}

/**
 * Update myAccount
 */
function updateMyAccount()
{
    if (_('pass1').value != _('pass2').value) {
        alert(wrongPassword);
        return false;
    }

    if (_('username').value.blank() ||
        _('nickname').value.blank() ||
        _('email').value.blank())
    {
        alert(incompleteUserFields);
        return false;
    }

    if (_('exponent')) {
        encryptedElement(_('pass1'), _('exponent').value, _('modulus').value, true, 128);
        _('pass2').value = _('pass1').value;
    }
    UsersAjax.callAsync(
        'updatemyaccount',
        _('uid').value,
        {'username': _('username').value,
         'password': _('pass1').value,
         'nickname': _('nickname').value,
         'email'   : _('email').value
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
