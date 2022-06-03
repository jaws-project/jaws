/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users() { return {
    searchTimer: null,

    // selected user/group ID

    // checkbox, allow & deny icons
    chkImages : [],

    //Cached form variables
    SettingsInUsersAjax: null,

    // ASync callback method
    AjaxCallback : {
        AddUserToGroup: function(response) {
            if (response.type === 'alert-success') {
                if ($('#group_combo').length) {
                    $('#group_combo').find('>input').val('');
                    $('#user-groups-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                } else {
                    $('#user_combo').find('>input').val('');
                    $('#group-users-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            }
        },
    },

    /**
     * Add an user to a group
     */
    addUserToGroup: function (uid, gid) {
        this.gadget.ajax.callAsync('AddUserToGroup', {
            'uid': uid,
            'gid': gid
        });
    },

    /**
     * Loads ACL data of the selected gadget/plugin
     */
    getACL: function(id, action) {
        function getValue(custom_acls, key, subkey) {
            var res = -1;
            $.each(custom_acls, function (index, acl) {
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

        this.ajax.callAsync('GetACLKeys', {
                'id': id,
                'comp': $('#components').val(),
                'action': action
            }, function (response, status, callOptions) {
                if (response.type === 'alert-success') {
                    var acls = response.data;
                    var form = $('#acl_form').html('');
                    $.each(acls.default_acls, $.proxy(function (index, acl) {
                        var key_unique = acl.key_name + ':' + acl.key_subkey;
                        var check = $('<img/>').attr('id', key_unique),
                            label = $('<label></label>').attr('for', key_unique),
                            div = $('<div></div>').append(check, label),
                            value = getValue(acls.custom_acls, acl.key_name, acl.key_subkey);

                        label.html(acl.key_desc);
                        check.attr('alt', value);
                        check.attr('src', this.chkImages[value]);
                        label.on('click', $.proxy(function (event) {
                            var check = $(event.target).prev('img'),
                                value = parseInt(check.attr('alt'));
                            check.attr('alt', (value == -1) ? 1 : value - 1);
                            check.attr('src', this.chkImages[check.attr('alt')]);
                        }, this));
                        check.on('click', $.proxy(function () {
                            $(event.target).attr('alt', (event.target.alt == -1) ? 1 : parseInt($(event.target).attr('alt')) - 1);
                            $(event.target).attr('src', this.chkImages[$(event.target).attr('alt')]);
                        }, this));
                        form.append(div);
                    }, this));
                }
            }
        );
    },

    /**
     * Cancel select combobox
     */
    cancelSelectCombobox: function(comboElement) {
        $(comboElement).find('div.input-group-btn ul.dropdown-menu').html('');
        $(comboElement).find('>input').val('');
        $(comboElement).combobox('enable').combobox('selectByIndex', '0');
        $(comboElement).find('>input').val('');
        $(comboElement).trigger('keyup.fu.combobox');
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
     *
     */
    encryptFormSubmit: function(form, elements) {
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
    },

    /**
     * Add option to combo box
     */
    addOptionToCombo: function (comboElement, data, emptyCombo = false) {
        if (emptyCombo) {
            $(comboElement).find('div.input-group-btn ul.dropdown-menu').html('');
        }
        $(comboElement).find('div.input-group-btn ul.dropdown-menu').append(
            '<li data-value="' + data.value + '"><a href="#">' + data.title + '</a></li>'
        );
    },

    /**
     * Search users and fill combo
     */
    searchUsersAndFillCombo: function (comboElm) {
        this.ajax.callAsync(
            'GetUsers',
            {'filters': {'filter_term': $(comboElm).find('>input').val()}, 'limit': 10},
            $.proxy(function (response, status) {
                $(comboElm).find('div.input-group-btn ul.dropdown-menu').html('');
                if (response.type === 'alert-success' && response.data.total > 0) {
                    $.each(response.data.records, $.proxy(function (key, user) {
                        this.addOptionToCombo(comboElm, {'value': user.id, 'title': user.nickname});
                    }, this));
                }
            }, this)
        );
    },

    /**
     * Search groups and fill combo
     */
    searchGroupsAndFillCombo: function (comboElm) {
        this.ajax.callAsync(
            'GetGroups',
            {'filters': {'filter_term': $(comboElm).find('>input').val()}, 'limit': 10},
            $.proxy(function (response, status) {
                $(comboElm).find('div.input-group-btn ul.dropdown-menu').html('');
                if (response.type === 'alert-success' && response.data.total > 0) {
                    $.each(response.data.records, $.proxy(function (key, group) {
                        this.addOptionToCombo(comboElm, {'value': group.id, 'title': group.title});
                    }, this));

                    if ($(comboElm).combobox('selectedItem').value === undefined) {
                        $(comboElm).combobox('selectByValue', response.data.records[0].id);
                    }
                }
            }, this)
        );
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        this.SettingsInUsersAjax = new JawsAjax('Settings');

        // init login box action
        if (this.gadget.actions.hasOwnProperty('Login')) {
            if ($('#loginkey').length) {
                $('#loginkey').focus();
            } else {
                $('#username').focus();
                $('#username').select();
            }
        }

    },

}};
