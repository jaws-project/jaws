/**
 * FeedReader Javascript actions
 *
 * @category   Ajax
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FeedReaderCallback = { 
    deletefeed: function(response) {
        if (response['css'] == 'notice-message') {
            stopAction();
            _('feedsites_datagrid').deleteItem();          
            getDG();
        }
        showResponse(response);
    },
    
    insertfeed: function(response) {
        if (response['css'] == 'notice-message') {
            _('feedsites_datagrid').addItem();
            _('feedsites_datagrid').setCurrentPage(0);
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    updatefeed: function(response) {
        if (response['css'] == 'notice-message') {
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    getfeed: function(response) {
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
    _('id').value          = '0';
    _('title').value       = '';
    _('url').value         = 'http://';
    _('cache_time').value  = '3600';
    _('view_type').value   = '0';
    _('count_entry').value = '';
    _('title_view').value  = '0';
    _('visible').value     = '1';
    unselectDataGridRow();
    _('btn_cancel').style.visibility = 'hidden';
}

/**
 * Update form with new values
 *
 */
function updateForm(feed)
{
    _('id').value          = feed['id'];
    _('title').value       = feed['title'].defilter();
    _('url').value         = feed['url'];
    _('cache_time').value  = feed['cache_time'];
    _('view_type').value   = feed['view_type'];
    _('count_entry').value = feed['count_entry'];
    _('title_view').value  = feed['title_view'];
    _('visible').value     = feed['visible'];
    _('btn_cancel').style.visibility = 'visible';
}

/**
 * Add/Update a feed
 */
function updateFeed()
{
    if (_('title').value.blank() ||
        _('url').value.blank() ||
        !isValidURL(_('url').value.trim()))
    {
        alert(incompleteFeedFields);
        return;
    }

    if(_('id').value==0) {
            FeedReaderAjax.callAsync('insertfeed',
                                _('title').value,
                                _('url').value,
                                _('cache_time').value,
                                _('view_type').value,
                                _('count_entry').value,
                                _('title_view').value,
                                _('visible').value);
    } else {
        FeedReaderAjax.callAsync('updatefeed',
                            _('id').value,
                            _('title').value,
                            _('url').value,
                            _('cache_time').value,
                            _('view_type').value,
                            _('count_entry').value,
                            _('title_view').value,
                            _('visible').value);
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
        FeedReaderAjax.callAsync('deletefeed', id);
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
    FeedReaderAjax.callAsync('getfeed', id);
}

var FeedReaderAjax = new JawsAjax('FeedReader', FeedReaderCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
