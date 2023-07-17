/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users_Action_ACLs() {
    return {

        // ASync callback method
        AjaxCallback: {
        },

        /**
         * view a acl permissions
         */
        viewACL: function(component, acl) {
            this.ajax.call(
                'GetACLGroupsUsers',
                {component: component, acl:acl},
                function (response) {
                    $("#groups_permission ul").html('');
                    $.each(response.groups, function (key, group) {
                        var status = '<span class="glyphicon glyphicon-ok"></span>';
                        if(group.key_value==0) {
                            status = '<span class="glyphicon glyphicon-remove"></span>';
                        }
                        $("#groups_permission ul").append('<li>' + group.title + ' ' + status +'</li>');
                    });

                    $("#users_permission ul").html('');
                    $.each(response.users, function (key, user) {
                        var status = '<span class="glyphicon glyphicon-ok"></span>';
                        if(user.key_value==0) {
                            status = '<span class="glyphicon glyphicon-remove"></span>';
                        }
                        $("#users_permission ul").append('<li>' + user.nickname  + ' ' + status +'</li>');
                    });
                }
            );
        },

        /**
         * Categories tree data source
         */
        aclTreeDataSource: function(openedParentData, callback) {
            var childNodesArray = [];

            var pid = openedParentData.id == undefined ? 0 : openedParentData.id;
            if (pid == 0) {
                $.each(this.gadget.defines.GADGETS, function (gadget, title) {
                    childNodesArray.push(
                        {
                            id: gadget,
                            name: title,
                            type: 'folder',
                            attr: {
                                id: 'gadget_' + gadget,
                                hasChildren: true,
                            },
                        }
                    );
                });

                callback({
                    data: childNodesArray
                });

            } else {
                this.ajax.call('GetACLs', {component: pid}, function (response) {
                    $.each(response, function (key, acl) {
                        childNodesArray.push(
                            {
                                id: acl.key_name,
                                name: acl.key_desc,
                                component: pid,
                                type: 'item',
                                attr: {
                                    id: 'acl_' + acl.key_name,
                                    hasChildren: false,
                                },
                            }
                        );
                    });

                    callback({
                        data: childNodesArray
                    });
                });
            }
        },

        /**
         * Initiates ACLs tree
         */
        initiateACLsTree: function() {
            $('#aclTree').tree({
                dataSource: $.proxy(this.aclTreeDataSource, this),
                multiSelect: false,
                folderSelect: true
            }).on('selected.fu.tree', $.proxy(function (event, data) {
                if (data.selected[0].type == 'item') {
                    this.viewACL(data.selected[0].component, data.selected[0].id);
                }
            }, this));
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            this.initiateACLsTree();
        }
    }
};
