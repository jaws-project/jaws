/**
 * FeedReader Javascript actions
 *
 * @category    Ajax
 * @package     FeedReader
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var FeedReaderCallback = {
    InsertFeed: function (response) {
        if (response['type'] == 'response_notice') {
            w2popup.close();
            w2ui['feeds-grid'].reload();
            stopAction();
        }
        FeedReaderAjax.showResponse(response);
    },
    UpdateFeed: function (response) {
        if (response['type'] == 'response_notice') {
            w2popup.close();
            w2ui['feeds-grid'].reload();
            stopAction();
        }
        FeedReaderAjax.showResponse(response);
    }
}


/**
 * Edit a feed
 */
function editFeed(id)
{
    selectedFeed = id;
    var feed = FeedReaderAjax.callSync('GetUserFeed', {'id': selectedFeed});

    $('#feed_workarea').w2popup({
        title: lbl_edit,
        modal: true,
        width: 350,
        height: 300,
        onOpen: function(event) {
            event.onComplete = function() {
                $('#w2ui-popup #title').val(feed['title'].defilter());
                $('#w2ui-popup #url').val(feed['url']);
                $('#w2ui-popup #cache_time').val(feed['cache_time']);
                $('#w2ui-popup #view_type').val(feed['view_type']);
                $('#w2ui-popup #title_view').val(feed['title_view']);
                $('#w2ui-popup #count_entry').val(feed['count_entry']);
                $('#w2ui-popup #visible').val(feed['visible']? 1 : 0);
                // if (mbox) {
                //     $('#w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').each(
                //         function() {
                //             $(this).val(mbox[$(this).attr('name')]);
                //         }
                //     );
                // }
            };
        },
    });
}

/**
 * Add a feed
 */
function addFeed()
{
    $('#feed_workarea').w2popup({
        title: lbl_add,
        modal: true,
        width: 350,
        height: 300,
    });
}


/**
 * Update a feed
 */
function updateFeed() {
    if (selectedFeed != null) {
        FeedReaderAjax.callAsync(
            'UpdateFeed', {
                'data': $.unserialize(
                    $('#w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').serialize()
                ),
                'id': selectedFeed
            }
        );
    } else {
        FeedReaderAjax.callAsync(
            'InsertFeed', {
                'data': $.unserialize(
                    $('#did, #w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').serialize()
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
    $('form[name="feed"]')[0].reset();
    w2popup.close();
}

/**
 * Initiates gadget
 */
$(document).ready(function() {
    // set w2ui default configuration
    w2utils.settings.dataType = 'JSON';
    // load Persian translation
    w2utils.locale('libraries/w2ui/fa-pe.json');

    // initial feeds datagrid
    $('#feeds-grid').w2grid({
        name: 'feeds-grid',
        method: 'POST',
        url: {
            get    : FeedReaderAjax.baseURL + 'GetUserFeeds',
            remove : FeedReaderAjax.baseURL + 'DeleteUserFeeds'
        },
        show: {
            toolbar: true,
            footer: true,
            selectColumn: true,
            toolbarAdd: true,
            toolbarDelete: true,
            toolbarEdit: true
        },
        recid: 'id',
        columns: [
            { field: 'title',     caption: lbl_title,  size: '60%' },
            { field: 'visible',     caption: lbl_visible,  size: '40%' },
        ],
        records: [],
        onRequest: function(event) {
            switch (event.postData.cmd) {
                case 'get':
                    break;

                case 'delete':
                    event.postData = {
                        'ids':  event.postData.selected,
                    };
                    break;

                case 'save':
                    break;

            }

        },
        onLoad: function(event) {
            event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
            if (event.xhr.responseText.type != 'response_notice') {
                event.xhr.responseText.message = event.xhr.responseText.text;
                event.xhr.responseText.status = "error";
            } else {
                event.xhr.responseText = event.xhr.responseText.data;
            }
        },
        onDelete: function(event) {
            if (event.xhr) {
                event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
                if (event.xhr.responseText.type != 'response_notice') {
                    event.xhr.responseText.message = event.xhr.responseText.text;
                    event.xhr.responseText.status = "error";
                } else {
                    event.xhr.responseText = event.xhr.responseText.data;
                }
            }
        },
        toolbar: {
            onClick: function (target, data) {
                if (target == 'w2ui-add') {
                    addFeed();
                } else if (target == 'w2ui-edit') {
                    editFeed(w2ui['feeds-grid'].getSelection()[0]);
                }
            }
        },
        onDblClick: function(event) {
            editFeed(event.recid)
        },
        onSelect: function(event) {
        },
        onUnselect: function(event) {
        },
    });
});

var selectedFeed = null,
    FeedReaderAjax = new JawsAjax('FeedReader', FeedReaderCallback);
