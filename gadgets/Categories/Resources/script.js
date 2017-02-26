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
    $('#categoryModalLabel').html(jaws.gadgets.Categories.lbl_edit);
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
    if (confirm(jaws.gadgets.Categories.confirmDelete)) {
        CategoriesAjax.callAsync('DeleteCategory', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function categoriesDataSource(options, callback) {
    var columns = [];
    if(jaws.gadgets.Categories.req_gadget =='') {
        columns.push(
            {
                'label': jaws.gadgets.Categories.lbl_gadget,
                'property': 'gadget',
                'sortable': true
            }
        );
    }
    columns.push(
        {
            'label': jaws.gadgets.Categories.lbl_action,
            'property': 'action',
            'sortable': true
        },
        {
            'label': jaws.gadgets.Categories.lbl_title,
            'property': 'title',
            'sortable': true
        }
    );

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var filters = {
        gadget: $('#filter_gadget').val(),
        action: $('#filter_action').val(),
        priority: $('#filter_priority').val(),
        status: $('#filter_status').val()
    };
    var options = {
        'offset': pageIndex,
        'limit': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filters': filters
    };

    var rows = CategoriesAjax.callSync('GetCategories', options);
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
 * initiate categories datagrid
 */
function initiateCategoriesDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.gadgets.Categories.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editCategory(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.gadgets.Categories.lbl_delete,
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