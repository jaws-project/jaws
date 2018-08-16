/**
 * FeedReader Javascript actions
 *
 * @category   Ajax
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FeedReaderCallback = { 
    DeleteFeed: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#feedsites_datagrid')[0].deleteItem();          
            getDG();
        }
        FeedReaderAjax.showResponse(response);
    },
    
    InsertFeed: function(response) {
        if (response['type'] == 'alert-success') {
            $('#feedsites_datagrid')[0].addItem();
            $('#feedsites_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        stopAction();
        FeedReaderAjax.showResponse(response);
    },

    UpdateFeed: function(response) {
        if (response['type'] == 'alert-success') {
            getDG();
        }
        stopAction();
        FeedReaderAjax.showResponse(response);
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
    $('#id').val('0');
    $('#title').val('');
    $('#url').val('http://');
    $('#cache_time').val('3600');
    $('#view_type').val('0');
    $('#count_entry').val('');
    $('#title_view').val('0');
    $('#alias').val('');
    $('#published').val(1);
    unselectDataGridRow();
    $('#btn_cancel').css('visibility', 'hidden');
}

/**
 * Update form with new values
 *
 */
function updateForm(feed)
{
    $('#id').val(feed['id']);
    $('#title').val(feed['title'].defilter());
    $('#url').val(feed['url'].defilter());
    $('#cache_time').val(feed['cache_time']);
    $('#view_type').val(feed['view_type']);
    $('#count_entry').val(feed['count_entry']);
    $('#title_view').val(feed['title_view']);
    $('#alias').val(feed['alias']);
    $('#published').val(feed['published']? 1 : 0);
    $('#btn_cancel').css('visibility', 'visible');
}

/**
 * Add/Update a feed
 */
function updateFeed()
{
    if (!$('#title').val() ||
        !$('#url').val() ||
        !isValidURL($.trim($('#url').val())))
    {
        alert(jaws.FeedReader.Defines.incompleteFeedFields);
        return;
    }

    if($('#id').val()==0) {
            FeedReaderAjax.callAsync(
                'InsertFeed', [
                    $('#title').val(),
                    $('#url').val(),
                    $('#cache_time').val(),
                    $('#view_type').val(),
                    $('#count_entry').val(),
                    $('#title_view').val(),
                    $('#alias').val(),
                    $('#published').val()
                ]
            );
    } else {
        FeedReaderAjax.callAsync(
            'UpdateFeed', [
                $('#id').val(),
                $('#title').val(),
                $('#url').val(),
                $('#cache_time').val(),
                $('#view_type').val(),
                $('#count_entry').val(),
                $('#title_view').val(),
                $('#alias').val(),
                $('#published').val()
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
    var answer = confirm(jaws.FeedReader.Defines.confirmFeedDelete);
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

$(document).ready(function() {
    initDataGrid('feedsites_datagrid', FeedReaderAjax);
});

var FeedReaderAjax = new JawsAjax('FeedReader', FeedReaderCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
