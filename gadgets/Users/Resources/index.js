/**
 * Users Javascript front-end actions
 *
 * @category    Ajax
 * @package     Users
 */

/**
 * Use async mode, create Callback
 */
var UsersCallback = {
    UpdateContact: function (response) {
        UsersAjax.showResponse(response);
    },

    SaveContact: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    DeleteContacts: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },

    UpdatePreferences: function (response) {
        UsersAjax.showResponse(response);
    },

    AddUser: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    UpdateUser: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    DeleteUser: function (response) {
        if (response.type == 'alert-success') {
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
            $('#userModal').modal('hide');
            $('form#users-form')[0].reset();
            $('#usersGrid').repeater('render');
            break;
        case 'UserGroups':
            selectedUser = null;
            $('#userGroupsModal').modal('hide');
            $('form#users-groups-form')[0].reset();
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
        case 'UserContacts':
            selectedContact = 0;
            $('#contactModal').modal('hide');
            $('form#contacts-form')[0].reset();
            $('#contractsGrid').repeater('render');
            break;
    }
}

/**
 * Update contacts
 */
function updateContact()
{
    UsersAjax.callAsync(
        'UpdateContact',
        $.unserialize($('form[name=contacts]').serialize())
    );
}

/**
 * Add or update the contact
 */
function saveContact()
{
    UsersAjax.callAsync(
        'SaveContact', {
            cid: selectedContact,
            data: $.unserialize($('form#contacts-form').serialize())
        }
    );
}

/**
 * Delete contacts
 */
function deleteContacts(ids)
{
    var confirmation = confirm(confirmDelete);
    if (confirmation) {
        UsersAjax.callAsync('DeleteContacts', {'ids': ids});
    }
}

/**
 * Edit a contacts info
 */
function editContact(cid)
{
    selectedContact = cid;
    $('#contactModalLabel').html(lbl_editContact);
    var cInfo = UsersAjax.callSync('GetContact', {'id': selectedContact});
    if (cInfo) {
        changeProvince(cInfo['province'])

        $('#contacts-form input, #contacts-form select, #contacts-form textarea').each(
            function () {
                $(this).val(cInfo[$(this).attr('name')]);
            }
        );

        $('#contactModal').modal('show');
    }
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
    $('#userModalLabel').html(editUser_title);
    var userInfo = UsersAjax.callSync('GetUser', {'id': selectedUser, 'account': true});
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
        $('#userModal').modal('show');
    }
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
    $('#userGroupsModalLabel').html(editUser_title);
    var uGroups = UsersAjax.callSync('GetUserGroups', {'uid': selectedUser});
    if (uGroups) {
        $.each(uGroups, function(index, gid) {
            if ($('#users-groups-form #group_' + gid).length) {
                $('#users-groups-form #group_' + gid).prop('checked', true);
            }
        });

        $('#userGroupsModal').modal('show');
    }
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
            if ($('#users-form #pass1').val() != "" && $('#users-form #pass1').val() != $('#users-form #pass2').val()) {
                alert(wrongPassword);
                return false;
            }

            if (!$('#users-form #username').val() ||
                !$('#users-form #nickname').val() ||
                !$('#users-form #email').val())
            {
                alert(incompleteUserFields);
                return false;
            }

            if ($('#users-form #exponent').length) {
                setMaxDigits(256);
                var pub_key = new RSAKeyPair(
                    $('#users-form #exponent').val(),
                    '10001', $('#users-form #modulus').val(),
                    parseInt($('#users-form #length').val())
                );
                var password = encryptedString(pub_key, $('#users-form #pass1').val(), RSAAPP.PKCS1Padding);
            } else {
                var password = $('#users-form #pass1').val();
            }

            if (selectedUser == null) {
                if (!$('#users-form #pass1').val()) {
                    alert(incompleteUserFields);
                    return false;
                }

                var formData = $.unserialize($('#users-form input, #users-form select,#users-form textarea').serialize());
                formData['password'] = password;
                delete formData['prev_status'];
                delete formData['pass1'];
                delete formData['pass2'];
                UsersAjax.callAsync('AddUser', {'data': formData});
            } else {
                var formData = $.unserialize($('#users-form input, #users-form select, #users-form textarea').serialize());
                formData['password'] = password;
                delete formData['pass1'];
                delete formData['pass2'];
                UsersAjax.callAsync('UpdateUser', {'uid': selectedUser, 'data': formData});
            }

            break;

        case 'UserGroups':
            var inputs  = $('#users-groups-form input');
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
 * change province combo
 */
function changeProvince(province, cityElement)
{
    var cities = UsersAjax.callSync('GetCities', {'province': province});
    $('#' + cityElement ).html('');
    $.each(cities, function (index, city) {
        $("#" + cityElement).append('<option value="' + city.id + '">' + city.title + '</option>');
    });
}

/**
 * Delete an user
 */
function deleteUser(id)
{
    if (confirm(confirmDelete)) {
        UsersAjax.callAsync('DeleteUser', {'id': id});
    }
}

// Define the data to be displayed in the repeater.
function usersDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': lbl_nickname,
            'property': 'nickname',
            'sortable': true
        },
        {
            'label': lbl_username,
            'property': 'username',
            'sortable': true
        }
    ];

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var filters = {
        group: $('#filter_group').val(),
        type: $('#filter_type').val(),
        status: $('#filter_status').val(),
        term: $('#filter_term').val()
    };
    var options = {
        'offset': pageIndex,
        'limit': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filters': filters
    };

    var rows = UsersAjax.callSync('GetUsers', options);
    console.info(rows);

    var items = rows.records;
    var totalItems = rows.total;
    var totalPages = Math.ceil(totalItems / pageSize);
    var startIndex = (pageIndex * pageSize) + 1;
    var endIndex = (startIndex + pageSize) - 1;

    if(endIndex > items.length) {
        endIndex = items.length;
    }

    // configure datasource
    var dataSource = {
        'page':    pageIndex,
        'pages':   totalPages,
        'count':   totalItems,
        'start':   startIndex,
        'end':     endIndex,
        'columns': columns,
        'items':   items
    };

    // pass the datasource back to the repeater
    callback(dataSource);
}


var list_actions = {
    width: 50,
    items: [
        {
            name: 'editUser',
            html: '<span class="glyphicon glyphicon-pencil"></span> ' + editUser_title,
            clickAction: function (helpers, callback, e) {
                e.preventDefault();
                editUser(helpers.rowData.id);
                callback();
            }

        },
        {
            name: 'delete',
            html: '<span class="glyphicon glyphicon-trash"></span> ' + deleteUser_title,
            clickAction: function (helpers, callback, e) {
                e.preventDefault();
                deleteUser(helpers.rowData.id);
                callback();
            }
        },
        {
            name: 'userGroup',
            html: '<span class="glyphicon glyphicon-user"></span> ' + editUserGroups_title,
            clickAction: function (helpers, callback, e) {
                e.preventDefault();
                editUserGroups(helpers.rowData.id);
                callback();
            }
        }
    ]
};

/**
 * initiate users datagrid
 */
function initiateUsersDG() {
    // initialize the repeater
    var repeater = $('#usersGrid');
    repeater.repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: usersDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_selectable: 'multi'
    });

    $('#userModal').on('hidden.bs.modal', function (e) {
        $('form#users-form')[0].reset();
    });
}

/**
 * initiate users datagrid filters
 */
function initiateUsersDGFilters() {
    $( ".datagrid-filters select" ).change(function() {
        $('#usersGrid').repeater('render');
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#usersGrid').repeater('render');
        }
    });
}

/**
 * Initiates gadget
 */
$(document).ready(function () {
    initiateUsersDG();
    initiateUsersDGFilters();
});

var UsersAjax = new JawsAjax('Users', UsersCallback);
var currentAction, selectedUser, selectedGroup;