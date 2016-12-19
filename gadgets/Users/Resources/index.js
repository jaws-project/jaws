/**
 * Users Javascript front-end actions
 *
 * @category    Ajax
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Use async mode, create Callback
 */
var UsersCallback = {
    UpdateContacts: function(response) {
        UsersAjax.showResponse(response);
    },

    UpdatePreferences: function(response) {
        UsersAjax.showResponse(response);
    },

    AddUser: function (response) {
        if (response.type == 'alert-success') {
            w2popup.close();
            w2ui['users-grid'].reload();
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    UpdateUser: function (response) {
        if (response.type == 'alert-success') {
            w2popup.close();
            w2ui['users-grid'].reload();
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    AddUserToGroups: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    AddGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            w2popup.close();
            w2ui['groups-grid'].reload();
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    UpdateGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            w2popup.close();
            w2ui['groups-grid'].reload();
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    AddUsersToGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },


};

/**
 * stop Action
 */
function stopAction() {
    switch (currentAction) {
        case 'UserAccount':
            selectedUser = null;
            w2popup.close();
            $('form[name="user"]')[0].reset();
            break;
        case 'UserGroups':
            selectedUser = null;
            w2popup.close();
            $('form[name="user_groups"]')[0].reset();
            break;
        case 'Group':
            selectedGroup = null;
            w2popup.close();
            $('form[name="group"]')[0].reset();
            break;
        case 'GroupUsers':
            selectedGroup = null;
            w2popup.close();
            $('form[name="group_users"]')[0].reset();
            break;
    }
}

/**
 * Update contacts
 */
function updateContacts()
{
    var result = UsersAjax.callAsync(
        'UpdateContacts',
        $.unserialize($('form[name=contacts]').serialize())
    );
    return false;
}

/**
 * Update preferences
 */
function updatePreferences(form)
{
    var result = UsersAjax.callAsync(
        'UpdatePreferences',
        $.unserialize($(form).serialize())
    );
    return false;
}

/**
 * Add a user - UI
 */
function addUser()
{
    $('#user_workarea').w2popup({
        title: addUser_title,
        modal: true,
        width: 350,
        height: 350,
        onClose : function (event) {
            $('form[name="user"]')[0].reset();
        }
    });
}


/**
 * Add a group - UI
 */
function addGroup()
{
    $('#group_workarea').w2popup({
        title: addGroup_title,
        modal: true,
        width: 350,
        height: 350,
        onClose : function (event) {
            $('form[name="group"]')[0].reset();
        }
    });
}

/**
 * Edit a user
 */
function editUser(id)
{
    currentAction = "UserAccount";
    selectedUser = id;
    var userInfo = UsersAjax.callSync('GetUser', {'id': selectedUser, 'account': true});

    $('#user_workarea').w2popup({
        title: editUser_title,
        modal: true,
        width: 350,
        height: 350,
        onOpen: function (event) {
            event.onComplete = function () {
                if (userInfo) {
                    $('#w2ui-popup form input, #w2ui-popup form select, #w2ui-popup form textarea').each(
                        function () {
                            // select element
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
                            $('#w2ui-popup #prev_status').val(userInfo['status']);
                        }
                    );
                }
            };
        },
        onClose : function (event) {
            $('form[name="user"]')[0].reset();
        }
    });
}

/**
 * Edit a group
 */
function editGroup(id)
{
    currentAction = "Group";
    selectedGroup = id;
    var gInfo = UsersAjax.callSync('GetGroup', {'id': selectedGroup});

    $('#group_workarea').w2popup({
        title: editGroup_title,
        modal: true,
        width: 350,
        height: 350,
        onOpen: function (event) {
            event.onComplete = function () {
                if (gInfo) {
                    $('#w2ui-popup form input, #w2ui-popup form select, #w2ui-popup form textarea').each(
                        function () {
                            // select element
                            if ($(this).is('select')) {
                                if (gInfo[$(this).attr('name')] === true) {
                                    $(this).val('1');
                                } else if (gInfo[$(this).attr('name')] === false) {
                                    $(this).val('0');
                                } else {
                                    $(this).val(gInfo[$(this).attr('name')]);
                                }
                            } else {
                                $(this).val(gInfo[$(this).attr('name')]);
                            }
                        }
                    );
                }
            };
        },
        onClose : function (event) {
            $('form[name="group"]')[0].reset();
        }
    });
}

/**
 * Edit a user's group membership
 */
function editUserGroups(id)
{
    currentAction = "UserGroups";
    selectedUser = id;
    var uGroups = UsersAjax.callSync('GetUserGroups', {'uid': selectedUser});

    $('#user_groups_workarea').w2popup({
        title: editUserGroups_title,
        modal: true,
        width: 250,
        height: 400,
        onOpen: function (event) {
            event.onComplete = function () {
                if (uGroups) {
                    $.each(uGroups, function(index, gid) {
                        if ($('#w2ui-popup #group_' + gid).length) {
                            $('#w2ui-popup #group_' + gid).prop('checked', true);
                        }
                    });
                }
            };
        },
        onClose : function (event) {
            $('form[name="user_groups"]')[0].reset();
        }
    });
}

/**
 * Edit a group members
 */
function editGroupUsers(id)
{
    currentAction = "GroupUsers";
    selectedGroup = id;
    var gUsers = UsersAjax.callSync('GetGroupUsers', {'gid': selectedGroup});

    $('#group_users_workarea').w2popup({
        title: editGroupUsers_title,
        modal: true,
        width: 250,
        height: 400,
        onOpen: function (event) {
            event.onComplete = function () {
                if (gUsers) {
                    $.each(gUsers, function(index, user) {
                        if ($('#w2ui-popup #user_' + user['id']).length) {
                            $('#w2ui-popup #user_' + user['id']).prop('checked', true);
                        }
                    });
                }
            };
        },
        onClose : function (event) {
            $('form[name="group_users"]')[0].reset();
        }
    });
}

/**
 * Saves users data / changes
 */
function saveUser()
{
    switch (currentAction) {
        case 'UserAccount':
            if ($('#w2ui-popup #pass1').val() != $('#w2ui-popup #pass2').val()) {
                alert(wrongPassword);
                return false;
            }

            if (!$('#w2ui-popup #username').val() ||
                !$('#w2ui-popup #nickname').val() ||
                !$('#w2ui-popup #email').val())
            {
                alert(incompleteUserFields);
                return false;
            }

            if ($('#w2ui-popup #exponent').length) {
                setMaxDigits(256);
                var pub_key = new RSAKeyPair(
                    $('#w2ui-popup #exponent').val(),
                    '10001', $('#w2ui-popup #modulus').val(),
                    parseInt($('#w2ui-popup #length').val())
                );
                var password = encryptedString(pub_key, $('#w2ui-popup #pass1').val(), RSAAPP.PKCS1Padding);
            } else {
                var password = $('#w2ui-popup #pass1').val();
            }

            if (selectedUser == null) {
                if (!$('#w2ui-popup #pass1').val()) {
                    alert(incompleteUserFields);
                    return false;
                }

                var formData = $.unserialize($('#w2ui-popup input,select,textarea').serialize());
                formData['password'] = password;
                delete formData['prev_status'];
                delete formData['pass1'];
                delete formData['pass2'];
                UsersAjax.callAsync('AddUser', {'data': formData});
            } else {
                var formData = $.unserialize($('#w2ui-popup input,select,textarea').serialize());
                formData['password'] = password;
                delete formData['pass1'];
                delete formData['pass2'];
                UsersAjax.callAsync('UpdateUser', {'uid': selectedUser, 'data': formData});
            }

            break;

        case 'UserGroups':
            var inputs  = $('#w2ui-popup input');
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
            UsersAjax.callAsync('AddUserToGroups', {'uid': selectedUser, 'groups': keys});
            break;

    }

}

/**
 * Saves group data / changes
 */
function saveGroup()
{
    switch (currentAction) {
        case 'Group':
            if (!$('#w2ui-popup #name').val() || !$('#w2ui-popup #title').val()) {
                alert(incompleteGroupFields);
                return false;
            }

            if (selectedGroup == null) {
                var formData = $.unserialize($('#w2ui-popup input,select,textarea').serialize());
                UsersAjax.callAsync('AddGlobalGroup', {'data': formData});
            } else {
                var formData = $.unserialize($('#w2ui-popup input,select,textarea').serialize());
                UsersAjax.callAsync('UpdateGlobalGroup', {'id': selectedGroup, 'data': formData});
            }

            break;

        case 'GroupUsers':
            var inputs  = $('#w2ui-popup input');
            var keys    = new Array();
            var counter = 0;
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].name.indexOf('group_users') == -1) {
                    continue;
                }

                if (inputs[i].checked) {
                    keys[counter] = inputs[i].value;
                    counter++;
                }
            }

            UsersAjax.callAsync('AddUsersToGroup', {'gid': selectedGroup, 'users': keys});
            break;

    }

}

/**
 * Initiates gadget
 */
$(document).ready(function() {
    // set w2ui default configuration
    w2utils.settings.dataType = 'JSON';
    // load Persian translation
    w2utils.locale('libraries/w2ui/fa-pe.json');
});

var UsersAjax = new JawsAjax('Users', UsersCallback);
var currentAction, selectedUser, selectedGroup;