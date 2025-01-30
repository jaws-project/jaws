/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users() { return {
    // global properties
    currentAction: null,
    selectedUser: null,
    selectedGroup: null,
    selectedContact: 0,
    selectedBookmark: 0,
    selectedFriendGroup: 0,

    // ASync callback method
    AjaxCallback : {
        SaveContact: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        DeleteContacts: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        AddUser: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        UpdateUser: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        UpdateUserPassword: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        DeleteUser: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        AddUserToGroups: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        AddGlobalGroup: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        DeleteGlobalGroup: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        UpdateGlobalGroup: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        AddUsersToGroup: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        UpdateBookmark: function (response) {
            if (response.type == 'alert-success') {
                if (this.currentAction == "Bookmarks") {
                    $('#bookmarkModal').modal('hide');
                    this.stopAction();
                } else {
                    $('#bookmarkModal-' + response.data.gadget + '-' + response.data.action + '-' + response.data.reference).modal('hide');
                }
            }
        },

        DeleteBookmark: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        SaveFriendGroup: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
                $('#friendModal').modal('hide');
            }
        },

        DeleteFriendGroups: function (response) {
            if (response.type == 'alert-success') {
                this.stopAction();
            }
        },

        AddUsersToFriendGroup: function (response) {
            if (response.type == 'alert-success') {
                $('#friendMembersModal').modal('hide');
            }
        }
    },

    /**
     * stop Action
     */
    stopAction: function() {
        switch (this.currentAction) {
            case 'UserAccount':
                this.selectedUser = null;
                $('#userModal').modal('hide');
                $('form#users-form')[0].reset();
                $('#usersGrid').repeater('render', {clearInfinite: true,pageIncrement: null});
                break;
            case 'UserPassword':
                this.selectedUser = null;
                $('#passModal').modal('hide');
                $('form#password-form')[0].reset();
                break;
            case 'UserGroups':
                this.selectedUser = null;
                $('#userGroupsModal').modal('hide');
                $('form#users-groups-form')[0].reset();
                break;
            case 'UserGroups':
                this.selectedUser = null;
                $('#userGroupsModal').modal('hide');
                $('form#users-groups-form')[0].reset();
                break;
            case 'Group':
                this.selectedGroup = null;
                $('form#groups-form')[0].reset();
                $('#groupsModal').modal('hide');
                $('#groupsGrid').repeater('render');
                break;
            case 'GroupUsers':
                this.selectedGroup = null;
                $('#groupUsersModal').modal('hide');
                $('form#group-users-form')[0].reset();
                break;
            case 'UserContacts':
                this.selectedContact = 0;
                $('#contactModal').modal('hide');
                $('form#contacts-form')[0].reset();
                $('#contractsGrid').repeater('render');
                break;
            case 'Bookmarks':
                this.selectedBookmark = 0;
                $('bookmarkModal').modal('hide');
                $('form#bookmark-form')[0].reset();
                $('#bookmarksGrid').repeater('render');
                break;
            case 'FriendsGroups':
                this.selectedFriendGroup = 0;
                $('form#friends-form')[0].reset();
                $('#friendModalLabel').html(this.t('lbl_addFriend'));
                $('#friendsGrid').repeater('render');
                break;
        }
    },





    /**
     * Update contacts
     */
    updateContact: function()
    {
        this.ajax.call(
            'UpdateContact',
            $.unserialize($('fieldset#contact').serialize())
        );
        return false;
    },

    /**
     * Add or update the contact
     */
    saveContact: function()
    {
        this.ajax.call(
            'SaveContact', {
                cid: this.selectedContact,
                data: $.unserialize($('form#contacts-form').serialize())
            }
        );
    },

    /**
     * Delete contacts
     */
    deleteContacts: function(ids)
    {
        var confirmation = confirm(this.t('confirmDelete'));
        if (confirmation) {
            this.ajax.call('DeleteContacts', {'ids': ids});
        }
    },

    /**
     * Edit a contacts info
     */
    editContact: function(cid)
    {
        this.selectedContact = cid;
        $('#contactModalLabel').html(this.t('lbl_editContact'));
        var cInfo = this.ajax.call('GetContact', {'id': this.selectedContact}, false, {'async': false});
        if (cInfo) {
            this.initContactForm(cInfo);
            $('#contactModal').modal('show');
        }
    },

    /**
     * Initialize contact form
     */
    initContactForm: function(contact)
    {
        $('#country_home').val(contact.country_home);
        $('#country_work').val(contact.country_work);
        $('#country_other').val(contact.country_other);
        this.changeCountry(contact.country_home,  $('#province_home'));
        this.changeCountry(contact.country_work,  $('#province_work'));
        this.changeCountry(contact.country_other, $('#province_other'));
        $('#province_home').val(contact.province_home);
        $('#province_work').val(contact.province_work);
        $('#province_other').val(contact.province_other);
        this.changeProvince(contact.province_home,  $('#city_home'),  $('#country_home'));
        this.changeProvince(contact.province_work,  $('#city_work'),  $('#country_work'));
        this.changeProvince(contact.province_other, $('#city_other'), $('#country_other'));
        $('fieldset#contact .form-control').each(function () {
            $(this).val(contact[$(this).attr('name')]);
        });
    },

    /**
     * Update preferences
     */
    updatePreferences: function(form)
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

        this.ajax.call('UpdatePreferences', postData);
        return false;
    },


    /**
     * Edit a user
     */
    editUser: function(id)
    {
        this.currentAction = "UserAccount";
        this.selectedUser = id;
        $('#userModalLabel').html(this.t('editUser_title'));
        var userInfo = this.ajax.call('GetUser', {'id': this.selectedUser, 'account': true}, false, {'async': false});
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
    },

    /**
     * Edit a user
     */
    editPassword: function(id, username)
    {
        this.currentAction = "UserPassword";
        this.selectedUser = id;
        $('#passModalLabel').html(this.t('updatePassword_title'));
        $('#password-form #username').prop('disabled', true).val(username);

        $('#passModal').modal('show');
    },

    /**
     * Edit a group
     */
    editGroup: function(id)
    {
        this.currentAction = "Group";
        this.selectedGroup = id;
        var gInfo = this.ajax.call('GetGroup', {'id': this.selectedGroup}, false, {'async': false});
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
    },

    /**
     * Edit a user's group membership
     */
    editUserGroups: function(id)
    {
        this.selectedUser = id;
        this.currentAction = "UserGroups";

        $('#users-groups-form input[type=checkbox]').prop('checked', false);

        $('#userGroupsModalLabel').html(this.t('editUser_title'));
        var uGroups = this.ajax.call('GetUserGroups', {'uid': this.selectedUser}, false, {'async': false});
        if (uGroups) {
            $.each(uGroups, function(index, gid) {
                if ($('#users-groups-form #group_' + gid).length) {
                    $('#users-groups-form #group_' + gid).prop('checked', true);
                }
            });

            $('#userGroupsModal').modal('show');
        }
    },

    /**
     * Edit a group members
     */
    editGroupUsers: function(id)
    {
        this.currentAction = "GroupUsers";
        this.selectedGroup = id;

        $('#group-users-form input[type=checkbox]').prop('checked', false);

        var gUsers = this.ajax.call('GetGroupUsers', {'gid': this.selectedGroup}, false, {'async': false});
        if (gUsers) {
            $.each(gUsers, function(index, user) {
                if ($('#group-users-form #user_' + user['id']).length) {
                    $('#group-users-form #user_' + user['id']).prop('checked', true);
                }
            });
            $('#groupUsersModal').modal('show');
        }
    },

    /**
     * Saves users data / changes
     */
    saveUser: function()
    {
        switch (this.currentAction) {
            case 'UserAccount':
                if (!$('#users-form #username').val() ||
                    !$('#users-form #nickname').val() ||
                    (!$('#users-form #email').val() && !$('#users-form #mobile').val())
                ) {
                    alert(this.t('incompleteUserFields'));
                    return false;
                }

                if (this.selectedUser == null) {
                    if ($('#users-form #password').val().blank()) {
                        alert(this.t('wrongPassword'));
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
                        this.ajax.call('AddUser', {'data': formData});
                    });

                } else {
                    var formData = $.unserialize(
                        $('#users-form input, #users-form select, #users-form textarea').serialize()
                    );
                    delete formData['password'];
                    this.ajax.call('UpdateUser', {'uid': this.selectedUser, 'data': formData});
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
                this.ajax.call('AddUserToGroups', {'uid': this.selectedUser, 'groups': keys});
                break;

        }
    },

    /**
     * Update user password
     */
    updatePassword: function()
    {
        if ($('#password-form #password').val().blank()) {
            alert(this.t('wrongPassword'));
            return false;
        }

        var password = $('#password-form #password').val();
        $.loadScript('libraries/js/jsencrypt.min.js', function() {
            if ($('#pubkey').length) {
                var objRSACrypt = new JSEncrypt();
                objRSACrypt.setPublicKey($('#pubkey').val());
                password = objRSACrypt.encrypt($('#password-form #password').val());
            }

            this.ajax.call(
                'UpdateUserPassword',
                {
                    'uid': this.selectedUser,
                    'password': password,
                    'expired': $('#password-form #expired').prop('checked')
                }
            );
        });

    },

    /**
     * Saves group data / changes
     */
    saveGroup: function()
    {
        switch (this.currentAction) {
            case 'Group':
                if (!$('#groups-form #name').val() || !$('#groups-form #title').val()) {
                    alert(this.t('incompleteGroupFields'));
                    return false;
                }
                if (this.selectedGroup == null) {
                    var formData = $.unserialize($('#groups-form input,#groups-form select,#groups-form textarea').serialize());
                    this.ajax.call('AddGlobalGroup', {'data': formData});
                } else {
                    var formData = $.unserialize($('#groups-form input,#groups-form select,#groups-form textarea').serialize());
                    this.ajax.call('UpdateGlobalGroup', {'id': this.selectedGroup, 'data': formData});
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

                this.ajax.call('AddUsersToGroup', {'gid': this.selectedGroup, 'users': keys});
                break;

        }

    },

    /**
     * change country combo
     */
    changeCountry: function(country, elProvince)
    {
        elProvince.html('');
        var provinces = Jaws_Gadget.getInstance('Settings').ajax.call(
            'GetProvinces',
            {'country': country},
            false,
            {'async': false}
        );
        $.each(provinces, function (index, province) {
            elProvince.append('<option value="' + province.province + '">' + province.title + '</option>');
        });
    },

    /**
     * change province combo
     */
    changeProvince: function(province, elCity, elCountry)
    {
        elCity.html('');
        var cities = Jaws_Gadget.getInstance('Settings').ajax.call(
            'GetCities',
            {'province': province, 'country': elCountry.val()},
            false,
            {'async': false}
        );
        $.each(cities, function (index, city) {
            elCity.append('<option value="' + city.city + '">' + city.title + '</option>');
        });
    },

    /**
     * Delete an user
     */
    deleteUser: function(id)
    {
        if (confirm(this.t('confirmDelete'))) {
            this.ajax.call('DeleteUser', {'id': id});
        }
    },

    /**
     * Delete a global group
     */
    deleteGroup: function(id)
    {
        if (confirm(this.t('confirmDelete'))) {
            this.ajax.call('DeleteGlobalGroup', {'id': id});
        }
    },

    /**
     * Define the data to be displayed in the users datagrid
     */
    usersDataSource: function(options, callback) {
        var columns = {
            'nickname': {
                'label': this.t('lbl_nickname'),
                'property': 'nickname',
                'sortable': true
            },
            'username': {
                'label': this.t('lbl_username'),
                'property': 'username',
                'sortable': true
            }
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.ajax.call(
            'getUsers', {
                'offset': options.pageIndex * options.pageSize,
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
    },

    /**
     * initiate users datagrid
     */
    initiateUsersDG: function() {
        var list_actions = {
            width: 50,
            items: {
                'common': [
                    {
                        name: 'delete',
                        html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('deleteUser_title'),
                        clickAction: function (helpers, callback, e) {
                            e.preventDefault();
                            this.deleteUser(helpers.rowData.id);
                            callback();
                        }
                    },
                    {
                        name: 'userGroup',
                        html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('editUserGroups_title'),
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
                        html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.t('editUser_title'),
                        clickAction: function (helpers, callback, e) {
                            e.preventDefault();
                            editUser(helpers.rowData.id);
                            callback();
                        }

                    },
                    {
                        name: 'editPassword',
                        html: '<span class="glyphicon glyphicon-lock"></span> ' + this.t('updatePassword_title'),
                        clickAction: function (helpers, callback, e) {
                            e.preventDefault();
                            editPassword(helpers.rowData.id, helpers.rowData.username);
                            callback();
                        }
                    },
                    {
                        name: 'delete',
                        html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('deleteUser_title'),
                        clickAction: function (helpers, callback, e) {
                            e.preventDefault();
                            this.deleteUser(helpers.rowData.id);
                            callback();
                        }
                    },
                    {
                        name: 'userGroup',
                        html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('editUserGroups_title'),
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
            dataSource: this.usersDataSource,
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
            this.selectedUser = null;
            $("#password").prop('disabled', false);
        });
    },

    /**
     * Define the data to be displayed in the groups datagrid
     */
    groupsDataSource: function(options, callback) {

        // define the columns for the grid
        var columns = [
            {
                'label': this.t('lbl_title'),
                'property': 'title',
                'sortable': true
            },
            {
                'label': this.t('lbl_name'),
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

        var rows = this.ajax.call('GetGroups', {}, false, {'async': false});
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
    },

    /**
     * initiate groups datagrid
     */
    initiateGroupsDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'editUser',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.t('editGroup_title'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editGroup(helpers.rowData.id);
                        callback();
                    }

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('lbl_delete'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteGroup(helpers.rowData.id);
                        callback();
                    }
                },
                {
                    name: 'userGroup',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('editGroupUsers_title'),
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
            dataSource: this.groupsDataSource,
            staticHeight: 600,
            list_actions: list_actions,
            list_selectable: 'multi',
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $('#groupsModal').on('hidden.bs.modal', function (e) {
            $('form#groups-form')[0].reset();
            this.selectedGroup = null;
        });
    },

    /**
     * Upload VCard file
     */
    uploadVCardFile: function() {
        var $file = $('<input>', {type: 'file', name: 'file', 'multiple': false});
        $file.change(function () {
            var xhr = this.ajax.call(
                'ImportVCard',
                this.files[0],
                function (response, code) {
                    if (response.type == 'alert-success' && code == 200) {
                    }
                }
            );
        }).trigger('click');
    },

    // Define the data to be displayed in the repeater.
    contactsDataSource: function(options, callback) {
        var columns = {
            'title': {
                'label': this.t('lbl_title'),
                'property': 'title',
                'sortable': false
            },
            'name': {
                'label': this.t('lbl_name'),
                'property': 'name',
                'sortable': false
            }
        };

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.ajax.call(
            'GetContacts', {
                'search': options.search || '',
                'limit': options.pageSize,
                'offset': options.pageIndex * options.pageSize
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
    },

    /**
     * initiate contacts datagrid
     */
    initiateContactsDG: function() {

        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.t('lbl_edit'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        editContact(helpers.rowData.id);
                        callback();
                    }

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('lbl_delete') ,
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

                        this.deleteContacts(ids);
                        callback();
                    }
                }
            ]
        };

        $('#contractsGrid').repeater({
            staticHeight: 560,
            dataSource: this.contactsDataSource,
            list_actions: list_actions,
            list_selectable: 'multi',
            list_direction: $('.repeater-canvas').css('direction')
        });

        $('#contactModal').on('hidden.bs.modal', function (e) {
            stopAction();
        })

    },

    // Define the data to be displayed in the repeater.
    friendsDataSource: function(options, callback) {

        // define the columns for the grid
        var columns = [
            {
                'label': this.t('lbl_name'),
                'property': 'name',
                'sortable': true
            },
            {
                'label': this.t('lbl_title'),
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

        var rows = this.ajax.call('GetFriendGroups', options, false, {'async': false});

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
    },

    /**
     * initiate friends datagrid
     */
    initiateFriendsDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.t('lbl_edit'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        this.editFriendGroup(helpers.rowData.id);
                        callback();
                    }

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('lbl_delete') ,
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

                        this.deleteFriendGroups(ids);
                        callback();
                    }
                },
                {
                    name: 'editFriendMembers',
                    html: '<span class="glyphicon glyphicon-user"></span> ' + this.t('lbl_manageFriends'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        this.editFriendMembers(helpers.rowData.id);
                        callback();
                    }

                }
            ]
        };

        $('#friendsGrid').repeater({
            // setup your custom datasource to handle data retrieval;
            // responsible for any paging, sorting, filtering, searching logic
            dataSource: this.friendsDataSource,
            staticHeight: 600,
            list_actions: list_actions,
            list_selectable: 'multi',
            list_direction: $('.repeater-canvas').css('direction')
        });

        $('#friendModal').on('hidden.bs.modal', function (e) {
            stopAction();
        })
    },

    /**
     * Add or update the friend
     */
    saveFriendGroup: function()
    {
        this.ajax.call(
            'SaveFriendGroup', {
                id: this.selectedFriendGroup,
                data: $.unserialize($('form#friends-form').serialize())
            }
        );
    },

    /**
     * Edit a friend group info
     */
    editFriendGroup: function(id)
    {
        this.selectedFriendGroup = id;
        $('#friendModalLabel').html(this.t('lbl_editFriend'));
        var gInfo = this.ajax.call('GetFriendGroup', {'id': this.selectedFriendGroup}, false, {'async': false});
        if (gInfo) {
            $('#friends-form #name').val(gInfo.name);
            $('#friends-form #title').val(gInfo.title);
            $('#friends-form #description').val(gInfo.description);
            $('#friendModal').modal('show');
        }
    },

    /**
     * Delete friend groups
     */
    deleteFriendGroups: function(ids)
    {
        if (confirm(this.t('confirmDelete'))) {
            this.ajax.call('DeleteFriendGroups', {'ids': ids});
        }
    },

    /**
     * Edit a friend group info
     */
    editFriendMembers: function(id)
    {
        this.selectedFriendGroup = id;

        $('#friends-users-form input[type=checkbox]').prop('checked', false);

        var gUsers = this.ajax.call('GetGroupUsers', {'gid': this.selectedFriendGroup}, false, {'async': false});
        if (gUsers) {
            $.each(gUsers, function(index, user) {
                if ($('#friends-users-form #user_' + user['id']).length) {
                    $('#friends-users-form #user_' + user['id']).prop('checked', true);
                }
            });
            $('#friendMembersModal').modal('show');
        }
    },

    /**
     * save friend members
     */
    saveFriendMembers: function()
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

        this.ajax.call('AddUsersToFriendGroup', {'gid': this.selectedFriendGroup, 'users': keys});
    },

    /**
     * Define the data to be displayed in the users datagrid
     */
    bookmarksDataSource: function(options, callback) {

        // define the columns for the grid
        var columns = [
            {
                'label': this.t('lbl_gadget'),
                'property': 'gadget',
                'sortable': true
            },
            {
                'label': this.t('lbl_action'),
                'property': 'action',
                'sortable': true
            },
            {
                'label': this.t('lbl_title'),
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

        var rows = this.ajax.call('GetBookmarks', options, false, {'async': false});
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
    },

    /**
     * initiate users datagrid
     */
    initiateBookmarksDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.t('lbl_edit'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        this.editBookmark(helpers.rowData.id);
                        callback();
                    }

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.t('lbl_delete'),
                    clickAction: function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteBookmark(helpers.rowData.id);
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
    },

    /**
     * Edit a bookmark
     */
    editBookmark: function(id) {
        this.selectedBookmark = id;
        $('#bookmarkModalLabel').html(this.t('lbl_edit'));
        var bInfo = this.ajax.call('GetBookmark', {'id': this.selectedBookmark}, false, {'async': false});
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
    },

    /**
     * Delete a bookmark
     */
    deleteBookmark: function(id) {
        if (confirm(this.t('confirmDelete'))) {
            this.ajax.call('DeleteBookmark', {'id': id});
        }
    },

    /**
     * Open bookmark windows
     */
    openBookmarkWindows: function(gadget, action, reference, url) {
        var bookmarkUI = this.ajax.call(
            'BookmarkUI',
            {
                'bookmark_gadget': gadget,
                'bookmark_action': action,
                'bookmark_reference': reference
            },
            false,
            {'async': false}
        );
        $("#bookmark-dialog-" + gadget + '-' + action + '-' + reference).html(bookmarkUI);
        $('#bookmarkModal-'+ gadget + '-' + action + '-' + reference).modal();
    },

    /**
     * Save bookmark
     */
    saveBookmark: function(gadget, action, reference, url) {
        var formId = "#bookmark-form-" + gadget + '-' + action + '-' + reference;
        this.ajax.call(
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

    },

    /**
     * Update a bookmark
     */
    updateBookmark: function() {
        this.ajax.call(
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

    },

    /**
     *
     */
    encryptFormSubmit: function(form, elements)
    {
        if ($(form).find('[name="usecrypt"]').prop('checked') && (elements.length > 0) && form.pubkey) {
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
     *
     */
    initRegistration: function()
    {
        let $form = $('[name="user-registration-form"]');
        $form.on('submit', $.proxy(function (event) {
            let regstep = $form.find('[name="regstep"]').val();
            if (regstep && regstep == 1) {
                let $email = $form.find('[name="email"]');
                let $mobile = $form.find('[name="mobile"]');
                if ((!$email.length || $email.val().blank()) && (!$mobile.length || $mobile.val().blank())) {
                    event.preventDefault();
                    this.gadget.message.show(
                        {
                            'text': Jaws.t('error_incomplete_fields'),
                            'type': 'alert-danger'
                        }
                    );
                    return false;
                }

                let $username = $form.find('[name="username"]');
                if ($username.length && $username.attr('type') == 'hidden') {
                    if ($mobile.length && !$mobile.val().blank()) {
                        $username.val($mobile.val());
                    } else {
                        $username.val($email.val());
                    }
                }

                let $nickname = $form.find('[name="nickname"]');
                if ($nickname.length && $nickname.attr('type') == 'hidden') {
                    $nickname.val($username.val());
                }
            }

            return true;
        }, this));
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        // init registration
        if (this.gadget.actions.hasOwnProperty('Registration')) {
            this.initRegistration();
        }
        // init Users action
        if (this.gadget.actions.hasOwnProperty('Users')) {
            this.currentAction = "UserAccount";
            this.initiateUsersDG();
        }
        // init Groups action
        if (this.gadget.actions.hasOwnProperty('Groups')) {
            this.currentAction = "Group";
            this.initiateGroupsDG();
        }
        // init Bookmarks action
        if (this.gadget.actions.hasOwnProperty('Bookmarks')) {
            this.currentAction = "Bookmarks";
            this.initiateBookmarksDG();
        }
        // init Contact action
        if (this.gadget.actions.hasOwnProperty('Contact')) {
            this.currentAction = "UserContact";
            this.initContactForm(this.t('contact'));
        }
        // init Contacts action
        if (this.gadget.actions.hasOwnProperty('Contacts')) {
            this.currentAction = "UserContacts";
            this.initiateContactsDG();
        }
        // init FriendsGroups action
        if (this.gadget.actions.hasOwnProperty('FriendsGroups')) {
            this.currentAction = "FriendsGroups";
            this.initiateFriendsDG();
        }
        // init FriendsGroups action
        if (this.gadget.actions.hasOwnProperty('UserAttributes') || this.gadget.actions.hasOwnProperty('GroupAttributes')) {
            $('[data-field-type="country"]').change($.proxy(function (e, data) {
                this.changeCountry($(e.target).val(), $('[data-field-type="province"]').first());
            }, this));

            $('[data-field-type="province"]').change($.proxy(function (e, data) {
                this.changeProvince(
                    $(e.target).val(),
                    $('[data-field-type="city"]').first(),
                    $('[data-field-type="country"]').first());
            }, this));
        }
    },

}};
