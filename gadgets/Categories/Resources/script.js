/**
 * Categories Javascript actions
 *
 * @category    Ajax
 * @package     Categories
 */

/**
 * Use async mode, create Callback
 */
var CategoriesCallback = {
    DeleteCategory: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#categories-grid').repeater('render');
        }
        CategoriesAjax.showResponse(response);
    },
    InsertCategory: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#categories-grid').repeater('render');
        }
        CategoriesAjax.showResponse(response);
    },
    UpdateCategory: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#categories-grid').repeater('render');
        }
        CategoriesAjax.showResponse(response);
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    selectedCategory = 0;
    $('#categoryModal').modal('hide');
    $('form#category-form')[0].reset();

}

/**
 * Edit a category
 */
function editCategory(id)
{
    selectedCategory = id;
    $('#categoryModalLabel').html(jaws.Categories.Defines.lbl_edit);
    CategoriesAjax.callAsync('GetCategory', {'id': selectedCategory}, function (response) {
        if (response) {
            $('#category-form input, #category-form select, #category-form textarea').each(
                function () {
                    $(this).val(response[$(this).attr('name')]);
                }
            );

            $('#categoryModal').modal('show');
        }

    });
}

/**
 * Update the category
 */
function saveCategory()
{
    if (selectedCategory == 0) {
        CategoriesAjax.callAsync(
            'InsertCategory', {
                data: $.unserialize($('form#category-form').serialize())
            }
        );
    } else {
        CategoriesAjax.callAsync(
            'UpdateCategory', {
                id: selectedCategory,
                data: $.unserialize($('form#category-form').serialize())
            }
        );
    }
}


/**
 * Delete category
 */
function deleteCategory(id)
{
    if (confirm(jaws.Categories.Defines.confirmDelete)) {
        CategoriesAjax.callAsync('DeleteCategory', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function categoriesDataSource(options, callback) {
    var columns = [];
    if(jaws.Categories.Defines.req_gadget =='') {
        columns.push(
            {
                'label': jaws.Categories.Defines.lbl_gadget,
                'property': 'gadget',
                'sortable': true
            },
            {
                'label': jaws.Categories.Defines.lbl_action,
                'property': 'action',
                'sortable': true
            }
        );
    }
    columns.push(
        {
            'label': jaws.Categories.Defines.lbl_title,
            'property': 'title',
            'sortable': true
        }
    );


    CategoriesAjax.callAsync(
        'GetCategories', {
            'offset': options.offset,
            'limit': options.pageSize,
            'sortDirection': options.sortDirection,
            'sortBy': options.sortProperty,
            'filters': {
                gadget: $('#filter_gadget').val(),
                action: $('#filter_action').val(),
                priority: $('#filter_priority').val(),
                status: $('#filter_status').val()
            }
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
            // pass the datasource back to the repeater
            callback(dataSource);
            CategoriesAjax.showResponse(response);
        }
    );
}

/**
 * initiate categories datagrid
 */
function initiateCategoriesDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Categories.Defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editCategory(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Categories.Defines.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteCategory(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    // initialize the repeater
    $('#categories-grid').repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: categoriesDataSource,
        staticHeight: 500,
        list_actions: list_actions,
        list_direction: $('.repeater-canvas').css('direction')
    });

    // monitor required events
    $( ".datagrid-filters select" ).change(function() {
        $('#categories-grid').repeater('render');
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#categories-grid').repeater('render');
        }
    });
    $('#categoryModal').on('hidden.bs.modal', function (e) {
        $('form#category-form')[0].reset();
        selectedCategory = 0;
    });
}


$(document).ready(function () {
    initiateCategoriesDG();
});

var CategoriesAjax = new JawsAjax('Categories', CategoriesCallback),
    selectedCategory = 0;