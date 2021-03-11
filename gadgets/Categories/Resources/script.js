/**
 * Categories Javascript actions
 *
 * @category    Ajax
 * @package     Categories
 */
function Jaws_Gadget_Categories() { return {
    // current selected category
    selectedCategory: 0,

    // ASync callback method
    AjaxCallback : {
        DeleteCategory: function(response) {
            if (response.type == 'alert-success') {
                this.stopAction();
                $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        InsertCategory: function(response) {
            if (response.type == 'alert-success') {
                this.stopAction();
                $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        },

        UpdateCategory: function(response) {
            if (response.type == 'alert-success') {
                this.stopAction();
                $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        }
    },

    /**
     * Stops doing a certain action
     */
    stopAction: function() {
        this.selectedCategory = 0;
        $('#categoryModal').modal('hide');
        $('form#category-form')[0].reset();
    },

    /**
     * Change the gadget combo
     */
    changeGadget: function(selectedGadget, actionElm, addDefaultItem = true) {
        actionElm = actionElm.empty().get(0);
        if (addDefaultItem) {
            actionElm.options[actionElm.options.length] = new Option(this.gadget.defines.lbl_all, 0);
        }
        $.each(this.gadget.defines.gadgets_actions[selectedGadget], function (actionName, actionTitle) {
            actionElm.options[actionElm.options.length] = new Option(actionTitle, actionName);
        });
    },

    /**
     * Add a category
     */
    addCategory: function() {
        $('#gadget').trigger('change');
        $('#categoryModal').modal('show');
    },

    /**
     * Edit a category
     */
    editCategory: function(id) {
        this.selectedCategory = id;
        $('#categoryModalLabel').html(this.gadget.defines.lbl_edit);
        this.gadget.ajax.callAsync('GetCategory', {'id': this.selectedCategory}, 
            function (response, status) {
                if (response['type'] == 'alert-success') {
                    var value;
                    $('#category-form input, #category-form select, #category-form textarea').each(
                        function (i, el) {
                            value = response['data'][$(el).attr('name')];
                            switch (typeof(value)) {
                                case 'boolean':
                                    value = value? '1' : '0'
                                    break;
                                default:
                                    // do nothing
                            }

                            $(el).val(value);

                            if ($(el).is("select")) {
                                $(el).trigger('change');
                            }
                        }
                    );

                    $('#categoryModal').modal('show');
                }
            });
    },

    /**
     * Update the category
     */
    saveCategory: function() {
        var data = $.unserialize($('form#category-form').serialize());
        if (this.gadget.defines.req_gadget !== null) {
            data.gadget = this.gadget.defines.req_gadget;
        }
        if (this.gadget.defines.req_action !== null) {
            if (data.action === undefined || data.action === null) {
                data.action = this.gadget.defines.req_action;
            }
        }
        if (this.selectedCategory === 0) {
            this.gadget.ajax.callAsync('InsertCategory', {
                data: data
            });
        } else {
            this.gadget.ajax.callAsync('UpdateCategory', {
                id: this.selectedCategory,
                data: data
            });
        }
    },

    /**
     * Delete category
     */
    deleteCategory: function(id) {
        if (confirm(this.gadget.defines.confirmDelete)) {
            this.gadget.ajax.callAsync('DeleteCategory', {'id': id});
        }
    },

    /**
     * Define the data to be displayed in the users datagrid
     */
    categoriesDataSource: function(options, callback) {
        var columns = [];
        if (this.gadget.defines.req_gadget === null) {
            columns.push(
                {
                    'label': this.gadget.defines.lbl_gadget,
                    'property': 'gadget',
                    'sortable': true
                },
                {
                    'label': this.gadget.defines.lbl_action,
                    'property': 'action',
                    'sortable': true
                }
            );
        } else {
            if (this.gadget.defines.req_action === null) {
                if (Object.keys(this.gadget.defines.gadgets_actions[this.gadget.defines.req_gadget]).length > 1) {
                    columns.push(
                        {
                            'label': this.gadget.defines.lbl_action,
                            'property': 'action',
                            'sortable': true
                        }
                    );
                }
            }
        }
        columns.push(
            {
                'label': this.gadget.defines.lbl_title,
                'property': 'title',
                'sortable': true
            }
        );

        var filters = {
            term: $('#filter_term').val(),
            gadget: this.gadget.defines.req_gadget,
            action: this.gadget.defines.req_action,
        };
        if (this.gadget.defines.req_gadget === null) {
            filters.gadget = $('#filter_gadget').val();
        }
        if (this.gadget.defines.req_action === null) {
            filters.action = $('#filter_action').val();
        }

        console.log('filters', filters);

        this.gadget.ajax.callAsync(
            'GetCategories', {
                'offset': options.offset,
                'limit': options.pageSize,
                'sortDirection': options.sortDirection,
                'sortBy': options.sortProperty,
                'filters': filters
            },
            function(response, status) {
                var dataSource = {};
                if (response['type'] == 'alert-success') {
                    // processing end item index of page
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
                // pass the dataSource back to the repeater
                callback(dataSource);
            }
        );
    },

    /**
     * categories Datagrid column renderer
     */
    categoriesDGColumnRenderer: function (helpers, callback) {
        var column = helpers.columnAttr;
        var rowData = helpers.rowData;
        var customMarkup = '';

        switch (column) {
            case 'gadget':
                customMarkup = this.gadget.defines.gadgets[rowData.gadget];
                break;
            case 'action':
                customMarkup = helpers.item.text();
                $.each(this.gadget.defines.gadgets_actions[rowData.gadget], function (actionName, actionTitle) {
                    if (actionName === rowData.action) {
                        customMarkup = actionTitle
                    }
                });
                break;
            default:
                customMarkup = helpers.item.text();
                break;
        }

        helpers.item.html(customMarkup);
        callback();
    },

    /**
     * initiate categories dataGrid
     */
    initiateCategoriesDG: function() {
        var list_actions = {
            width: 50,
            items: [
                {
                    name: 'edit',
                    html: '<span class="glyphicon glyphicon-pencil"></span> ' + this.gadget.defines.lbl_edit,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.editCategory(helpers.rowData.id);
                        callback();
                    }, this)

                },
                {
                    name: 'delete',
                    html: '<span class="glyphicon glyphicon-trash"></span> ' + this.gadget.defines.lbl_delete,
                    clickAction: $.proxy(function (helpers, callback, e) {
                        e.preventDefault();
                        this.deleteCategory(helpers.rowData.id);
                        callback();
                    }, this)
                }
            ]
        };

        // initialize the repeater
        $('#categories-grid').repeater({
            dataSource: $.proxy(this.categoriesDataSource, this),
            staticHeight: 500,
            list_actions: list_actions,
            list_columnRendered: $.proxy(this.categoriesDGColumnRenderer, this),
            list_direction: $('.repeater-canvas').css('direction')
        });

        // monitor required events
        $( ".datagrid-filters input" ).keypress(function(e) {
            if (e.which == 13) {
                $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            }
        });

        $("#categories-grid .btn-refresh").on('click', function (e) {
            $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        });
    },

    /**
     * initialize gadget actions
     */
    init: function (mainGadget, mainAction) {
        this.initiateCategoriesDG();

        $('#categoryModal').on('hidden.bs.modal', $.proxy(function (e) {
            $('form#category-form')[0].reset();
            this.selectedCategory = 0;
        }, this));

        $('#btn-add-category').on('click', $.proxy(function (e) {
            this.addCategory();
        }, this));

        $('#filter_gadget').on('change', $.proxy(function (e) {
            this.changeGadget($(e.target).val(), $('#filter_action'));
            $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }, this));
        $('#gadget').on('change', $.proxy(function (e) {
            this.changeGadget($(e.target).val(), $('#action'), false);
        }, this));
        $('#filter_action').on('change', $.proxy(function (e) {
            $('#categories-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
        }, this));
        $('#btn-save-category').on('click', $.proxy(function (e) {
            this.saveCategory();
        }, this));
    },

}};

