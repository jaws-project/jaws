/**
 * Phoo Javascript actions
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PhooCallback = {

    importimage: function(response) {
        currentIndex++;
        ImportImages();
    },

    deletecomment: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            $('comments_datagrid').deleteItem();
            var limit = $('comments_datagrid').getCurrentPage();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        }
    },

    deletecomments: function(response) {
        if (response[0]['css'] == 'notice-message') {
            var rows = $('comments_datagrid').getSelectedRows();
            if (rows.length > 0) {
                for(var i=0; i<rows.length; i++) {
                    $('comments_datagrid').deleteItem();
                }
            }
            PiwiGrid.multiSelect($('comments_datagrid'));
            var limit = $('comments_datagrid').getCurrentPage();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(limit, formData['filter'],
                                   formData['search'], formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('comments_datagrid'));
        }
        showResponse(response);
    },

    markas: function(response) {
        if (response[0]['css'] == 'notice-message') {
            PiwiGrid.multiSelect($('comments_datagrid'));
            resetLCForm();
            var formData = getDataOfLCForm();
            updateCommentsDatagrid(0,
                                   formData['filter'],
                                   formData['search'],
                                   formData['status'],
                                   true);
        } else {
            PiwiGrid.multiSelect($('comments_datagrid'));
        }
        showResponse(response);
    },

    updatephoto: function(response) {
        showResponse(response);
    }
}

function gotoLocation(album)
{
    window.location= base_script + '&action=AdminPhotos&album=' + album;
}

/**
 * Function to import images from data/phoo/import
 */
function ImportImages()
{
    if (((currentIndex + 1) <= howmany) && (items[currentIndex]['image'])) {
        $('nofm').innerHTML = (currentIndex + 1) + ' / ' + howmany;
        var percent = Math.round(((currentIndex + 1) * 100) / howmany);

        $('percent').innerHTML = percent + '%';
        $('img_percent').setAttribute('style', 'width:' + percent + '%;');
        var phoo = new phooadminajax(PhooCallback);
        phoo.importimage(items[currentIndex]['image'], items[currentIndex]['name'], album);
    } else {
        if (currentIndex == howmany) {
            $('nofm').innerHTML = finished_message;
            $('indicator').src = ok_image;
            new Effect.Fade($('warning'));
        }
    }
}


/**
 * Reset ListComments form
 */
function resetLCForm()
{
    var form = document.forms['ListComments'];
    form.elements['filterby'].value = '';
    form.elements['filter'].value   = '';
    form.elements['status'].value   = 'approved';
}

/**
 * Get data of the form ListComments form
 */
function getDataOfLCForm()
{
    var form = document.forms['ListComments'];

    var data = new Array();

    data['filter']   = form.elements['filterby'].value;
    data['search']   = form.elements['filter'].value;
    data['status']   = form.elements['status'].value;

    return data;
}

/**
 * Delete a comment
 */
function deleteComment(id)
{
    phoo.deletecomment(id);
}

/**
 * search for a comment
 */
function searchComment()
{
    var formData = getDataOfLCForm();
    updateCommentsDatagrid(0, formData['filter'], formData['search'], formData['status'], true);
    return false;
}

/**
 * Get posts data
 */
function getData(limit)
{
    if (limit == undefined) {
        limit = $('comments_datagrid').getCurrentPage();
    }

    var formData = getDataOfLCForm();
    updateCommentsDatagrid(limit, formData['filter'],
                           formData['search'], formData['status'],
                           false);
}

/**
 * Get first values of comments
 */
function firstValues()
{
    var firstValues = $('comments_datagrid').getFirstPagerValues();
    getData(firstValues);
    $('comments_datagrid').firstPage();
}

/**
 * Get previous values of comments
 */
function previousValues()
{
    var previousValues = $('comments_datagrid').getPreviousPagerValues();
    getData(previousValues);
    $('comments_datagrid').previousPage();
}

/**
 * Get next values of comments
 */
function nextValues()
{
    var nextValues = $('comments_datagrid').getNextPagerValues();
    getData(nextValues);
    $('comments_datagrid').nextPage();
}

/**
 * Get last values of comments
 */
function lastValues()
{
    var lastValues = $('comments_datagrid').getLastPagerValues();
    getData(lastValues);
    $('comments_datagrid').lastPage();
}

/**
 * Update comments datagrid
 */
function updateCommentsDatagrid(limit, filter, search, status, resetCounter)
{
    result = phooSync.searchcomments(limit, filter, search, status);
    resetGrid('comments_datagrid', result);
    if (resetCounter) {
        var size = phooSync.sizeofcommentssearch(filter, search, status);
        $('comments_datagrid').rowsSize    = size;
        $('comments_datagrid').setCurrentPage(0);
        $('comments_datagrid').updatePageCounter();
    }
}

/**
 * Delete comment
 */
function commentDelete(row_id)
{
    var confirmation = confirm(deleteConfirm);
    if (confirmation) {
        phoo.deletecomments(row_id);
    }
}

/**
 * Executes an action on comments
 */
function commentDGAction(combo)
{
    var rows = $('comments_datagrid').getSelectedRows();
    var selectedRows = false;
    if (rows.length > 0) {
        selectedRows = true;
    }

    if (combo.value == 'delete') {
        if (selectedRows) {
            var confirmation = confirm(deleteConfirm);
            if (confirmation) {
                phoo.deletecomments(rows);
            }
        }
    } else {
        if (selectedRows) {
            phoo.markas(rows, combo.value);
        }
    }
}

function updatePhoto()
{
    var id             = document.forms['EditPhoto'].elements['image'].value;
    var title          = document.forms['EditPhoto'].elements['title'].value;
    var allow_comments = document.forms['EditPhoto'].elements['allow_comments[]'].checked;
    var published      = document.forms['EditPhoto'].elements['published'].value;
    var description    = getEditorValue('description');

    var albumsNode  = $('album-checkboxes').getElementsByTagName('input');
    var albums      = new Array();
    var albmCounter = 0;
    for(var i = 0; i < albumsNode.length; i++) {
        if (albumsNode[i].checked) {
            albums[albmCounter] = albumsNode[i].value;
            albmCounter++;
        }
    }

    phoo.updatephoto(id, title, description, allow_comments, published, albums);
}

/**
 * add a file entry
 */
function addEntry(title)
{
    num_entries++;
    id = num_entries;
    entry = '<label id="photo' + id + '_label" for="photo' + id + '">' + title + ' ' + id + ':&nbsp;</label>';
    entry += '<input type="file" name="photo' + id + '" id="photo' + id + '" title="Photo ' + id + '" /><br />';
    $('phoo_addentry' + id).innerHTML = entry + '<span id="phoo_addentry' + (id + 1) + '">' + $('phoo_addentry' + id).innerHTML + '</span>';
}

var num_entries = 5;
var phoo = new phooadminajax(PhooCallback);
phoo.serverErrorFunc = Jaws_Ajax_ServerError;
phoo.onInit = showWorkingNotification;
phoo.onComplete = hideWorkingNotification;

var phooSync = new phooadminajax();
phooSync.serverErrorFunc = Jaws_Ajax_ServerError;
phooSync.onInit = showWorkingNotification;
phooSync.onComplete = hideWorkingNotification;

var firstFetch = true;
var currentIndex = 0;
