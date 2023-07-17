/**
 * FeedReader Javascript actions
 *
 * @category    Ajax
 * @package     FeedReader
 * @copyright   2016-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var FeedReaderCallback = {
    InsertFeed: function (response) {
        if (response['type'] == 'alert-success') {
            $('#feeds-grid').repeater('render');
            stopAction();
        }
    },
    UpdateFeed: function (response) {
        if (response['type'] == 'alert-success') {
            $('#feeds-grid').repeater('render');
            stopAction();
        }
    },
    DeleteUserFeed: function (response) {
        if (response['type'] == 'alert-success') {
            $('#feeds-grid').repeater('render');
            stopAction();
        }
    }

}

/**
 * Edit a feed
 */
function editFeed(id)
{
    selectedFeed = id;
    FeedReaderAjax.call('GetUserFeed', {'id': selectedFeed}, function (feed) {
        if (feed) {
            $('form#feed #title').val(feed['title'].defilter());
            $('form#feed #url').val(feed['url']);
            $('form#feed #cache_time').val(feed['cache_time']);
            $('form#feed #view_type').val(feed['view_type']);
            $('form#feed #title_view').val(feed['title_view']);
            $('form#feed #count_entry').val(feed['count_entry']);
            $('form#feed #alias').val(feed['alias']);
            $('form#feed #published').val(feed['published']? 1 : 0);
            $('#feedModal').modal('show');
        }
    });
}

/**
 * Delete a feed
 */
function deleteFeed(id)
{
    if (confirm(Jaws.gadgets.FeedReader.defines.confirmDelete)) {
        FeedReaderAjax.call('DeleteUserFeed', {'id': id});
    }
}

/**
 * Update a feed
 */
function updateFeed() {
    if (selectedFeed != null) {
        FeedReaderAjax.call(
            'UpdateFeed', {
                'data': $.unserialize(
                    $('form#feed input,form#feed select,form#feed textarea').serialize()
                ),
                'id': selectedFeed
            }
        );
    } else {
        FeedReaderAjax.call(
            'InsertFeed', {
                'data': $.unserialize(
                    $('#did, form#feed input,form#feed select,form#feed textarea').serialize()
                )
            }
        );
    }
}

/**
 * stop Action
 */
function stopAction()
{
    selectedFeed = null;
    $('#feedModal').modal('hide');
    $('form#feed')[0].reset();
}

// Define the data to be displayed in the repeater.
function feedsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': Jaws.gadgets.FeedReader.defines.lbl_title,
            'property': 'title',
        },
        {
            'label': Jaws.gadgets.FeedReader.defines.lbl_published,
            'property': 'published',
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

    FeedReaderAjax.call('GetUserFeeds', options, function (response, status, callOptions) {
        if (response.type == 'alert-success') {
            callOptions.showMessage = false;
            var items = response.data.records;
            var totalItems = response.data.total;
            var totalPages = Math.ceil(totalItems / pageSize);
            var startIndex = (pageIndex * pageSize) + 1;
            var endIndex = (startIndex + pageSize) - 1;

            if (endIndex > items.length) {
                endIndex = items.length;
            }

            // configure datasource
            var dataSource = {
                'page': pageIndex,
                'pages': totalPages,
                'count': totalItems,
                'start': startIndex,
                'end': endIndex,
                'columns': columns,
                'items': items
            };

            // pass the datasource back to the repeater
            callback(dataSource);
        }
    });
}

/**
 * initiate feeds datagrid
 */
function initiateFeedsDG() {

    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + Jaws.gadgets.FeedReader.defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editFeed(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + Jaws.gadgets.FeedReader.defines.lbl_delete ,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteFeed(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    $('#feeds-grid').repeater({
        dataSource: feedsDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_direction: $('.repeater-canvas').css('direction')
    });

    $('#feedModal').on('hidden.bs.modal', function (e) {
        stopAction();
    });
}

/**
 * Initiates gadget
 */
$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'UserFeedsList':
            initiateFeedsDG();
            break;
    }
});

var selectedFeed = null,
    FeedReaderAjax = new JawsAjax('FeedReader', FeedReaderCallback);
