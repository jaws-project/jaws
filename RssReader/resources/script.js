/**
 * RSSReader Javascript actions
 *
 * @category   Ajax
 * @package    RssReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var RssReaderCallback = { 
    deleterss: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            $('rsssites_datagrid').deleteItem();          
            getDG();
        }
        showResponse(response);
    },
    
    insertrss: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('rsssites_datagrid').addItem();
            $('rsssites_datagrid').setCurrentPage(0);
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    updaterss: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getDG();
        }
        stopAction();
        showResponse(response);
    },

    getrss: function(response) {
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
function updateForm(rssInfo)
{
    $('id').value          = rssInfo['id'];
    $('title').value       = rssInfo['title'];
    $('url').value         = rssInfo['url'];
    $('cache_time').value  = rssInfo['cache_time'];
    $('view_type').value   = rssInfo['view_type'];
    $('count_entry').value = rssInfo['count_entry'];
    $('title_view').value  = rssInfo['title_view'];
    $('visible').value     = rssInfo['visible'];
    $('btn_cancel').style.visibility = 'visible';
}

/**
 * Add/Update a RSS
 */
function updateRSS()
{
    if ($('title').value.blank() ||
        $('url').value.blank() ||
        !isValidURL($('url').value.trim()))
    {
        alert(incompleteFeedFields);
        return;
    }

    if($('id').value==0) {
        rssreader.insertrss(
                        $('title').value,
                        $('url').value,
                        $('cache_time').value,
                        $('view_type').value,
                        $('count_entry').value,
                        $('title_view').value,
                        $('visible').value);
    } else {
        rssreader.updaterss(
                        $('id').value,
                        $('title').value,
                        $('url').value,
                        $('cache_time').value,
                        $('view_type').value,
                        $('count_entry').value,
                        $('title_view').value,
                        $('visible').value);
    }
}

/**
 * Delete a RSS
 */
function deleteRSS(element, id)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    var answer = confirm(confirmFeedDelete);
    if (answer) {
        rssreader.deleterss(id);
    }
    unselectDataGridRow();
}

/**
 * Edit a RSS
 *
 */
function editRSS(element, id)
{
    selectDataGridRow(element.parentNode.parentNode);
    rssreader.getrss(id);
}

var rssreader = new rssreaderadminajax(RssReaderCallback);
rssreader.serverErrorFunc = Jaws_Ajax_ServerError;
rssreader.onInit = showWorkingNotification;
rssreader.onComplete = hideWorkingNotification;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
