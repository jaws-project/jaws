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
            stopAction();
        }
        UsersAjax.showResponse(response);
    },
    DeleteGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
        UsersAjax.showResponse(response);
    },

    UpdateGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
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
            $('form#groups-form')[0].reset();
            $('#groupsModal').modal('hide');
            $('#groupsGrid').repeater('render');
            break;
        case 'GroupUsers':
            selectedGroup = null;
            $('#groupUsersModal').modal('hide');
            $('form#group-users-form')[0].reset();
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
    var confirmation = confirm(jaws.gadgets.Users.confirmDelete);
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
    $('#contactModalLabel').html(jaws.gadgets.Users.lbl_editContact);
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
 * Edit a user
 */
function editUser(id)
{
    currentAction = "UserAccount";
    selectedUser = id;
    $('#userModalLabel').html(jaws.gadgets.Users.editUser_title);
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
    if (gInfo) {
        $('#groups-form input, #groups-form select, #groups-form textarea').each(
            function () {
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
        $('#groupsModal').modal('show');
    }
}

/**
 * Edit a user's group membership
 */
function editUserGroups(id)
{
    currentAction = "UserGroups";
    selectedUser = id;
    $('#userGroupsModalLabel').html(jaws.gadgets.Users.editUser_title);
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
    if (gUsers) {
        $.each(gUsers, function(index, user) {
            if ($('#group-users-form #user_' + user['id']).length) {
                $('#group-users-form #user_' + user['id']).prop('checked', true);
            }
        });
        $('#groupUsersModal').modal('show');
    }
}

/**
 * Saves users data / changes
 */
function saveUser()
{
    switch (currentAction) {
        case 'UserAccount':
            if ($('#users-form #pass1').val() != "" && $('#users-form #pass1').val() != $('#users-form #pass2').val()) {
                alert(jaws.gadgets.Users.wrongPassword);
                return false;
            }

            if (!$('#users-form #username').val() ||
                !$('#users-form #nickname').val() ||
                !$('#users-form #email').val())
            {
                alert(jaws.gadgets.Users.incompleteUserFields);
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
                    alert(jaws.gadgets.Users.incompleteUserFields);
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
            if (!$('#groups-form #name').val() || !$('#groups-form #title').val()) {
                alert(jaws.gadgets.Users.incompleteGroupFields);
                return false;
            }
            if (selectedGroup == null) {
                var formData = $.unserialize($('#groups-form input,#groups-form select,#groups-form textarea').serialize());
                UsersAjax.callAsync('AddGlobalGroup', {'data': formData});
            } else {
                var formData = $.unserialize($('#groups-form input,#groups-form select,#groups-form textarea').serialize());
                UsersAjax.callAsync('UpdateGlobalGroup', {'id': selectedGroup, 'data': formData});
            }
            break;

        case 'GroupUsers':
            var inputs = $('#group-users-form input');
            var keys = new Array();
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
    var cities = SettingsInUsersAjax.callSync('GetCities', {'province': province});
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
    if (confirm(jaws.gadgets.Users.confirmDelete)) {
        UsersAjax.callAsync('DeleteUser', {'id': id});
    }
}

/**
 * Delete a global group
 */
function deleteGroup(id)
{
    if (confirm(jaws.gadgets.Users.confirmDelete)) {
        UsersAjax.callAsync('DeleteGlobalGroup', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function usersDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.gadgets.Users.lbl_nickname,
            'property': 'nickname',
            'sortable': true
        },
        {
            'label': jaws.gadgets.Users.lbl_username,
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

/**
 * initiate users datagrid
 */
function initiateUsersDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'editUser',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.gadgets.Users.editUser_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editUser(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.gadgets.Users.deleteUser_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteUser(helpers.rowData.id);
                    callback();
                }
            },
            {
                name: 'userGroup',
                html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.gadgets.Users.editUserGroups_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editUserGroups(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

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

    // monitor required events
    $( ".datagrid-filters select" ).change(function() {
        $('#usersGrid').repeater('render');
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#usersGrid').repeater('render');
        }
    });
    $('#userModal').on('hidden.bs.modal', function (e) {
        $('form#users-form')[0].reset();
    });
}

/**
 * Define the data to be displayed in the groups datagrid
 */
function groupsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.gadgets.Users.lbl_title,
            'property': 'title',
            'sortable': true
        },
        {
            'label': jaws.gadgets.Users.lbl_name,
            'property': 'name',
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

    var rows = UsersAjax.callSync('GetGroups');
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

/**
 * initiate groups datagrid
 */
function initiateGroupsDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'editUser',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.gadgets.editGroup_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editGroup(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.gadgets.Users.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteGroup(helpers.rowData.id);
                    callback();
                }
            },
            {
                name: 'userGroup',
                html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.gadgets.Users.editGroupUsers_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editGroupUsers(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    // initialize the repeater
    var repeater = $('#groupsGrid');
    repeater.repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: groupsDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_selectable: 'multi'
    });

    // monitor required events
    $('#groupsModal').on('hidden.bs.modal', function (e) {
        $('form#groups-form')[0].reset();
    });
}


// Define the data to be displayed in the repeater.
function contactsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.gadgets.Users.lbl_title,
            'property': 'title',
            'sortable': true
        }
    ];

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var options = {
        'pageIndex': pageIndex,
        'pageSize': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filterBy': options.filter.value || '',
        'searchBy': options.search || ''
    };

    var rows = UsersAjax.callSync('GetContacts', options);

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

/**
 * initiate contacts datagrid
 */
function initiateContactsDG() {

    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.gadgets.Users.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editContact(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.gadgets.Users.lbl_delete ,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();

                    // detect multi select
                    var ids = new Array();
                    if (helpers.length > 1) {
                        helpers.forEach(function(entry) {
                            ids.push(entry.rowData.id);
                        });

                    } else {
                        ids.push(helpers.rowData.id);
                    }

                    deleteContacts(ids);
                    callback();
                }
            }
        ]
    };

    var repeater = $('#contractsGrid');
    repeater.repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: contactsDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_selectable: 'multi'
    });

    $('#contactModal').on('hidden.bs.modal', function (e) {
        stopAction();
    })

}
$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Users':
            currentAction = "UserAccount";
            initiateUsersDG();
            break;

        case 'ManageGroups':
            currentAction = "Group";
            initiateGroupsDG();
            break;

        case 'Contacts':
            currentAction = "UserContacts";
            initiateContactsDG();
            break;
    }
});

var UsersAjax = new JawsAjax('Users', UsersCallback);
var SettingsInUsersAjax = new JawsAjax('Settings');

var currentAction, selectedUser, selectedGroup;
var selectedContact = 0;