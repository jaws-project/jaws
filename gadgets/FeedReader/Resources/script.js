/**
 * FeedReader Javascript actions
 *
 * @category   Ajax
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FeedReaderCallback = { 
    DeleteFeed: function(response) {
        if (response['type'] == 'response_notice') {
            stopAction();
            $('feedsites_datagrid').deleteItem();          
            getDG();
        }
        showResponse(response);
    },
    
    InsertFeed: function(response) {
        if (response['type'] == 'response_notice') {
            $('feedsites_datagrid').addItem();
            $('feedsites_datagrid').setCurrentPage(0);
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    UpdateFeed: function(response) {
        if (response['type'] == 'response_notice') {
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    GetFeed: function(response) {
        updateForm(response);
    }
}

function isValidURL(url) {
    return (/^(((ht|f)tp(s?))\:\/\/).*$/.test(url));
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Clean the form
 *
 */
function stopAction() 
{
    $('id').value          = '0';
    $('title').value       = '';
    $('url').value         = 'http://';
    $('cache_time').value  = '3600';
    $('view_type').value   = '0';
    $('count_entry').value = '';
    $('title_view').value  = '0';
    $('visible').value     = '1';
    unselectDataGridRow();
    $('btn_cancel').style.visibility = 'hidden';
}

/**
 * Update form with new values
 *
 */
function updateForm(feed)
{
    $('id').value          = feed['id'];
    $('title').value       = feed['title'].defilter();
    $('url').value         = feed['url'].defilter();
    $('cache_time').value  = feed['cache_time'];
    $('view_type').value   = feed['view_type'];
    $('count_entry').value = feed['count_entry'];
    $('title_view').value  = feed['title_view'];
    $('visible').value     = feed['visible'];
    $('btn_cancel').style.visibility = 'visible';
}

/**
 * Add/Update a feed
 */
function updateFeed()
{
    if (!$('title').val() ||
        !$('url').val() ||
        !isValidURL($('url').value.trim()))
    {
        alert(incompleteFeedFields);
        return;
    }

    if($('id').value==0) {
            FeedReaderAjax.callAsync(
                'InsertFeed', [
                    $('title').value,
                    $('url').value,
                    $('cache_time').value,
                    $('view_type').value,
                    $('count_entry').value,
                    $('title_view').value,
                    $('visible').value
                ]
            );
    } else {
        FeedReaderAjax.callAsync(
            'UpdateFeed', [
                $('id').value,
                $('title').value,
                $('url').value,
                $('cache_time').value,
                $('view_type').value,
                $('count_entry').value,
                $('title_view').value,
                $('visible').value
            ]
        );
    }
}

/**
 * Delete a feed
 */
function deleteFeed(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmFeedDelete);
    if (answer) {
        FeedReaderAjax.callAsync('DeleteFeed', id);
    }
    unselectDataGridRow();
}

/**
 * Edit a feed
 *
 */
function editFeed(element, id)
{
    selectDataGridRow(element.parentNode.parentNode);
    FeedReaderAjax.callAsync('GetFeed', id);
}

var FeedReaderAjax = new JawsAjax('FeedReader', FeedReaderCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
