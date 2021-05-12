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
    SaveContact: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    DeleteContacts: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    AddUser: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    UpdateUser: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    UpdateUserPassword: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    DeleteUser: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    AddUserToGroups: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    AddGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    DeleteGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    UpdateGlobalGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    AddUsersToGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    UpdateBookmark: function (response) {
        if (response.type == 'alert-success') {
            if (currentAction == "Bookmarks") {
                $('#bookmarkModal').modal('hide');
                stopAction();
            } else {
                $('#bookmarkModal-' + response.data.gadget + '-' + response.data.action + '-' + response.data.reference).modal('hide');
            }
        }
    },

    DeleteBookmark: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    SaveFriendGroup: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#friendModal').modal('hide');
        }
    },

    DeleteFriendGroups: function (response) {
        if (response.type == 'alert-success') {
            stopAction();
        }
    },

    AddUsersToFriendGroup: function (response) {
        if (response.type == 'alert-success') {
            $('#friendMembersModal').modal('hide');
        }
    }

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
            $('#usersGrid').repeater('render', {clearInfinite: true,pageIncrement: null});
            break;
        case 'UserPassword':
            selectedUser = null;
            $('#passModal').modal('hide');
            $('form#password-form')[0].reset();
            break;
        case 'UserGroups':
            selectedUser = null;
            $('#userGroupsModal').modal('hide');
            $('form#users-groups-form')[0].reset();
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
        case 'Bookmarks':
            selectedBookmark = 0;
            $('bookmarkModal').modal('hide');
            $('form#bookmark-form')[0].reset();
            $('#bookmarksGrid').repeater('render');
            break;
        case 'FriendsGroups':
            selectedFriendGroup = 0;
            $('form#friends-form')[0].reset();
            $('#friendModalLabel').html(jaws.Users.Defines.lbl_addFriend);
            $('#friendsGrid').repeater('render');
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
        $.unserialize($('fieldset#contact').serialize())
    );
    return false;
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
    var confirmation = confirm(jaws.Users.Defines.confirmDelete);
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
    $('#contactModalLabel').html(jaws.Users.Defines.lbl_editContact);
    var cInfo = UsersAjax.callSync('GetContact', {'id': selectedContact});
    if (cInfo) {
        initContactForm(cInfo);
        $('#contactModal').modal('show');
    }
}

/**
 * Initialize contact form
 */
function initContactForm(contact)
{
    $('#country_home').val(contact.country_home);
    $('#country_work').val(contact.country_work);
    $('#country_other').val(contact.country_other);
    changeCountry(contact.country_home,  $('#province_home'));
    changeCountry(contact.country_work,  $('#province_work'));
    changeCountry(contact.country_other, $('#province_other'));
    $('#province_home').val(contact.province_home);
    $('#province_work').val(contact.province_work);
    $('#province_other').val(contact.province_other);
    changeProvince(contact.province_home,  $('#city_home'),  $('#country_home'));
    changeProvince(contact.province_work,  $('#city_work'),  $('#country_work'));
    changeProvince(contact.province_other, $('#city_other'), $('#country_other'));
    $('fieldset#contact .form-control').each(function () {
        $(this).val(contact[$(this).attr('name')]);
    });
}

/**
 * Update preferences
 */
function updatePreferences(form)
{
    var postData = $.unserialize($(form).serialize());
    delete postData.gadget;
    delete postData.action;

    $.each(postData, function (key, val) {
        switch (form.elements[key].type) {
            case 'checkbox':
                val = form.elements[key].checked;
                break;
            case 'number':
                val = parseFloat(val);
                break;
        }
        postData[key] = val;
    });

    UsersAjax.callAsync('UpdatePreferences', postData);
    return false;
}

/**
 * Edit a user
 */
function editUser(id)
{
    currentAction = "UserAccount";
    selectedUser = id;
    $('#userModalLabel').html(jaws.Users.Defines.editUser_title);
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
        $("#password").prop('disabled', true);
        $('#userModal').modal('show');
    }
}

/**
 * Edit a user
 */
function editPassword(id, username)
{
    currentAction = "UserPassword";
    selectedUser = id;
    $('#passModalLabel').html(jaws.Users.Defines.updatePassword_title);
    $('#password-form #username').prop('disabled', true).val(username);

    $('#passModal').modal('show');
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
    selectedUser = id;

    $('#users-groups-form input[type=checkbox]').prop('checked', false);

    $('#userGroupsModalLabel').html(jaws.Users.Defines.editUser_title);
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

    $('#group-users-form input[type=checkbox]').prop('checked', false);

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
            if (!$('#users-form #username').val() ||
                !$('#users-form #nickname').val() ||
                (!$('#users-form #email').val() && !$('#users-form #mobile').val())
            ) {
                alert(jaws.Users.Defines.incompleteUserFields);
                return false;
            }

            if (selectedUser == null) {
                if ($('#users-form #password').val().blank()) {
                    alert(jaws.Users.Defines.wrongPassword);
                    return false;
                }

                var password = $('#users-form #password').val();
                $.loadScript('libraries/js/jsencrypt.min.js', function() {
                    if ($('#pubkey').length) {
                        var objRSACrypt = new JSEncrypt();
                        objRSACrypt.setPublicKey($('#pubkey').val());
                        password = objRSACrypt.encrypt($('#users-form #password').val());
                    }

                    var formData = $.unserialize(
                        $('#users-form input, #users-form select,#users-form textarea').serialize()
                    );
                    formData['password'] = password;
                    delete formData['prev_status'];
                    UsersAjax.callAsync('AddUser', {'data': formData});
                });

            } else {
                var formData = $.unserialize(
                    $('#users-form input, #users-form select, #users-form textarea').serialize()
                );
                delete formData['password'];
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
 * Update user password
 */
function updatePassword()
{
    if ($('#password-form #password').val().blank()) {
        alert(jaws.Users.Defines.wrongPassword);
        return false;
    }

    var password = $('#password-form #password').val();
    $.loadScript('libraries/js/jsencrypt.min.js', function() {
        if ($('#pubkey').length) {
            var objRSACrypt = new JSEncrypt();
            objRSACrypt.setPublicKey($('#pubkey').val());
            password = objRSACrypt.encrypt($('#password-form #password').val());
        }

        UsersAjax.callAsync(
            'UpdateUserPassword',
            {
                'uid': selectedUser,
                'password': password,
                'expired': $('#password-form #expired').prop('checked')
            }
        );
    });

}

/**
 * Saves group data / changes
 */
function saveGroup()
{
    switch (currentAction) {
        case 'Group':
            if (!$('#groups-form #name').val() || !$('#groups-form #title').val()) {
                alert(jaws.Users.Defines.incompleteGroupFields);
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
 * change country combo
 */
function changeCountry(country, elProvince)
{
    elProvince.html('');
    var provinces = SettingsInUsersAjax.callSync('GetProvinces', {'country': country});
    $.each(provinces, function (index, province) {
        elProvince.append('<option value="' + province.province + '">' + province.title + '</option>');
    });
}

/**
 * change province combo
 */
function changeProvince(province, elCity, elCountry)
{
    elCity.html('');
    var cities = SettingsInUsersAjax.callSync(
        'GetCities',
        {'province': province, 'country': elCountry.val()}
    );
    $.each(cities, function (index, city) {
        elCity.append('<option value="' + city.city + '">' + city.title + '</option>');
    });
}

/**
 * Delete an user
 */
function deleteUser(id)
{
    if (confirm(jaws.Users.Defines.confirmDelete)) {
        UsersAjax.callAsync('DeleteUser', {'id': id});
    }
}

/**
 * Delete a global group
 */
function deleteGroup(id)
{
    if (confirm(jaws.Users.Defines.confirmDelete)) {
        UsersAjax.callAsync('DeleteGlobalGroup', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function usersDataSource(options, callback) {
    var columns = {
        'nickname': {
            'label': jaws.Users.Defines.lbl_nickname,
            'property': 'nickname',
            'sortable': true
        },
        'username': {
            'label': jaws.Users.Defines.lbl_username,
            'property': 'username',
            'sortable': true
        }
    };

    // set sort property & direction
    if (options.sortProperty) {
        columns[options.sortProperty].sortDirection = options.sortDirection;
    }
    columns = Object.values(columns);

    UsersAjax.callAsync(
        'GetUsers', {
            'offset': options.offset,
            'limit': options.pageSize,
            'sortDirection': options.sortDirection,
            'sortBy': options.sortProperty,
            'filters': {
                'group': $('#filter_group').val(),
                'type': $('#filter_type').val(),
                'status': $('#filter_status').val(),
                'term': $('#filter_term').val()
            }
        },
        function(response, status) {
            var dataSource = {};
            if (response['type'] == 'alert-success') {
                // processing end item index of page
                options.offset = options.pageIndex*options.pageSize;
                options.end = options.offset + options.pageSize;
                options.end = (options.end > response['data'].total)? response['data'].total : options.end;
                dataSource = {
                    'page': options.pageIndex,
                    'pages': Math.ceil(response['data'].total/options.pageSize),
                    'count': response['data'].total,
                    'start': options.offset + 1,
                    'end':   options.end,
                    'items': response['data'].records
                };
            } else {
                dataSource = {
                    'page': 0,
                    'pages': 0,
                    'count': 0,
                    'start': 0,
                    'end':   0,
                    'items': {}
                };
            }
            if (options.view !== 'thumbnail') {
                dataSource.columns = columns;
            }
            // pass the datasource back to the repeater
            callback(dataSource);
        }
    );
}

/**
 * initiate users datagrid
 */
function initiateUsersDG() {
    var list_actions = {
        width: 50,
        items: {
            'common': [
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.deleteUser_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        deleteUser(helpers.rowData.id);
                        callback();
                    }
                },
                {
                    name: 'userGroup',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.Users.Defines.editUserGroups_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editUserGroups(helpers.rowData.id);
                        callback();
                    }
                }
            ],
            'default': [
                {
                    name: 'editUser',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Users.Defines.editUser_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editUser(helpers.rowData.id);
                        callback();
                    }

                },
                {
                    name: 'editPassword',
                    html: '<span class="glyphicon glyphicon-lock"></span> ' + jaws.Users.Defines.updatePassword_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editPassword(helpers.rowData.id, helpers.rowData.username);
                        callback();
                    }
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.deleteUser_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        deleteUser(helpers.rowData.id);
                        callback();
                    }
                },
                {
                    name: 'userGroup',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.Users.Defines.editUserGroups_title,
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editUserGroups(helpers.rowData.id);
                        callback();
                    }
                }
            ]
        }
    };

    // initialize the repeater
    var repeater = $('#usersGrid');
    repeater.repeater({
        /*
        defaultView: 'thumbnail',
        defaultPageSize: 2,
        list_infiniteScroll: true,
        thumbnail_infiniteScroll: true,
        list_noItemsHTML: '',
        thumbnail_noItemsHTML: '',
        thumbnail_endItemsHTML: '',
        thumbnail_template: '<span>{{username}}</span><br/><span>{{nickname}}</span><br/><span>{{email}}</span>',
        */
        dataSource: usersDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction'),
        list_highlightSortedColumn: true
    });

    // monitor required events
    $( ".datagrid-filters select" ).change(function() {
        $('#usersGrid').repeater('render', {clearInfinite: true,pageIncrement: null});
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#usersGrid').repeater('render', {clearInfinite: true,pageIncrement: null});
        }
    });
    $('#userModal').on('hidden.bs.modal', function (e) {
        $('form#users-form')[0].reset();
        selectedUser = null;
        $("#password").prop('disabled', false);
    });
}

/**
 * Define the data to be displayed in the groups datagrid
 */
function groupsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.Users.Defines.lbl_title,
            'property': 'title',
            'sortable': true
        },
        {
            'label': jaws.Users.Defines.lbl_name,
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
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Users.Defines.editGroup_title,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editGroup(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteGroup(helpers.rowData.id);
                    callback();
                }
            },
            {
                name: 'userGroup',
                html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.Users.Defines.editGroupUsers_title,
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
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction')
    });

    // monitor required events
    $('#groupsModal').on('hidden.bs.modal', function (e) {
        $('form#groups-form')[0].reset();
        selectedGroup = null;
    });
}

/**
 * Upload VCard file
 */
function uploadVCardFile() {
    var $file = $('<input>', {type: 'file', name: 'file', 'multiple': false});
    $file.change(function () {
        var xhr = UsersAjax.uploadFile(
            'ImportVCard',
            this.files[0],
            function (response, code) {
                if (response.type == 'alert-success' && code == 200) {
                }
            },
            function (e) {
            }
        );
    }).trigger('click');
}


// Define the data to be displayed in the repeater.
function contactsDataSource(options, callback) {
    var columns = {
        'title': {
            'label': jaws.Users.Defines.lbl_title,
            'property': 'title',
            'sortable': false
        },
        'name': {
            'label': jaws.Users.Defines.lbl_name,
            'property': 'name',
            'sortable': false
        }
    };

    // set sort property & direction
    if (options.sortProperty) {
        columns[options.sortProperty].sortDirection = options.sortDirection;
    }
    columns = Object.values(columns);

    UsersAjax.callAsync(
        'GetContacts', {
            'search': options.search || '',
            'limit': options.pageSize,
            'offset': options.offset
        },
        function (response, status) {
            var dataSource = {};
            if (response['type'] == 'alert-success') {
                // processing end item index of page
                options.offset = options.pageIndex*options.pageSize;
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
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Users.Defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editContact(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.lbl_delete ,
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

    $('#contractsGrid').repeater({
        staticHeight: 560,
        dataSource: contactsDataSource,
        list_actions: list_actions,
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction')
    });

    $('#contactModal').on('hidden.bs.modal', function (e) {
        stopAction();
    })

}

// Define the data to be displayed in the repeater.
function friendsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.Users.Defines.lbl_name,
            'property': 'name',
            'sortable': true
        },
        {
            'label': jaws.Users.Defines.lbl_title,
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

    var rows = UsersAjax.callSync('GetFriendGroups', options);

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
 * initiate friends datagrid
 */
function initiateFriendsDG() {

    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Users.Defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editFriendGroup(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.lbl_delete ,
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

                    deleteFriendGroups(ids);
                    callback();
                }
            },
            {
                name: 'editFriendMembers',
                html: '<span class="glyphicon glyphicon-user"></span> ' + jaws.Users.Defines.lbl_manageFriends,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editFriendMembers(helpers.rowData.id);
                    callback();
                }

            }
        ]
    };

    $('#friendsGrid').repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: friendsDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction')
    });

    $('#friendModal').on('hidden.bs.modal', function (e) {
        stopAction();
    })
}

/**
 * Add or update the friend
 */
function saveFriendGroup()
{
    UsersAjax.callAsync(
        'SaveFriendGroup', {
            id: selectedFriendGroup,
            data: $.unserialize($('form#friends-form').serialize())
        }
    );
}

/**
 * Edit a friend group info
 */
function editFriendGroup(id)
{
    selectedFriendGroup = id;
    $('#friendModalLabel').html(jaws.Users.Defines.lbl_editFriend);
    var gInfo = UsersAjax.callSync('GetFriendGroup', {'id': selectedFriendGroup});
    if (gInfo) {
        $('#friends-form #name').val(gInfo.name);
        $('#friends-form #title').val(gInfo.title);
        $('#friends-form #description').val(gInfo.description);
        $('#friendModal').modal('show');
    }
}

/**
 * Delete friend groups
 */
function deleteFriendGroups(ids)
{
    if (confirm(jaws.Users.Defines.confirmDelete)) {
        UsersAjax.callAsync('DeleteFriendGroups', {'ids': ids});
    }
}

/**
 * Edit a friend group info
 */
function editFriendMembers(id)
{
    selectedFriendGroup = id;

    $('#friends-users-form input[type=checkbox]').prop('checked', false);

    var gUsers = UsersAjax.callSync('GetGroupUsers', {'gid': selectedFriendGroup});
    if (gUsers) {
        $.each(gUsers, function(index, user) {
            if ($('#friends-users-form #user_' + user['id']).length) {
                $('#friends-users-form #user_' + user['id']).prop('checked', true);
            }
        });
        $('#friendMembersModal').modal('show');
    }
}

/**
 * save friend members
 */
function saveFriendMembers()
{
    var inputs = $('#friends-users-form input');
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

    UsersAjax.callAsync('AddUsersToFriendGroup', {'gid': selectedFriendGroup, 'users': keys});
}


/**
 * Define the data to be displayed in the users datagrid
 */
function bookmarksDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.Users.Defines.lbl_gadget,
            'property': 'gadget',
            'sortable': true
        },
        {
            'label': jaws.Users.Defines.lbl_action,
            'property': 'action',
            'sortable': true
        },
        {
            'label': jaws.Users.Defines.lbl_title,
            'property': 'title',
            'sortable': true
        }
    ];

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var filters = {
        gadget: $('#filter_gadget').val(),
        term: $('#filter_term').val()
    };
    var options = {
        'offset': pageIndex,
        'limit': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filters': filters
    };

    var rows = UsersAjax.callSync('GetBookmarks', options);
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
function initiateBookmarksDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Users.Defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editBookmark(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Users.Defines.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteBookmark(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    // initialize the repeater
    var repeater = $('#bookmarksGrid');
    repeater.repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: bookmarksDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_direction: $('.repeater-canvas').css('direction')
    });

    // monitor required events
    $( ".datagrid-filters select" ).change(function() {
        $('#bookmarksGrid').repeater('render');
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#bookmarksGrid').repeater('render');
        }
    });
    $('#userModal').on('hidden.bs.modal', function (e) {
        $('form#bookmarks-form')[0].reset();
    });
}

/**
 * Edit a bookmark
 */
function editBookmark(id) {
    selectedBookmark = id;
    $('#bookmarkModalLabel').html(jaws.Users.Defines.lbl_edit);
    var bInfo = UsersAjax.callSync('GetBookmark', {'id': selectedBookmark});
    if (bInfo) {
        $('#bookmark-form input, #bookmark-form select, #bookmark-form textarea').each(
            function () {
                $(this).val(bInfo[$(this).attr('name')]);
            }
        );
        $('#bookmark-form #url').prop('href', bInfo['url']);
        $('#bookmark-form #url').html(bInfo['url']);
        $('#bookmarkModal').modal('show');
    }
}

/**
 * Delete a bookmark
 */
function deleteBookmark(id) {
    if (confirm(jaws.Users.Defines.confirmDelete)) {
        UsersAjax.callAsync('DeleteBookmark', {'id': id});
    }
}

/**
 * Open bookmark windows
 */
function openBookmarkWindows(gadget, action, reference, url) {
    var bookmarkUI = UsersAjax.callSync(
        'BookmarkUI',
        {
            'bookmark_gadget': gadget,
            'bookmark_action': action,
            'bookmark_reference': reference
        }
    );
    $("#bookmark-dialog-" + gadget + '-' + action + '-' + reference).html(bookmarkUI);
    $('#bookmarkModal-'+ gadget + '-' + action + '-' + reference).modal();
}

/**
 * Save bookmark
 */
function saveBookmark(gadget, action, reference, url) {
    var formId = "#bookmark-form-" + gadget + '-' + action + '-' + reference;
    UsersAjax.callAsync(
        'UpdateBookmark',
        {
            'bookmark_gadget': gadget,
            'bookmark_action': action,
            'bookmark_reference': reference,
            'url': url,
            'title': $(formId + ' #title').val(),
            'description': $(formId + ' #description').val(),
            'bookmarked': true
        }
    );

}

/**
 * Update a bookmark
 */
function updateBookmark() {
    UsersAjax.callAsync(
        'UpdateBookmark',
        {
            'bookmark_gadget': $('#bookmark-form #gadget').val(),
            'bookmark_action': $('#bookmark-form #action').val(),
            'bookmark_reference': $('#bookmark-form #reference').val(),
            'url': $('#bookmark-form #url').prop('href'),
            'title': $('#bookmark-form #title').val(),
            'description': $('#bookmark-form #description').val(),
            'bookmarked': true
        }
    );

}

/**
 *
 */
function encryptFormSubmit(form, elements)
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
}

$(document).ready(function() {
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

    switch (jaws.Defines.mainAction) {
        case 'Users':
            currentAction = "UserAccount";
            initiateUsersDG();
            break;

        case 'Groups':
            currentAction = "Group";
            initiateGroupsDG();
            break;

        case 'Bookmarks':
            currentAction = "Bookmarks";
            initiateBookmarksDG();
            break;

        case 'Contact':
            currentAction = "UserContact";
            initContactForm(jaws.Users.Defines.contact);
            break;

        case 'Contacts':
            currentAction = "UserContacts";
            initiateContactsDG();
            break;

        case 'FriendsGroups':
            currentAction = "FriendsGroups";
            initiateFriendsDG();
            break;

        case 'UserAttributes':
        case 'GroupAttributes':
            $('[data-field-type="country"]').change($.proxy(function (e, data) {
                changeCountry($(e.target).val(), $('[data-field-type="province"]').first());
            }, this));

            $('[data-field-type="province"]').change($.proxy(function (e, data) {
                changeProvince(
                    $(e.target).val(),
                    $('[data-field-type="city"]').first(),
                    $('[data-field-type="country"]').first());
            }, this));
            break;

    }
});

var UsersAjax = new JawsAjax('Users', UsersCallback);
var SettingsInUsersAjax = new JawsAjax('Settings');

var currentAction, selectedUser, selectedGroup;
var selectedContact = 0, selectedBookmark = 0, selectedFriendGroup = 0;