/**
 * Comments Javascript actions
 *
 * @category    Ajax
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Comments() { return {
    // ASync callback method
    AjaxCallback: {
        UpdateComment: function(response) {
            if (response['type'] == 'alert-success') {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteComments: function(response) {
            if (response['type'] == 'alert-success') {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        MarkAs: function(response) {
            if (response['type'] == 'alert-success') {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        }
    },

    /**
     * Define the data to be displayed in the comment datagrid
     */
    commentsDataSource: function (options, callback) {
        var columns = {
            'gadget': {
                'label': this.gadget.defines.LANGUAGE.gadget,
                'property': 'gadget'
            },
            'action': {
                'label': this.gadget.defines.LANGUAGE.action,
                'property': 'action'
            },
            'auth': {
                'label': this.gadget.defines.LANGUAGE.auth,
                'property': 'auth'
            },
            'username': {
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username'
            },
            'time': {
                'label': this.gadget.defines.LANGUAGE.time,
                'property': 'time'
            }
        };

        var filters = $.unserialize($('#comments-grid .datagrid-filters form').serialize());
        filters.user = $('#filter_user').combobox('selectedItem').value === undefined ? 0 :
            $('#filter_user').combobox('selectedItem').value;

        // set sort property & direction
        if (options.sortProperty) {
            columns[options.sortProperty].sortDirection = options.sortDirection;
        }
        columns = Object.values(columns);

        this.gadget.ajax.callAsync(
            'GetComments', {
                'offset': options.pageIndex * options.pageSize,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': filters
            },
            function(response, status, callOptions) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;

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
    },

    /**
     * comments Datagrid column renderer
     */
    commentsDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'gadget':
                customMarkup = this.gadget.defines.gadgetList[rowData.gadget];
                break;
            default:
                customMarkup = helpers.item.text();
                break;
        }

        helpers.item.html(customMarkup);
        callback();
    },

    /**
     * initiate Comments dataGrid
     */
    initiateCommentsDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'view',
                    html: '<span class="glyphicon glyphicon-eye-open"></span> ' + this.gadget.defines.LANGUAGE.view,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.viewComment(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        var ids = [];
                        if (helpers.length > 1) {
                            helpers.forEach(function(entry) {
                                ids.push(entry.rowData.id);
                            });
                        } else {
                            ids.push(helpers.rowData.id);
                        }

                        this.deleteComments('selected', ids);
                        callback();
                    }, this)

                },
            ]
        };

        // initialize the repeater
        $('#comments-grid').repeater({
            dataSource: $.proxy(this.commentsDataSource, this),
            list_actions: list_actions,
            list_columnRendered: $.proxy(this.commentsDGColumnRenderer, this),
            list_selectable: 'multi',
            list_noItemsHTML: this.gadget.defines.datagridNoItems,
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $("#comments-grid select").change(function () {
            $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
        $("#comments-grid input").keypress(function (e) {
            if (e.which == 13) {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        });
        $("#comments-grid button.btn-refresh").on('click', function (e) {
            $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * view a comment details
     *
     */
    viewComment: function (id) {
        this.ajax.callAsync('GetComment',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var commentInfo = response.data;
                    if (commentInfo) {
                        $('#comment-form span').each(
                            $.proxy(function (key, elem) {
                                $(elem).html(response.data[$(elem).data('field')]);
                            }, this)
                        );

                        $('#commentModal').modal('show');
                    }
                }
            }
        );
    },

    /**
     * delete comments
     */
    deleteComments: function (type, ids = []) {
        if (!confirm(this.gadget.defines.confirmCommentsDelete)) {
            return false;
        }

        var params = {'ids': [], 'filters': []};
        if (type === 'filtered') {
            params.filters = $.unserialize($('#comments-grid .datagrid-filters form').serialize());
            params.filters.user = $('#filter_user').combobox('selectedItem').value === undefined ? 0 :
                $('#filter_user').combobox('selectedItem').value;
        } else {
            params.ids = ids;
        }
        this.ajax.callAsync('DeleteComments', params);
    },

    /**
     * save properties
     */
    saveSettings: function () {
        this.ajax.callAsync(
            'SaveSettings', {
                'allow_comments': $('#allow_comments').val(),
                'default_comment_status': $('#default_comment_status').val(),
                'order_type': $('#order_type').val()
            }
        );
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        switch (mainAction) {
            case 'Comments':
                $('#gadgets_filter').selectedIndex = 0;

                this.initiateCommentsDG();

                $('#btn-delete-filtered-comments').on('click', $.proxy(function (e) {
                    this.deleteComments('filtered');
                }, this));

                break;
            case 'Settings':
                $('#btn-update-settings').on('click', $.proxy(function (e) {
                    this.saveSettings();
                }, this));

                break;
        }
    },

}};




/**
 * Use async mode, create Callback
 */
// var CommentsCallback = {
//     UpdateComment: function(response) {
//         if (response['type'] == 'alert-success') {
//             stopCommentAction();
//             getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
//         }
//     },
//
//     DeleteComments: function(response) {
//         if (response['type'] == 'alert-success') {
//             stopCommentAction();
//             getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
//         }
//     },
//
//     MarkAs: function(response) {
//         if (response['type'] == 'alert-success') {
//             stopCommentAction();
//             getDG('comments_datagrid', $('#comments_datagrid')[0].getCurrentPage(), true);
//         }
//     }
//
// }
//
// /**
//  * Fetches comments data to fills the data grid
//  */
// function getCommentsDataGrid(name, offset, reset)
// {
//     var comments = CommentsAjax.callSync(
//         'SearchComments', [
//             CommentsAjax.mainRequest.gadget,
//             $('#gadgets_filter').val(),
//             $('#filter').val(),
//             $('#status').val(),
//             offset,
//             2
//         ]
//     );
//     if (reset) {
//         stopCommentAction();
//         $('#' + name)[0].setCurrentPage(0);
//         var total = CommentsAjax.callSync(
//             'SizeOfCommentsSearch', [
//                 $('#gadgets_filter').val(),
//                 $('#filter').val(),
//                 $('#status').val()
//             ]
//         );
//     }
//
//     resetGrid(name, comments, total);
// }
//
// function isValidEmail(email) {
//     return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(email));
// }
//
// /**
//  * Clean the form
//  *
//  */
// function stopCommentAction()
// {
//     $('#id').val(0);
//     $('#gadget').val('');
//     $('#comment_ip').html('');
//     $('#insert_time').html('');
//     $('#reference_link').html('');
//     $('#name').val('');
//     $('#email').val('');
//     $('#url').val('');
//     $('#message').val('');
//     $('#reply').val('');
//     $('#comment_status').prop('selectedIndex', 0);
//     $('#btn_save').css('display', 'none');
//     $('#btn_reply').css('display', 'none');
//     $('#btn_cancel').css('display', 'none');
//     $("#name").prop('disabled', false);
//     $("#email").prop('disabled', false);
//     $("#url").prop('disabled', false);
//     $("#message").prop('disabled', false);
//     $("#comment_status").prop('disabled', false);
//
//     unselectGridRow('comments_datagrid');
//     $('#name').focus();
// }
//
// /**
//  * Edit a Comment
//  *
//  */
// function editComment(rowElement, id)
// {
//     stopCommentAction();
//     selectGridRow('comments_datagrid', rowElement.parentNode.parentNode);
//     var comment = CommentsAjax.callSync('GetComment', id);
//     $("#name").prop('disabled', false);
//     $("#email").prop('disabled', false);
//     $("#url").prop('disabled', false);
//     $("#message").prop('disabled', false);
//     $("#comment_status").prop('disabled', false);
//     $('#id').val(comment['id']);
//     $('#gadget').val(comment['gadget']);
//     $('#comment_ip').html(comment['ip']);
//     $('#insert_time').html(comment['insert_time']);
//     $('#name').val(comment['name']);
//     $('#email').val(comment['email']);
//     $('#url').val(comment['url']);
//     $('#message').val(comment['msg_txt'].defilter());
//     $('#comment_status').val(comment['status']);
//     if (comment['reference_link'] != '') {
//         $('#reference_link').html(
//             '<a href="'
//             + comment['reference_link']
//             + '">'
//             + comment['reference_title']
//             + '</a>'
//         );
//     }
//     $('#btn_save').css('display', 'inline');
//     $('#btn_reply').css('display', 'inline');
//     $('#btn_cancel').css('display', 'inline');
//
//     if(comment['reply']!=null) {
//         $('#reply').val(comment['reply'].defilter());
//     }
// }
//
// /**
//  * Update a Comment
//  */
// function updateComment(sendEmail) {
//     CommentsAjax.callAsync(
//         'UpdateComment', [
//             $('#gadget').val(),
//             $('#id').val(),
//             $('#name').val(),
//             $('#email').val(),
//             $('#url').val(),
//             $('#message').val(),
//             $('#reply').val(),
//             $('#comment_status').val(),
//             sendEmail
//         ]
//     );
// }
//
// /**
//  * Delete comment
//  *
//  */
// function commentDelete(id)
// {
//     stopCommentAction();
//     if (confirm(jaws.Comments.Defines.confirmCommentDelete)) {
//         CommentsAjax.callAsync('DeleteComments', new Array(id));
//     }
//     unselectGridRow('comments_datagrid');
// }
//
//
// /**
//  * Executes an action on comments
//  */
// function commentDGAction(combo)
// {
//     var rows = $('#comments_datagrid')[0].getSelectedRows();
//     if (rows.length < 1) {
//         return;
//     }
//
//     if (combo.val() == 'delete') {
//         var confirmation = confirm(jaws.Comments.Defines.confirmCommentDelete);
//         if (confirmation) {
//             CommentsAjax.callAsync('DeleteComments', rows);
//         }
//     } else if (combo.val() != '') {
//         CommentsAjax.callAsync('MarkAs', {
//             'ids': rows,
//             'status': combo.val()
//         });
//     }
// }
//
// /**
//  * search for a comment
//  */
// function searchComment()
// {
//     getCommentsDataGrid('comments_datagrid', 0, true);
// }
//
// /**
//  * save properties
//  */
// function SaveSettings()
// {
//     CommentsAjax.callAsync(
//         'SaveSettings', [
//             $('#allow_comments').val(),
//             $('#default_comment_status').val(),
//             $('#order_type').val()
//         ]
//     );
// }
//
// $(document).ready(function() {
//     if (jaws.Defines.mainGadget !== 'Comments' || jaws.Defines.mainAction === 'Comments') {
//         $('#gadgets_filter').selectedIndex = 0;
//         initDataGrid('comments_datagrid', CommentsAjax, getCommentsDataGrid);
//     }
// });
//
// var CommentsAjax = new JawsAjax('Comments', CommentsCallback),
//     selectedRow = null,
//     selectedRowColor = null;
