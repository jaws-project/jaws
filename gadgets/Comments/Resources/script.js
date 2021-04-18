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
    selectedComment : 0,

    // ASync callback method
    AjaxCallback: {
        UpdateComment: function(response) {
            if (response['type'] == 'alert-success') {
                this.selectedComment = 0;
                $('#commentModal').modal('hide');
                $('#comment-form')[0].reset();
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        DeleteComments: function(response) {
            if (response['type'] == 'alert-success') {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        MarkComments: function(response) {
            if (response['type'] == 'alert-success') {
                $('#comments-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        }
    },

    /**
     * Get selected data grid row ids
     */
    getSelectedDGRows: function (helpers) {
        var ids = [];
        if (helpers.length > 1) {
            helpers.forEach(function(entry) {
                ids.push(entry.rowData.id);
            });
        } else {
            ids.push(helpers.rowData.id);
        }
        return ids;
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
            'msg_abbr': {
                'label': this.gadget.defines.LANGUAGE.comment,
                'property': 'msg_abbr'
            },
            'username': {
                'label': this.gadget.defines.LANGUAGE.username,
                'property': 'username'
            },
            'insert_time': {
                'label': this.gadget.defines.LANGUAGE.time,
                'property': 'insert_time'
            },
            'status': {
                'label': this.gadget.defines.LANGUAGE.status,
                'property': 'status'
            }
        };

        var filters = $.unserialize($('#comments-grid .datagrid-filters form').serialize());

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
            case 'status':
                customMarkup = this.gadget.defines.statusItems[rowData.status];
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
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-edit"></span> ' + this.gadget.defines.LANGUAGE.edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editComment(helpers.rowData.id);
                        callback();
                    }, this)
                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.LANGUAGE.delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.deleteComments(this.getSelectedDGRows(helpers));
                        callback();
                    }, this)
                },
                {
                    name: 'mark_as_approved',
                    html: '<span class="glyphicon glyphicon-ok"></span> ' + this.gadget.defines.LANGUAGE.mark_as_approved,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.markComments(this.getSelectedDGRows(helpers), this.gadget.defines.status.approve);
                        callback();
                    }, this)
                },
                {
                    name: 'mark_as_waiting',
                    html: '<span class="glyphicon glyphicon-time"></span> ' + this.gadget.defines.LANGUAGE.mark_as_waiting,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.markComments(this.getSelectedDGRows(helpers), this.gadget.defines.status.waiting);
                        callback();
                    }, this)
                },
                {
                    name: 'mark_as_spam',
                    html: '<span class="glyphicon glyphicon-ban-circle"></span> ' + this.gadget.defines.LANGUAGE.mark_as_spam,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.markComments(this.getSelectedDGRows(helpers), this.gadget.defines.status.spam);
                        callback();
                    }, this)
                },
                {
                    name: 'mark_as_private',
                    html: '<span class="glyphicon glyphicon-eye-close"></span> ' + this.gadget.defines.LANGUAGE.mark_as_private,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();

                        this.markComments(this.getSelectedDGRows(helpers), this.gadget.defines.status.private);
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
     * view/edit a comment
     *
     */
    editComment: function (id) {
        this.selectedComment = id;

        this.ajax.callAsync('GetComment',
            {'id': id},
            function (response, status, callOptions) {
                if (response['type'] == 'alert-success') {
                    callOptions.showMessage = false;
                    var commentInfo = response.data;
                    if (commentInfo) {
                        $('#comment-form').find(':input').each($.proxy(function (key, elem) {
                                $(elem).val(response.data[$(elem).attr('name')]);
                            }, this)
                        );

                        $('#commentModal').modal('show');
                    }
                }
            }
        );
    },

    /**
     * Update a comment
     */
    updateComment: function (sendEmail = false) {
        var data = $.unserialize($('#comment-form').serialize());
        data.gadget = this.gadget.defines.gadget;
        data.send_email = sendEmail;
        this.ajax.callAsync('UpdateComment', {'id': this.selectedComment, 'data': data});
    },

    /**
     * delete comments
     */
    deleteComments: function (ids) {
        if (!confirm(this.gadget.defines.confirmCommentDelete)) {
            return false;
        }
        this.ajax.callAsync('DeleteComments', {'ids':ids});
    },

    /**
     * mark comments
     */
    markComments: function (ids, status) {
        this.ajax.callAsync('MarkComments', {'ids':ids, 'status':status});
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

                $('#btn-save-comment').on('click', $.proxy(function (e) {
                    this.updateComment(false);
                }, this));
                $('#btn-send-reply').on('click', $.proxy(function (e) {
                    this.updateComment(true);
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