/**
 * FileBrowser Javascript actions
 *
 * @category   Ajax
 * @package    FileBrowser
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2010-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FileBrowserCallback = {
    updatedbfileinfo: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('fb_datagrid');
        }
        showResponse(response);
    },

    updatedbdirinfo: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('fb_datagrid');
        }
        showResponse(response);
    },

    deletefile: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('fb_datagrid');
        }
        showResponse(response);
    },

    deletedir: function(response) {
        if (response[0]['css'] == 'notice-message') {
            stopAction();
            getDG('fb_datagrid');
        }
        showResponse(response);
    }

};

/**
 * Select DataGrid row
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
 * Switch to a given tab (file or directory)
 */
function switchTab(tab, hideTabs)
{
    var fileDiv = $('f_work_area');
    var dirDiv  = $('d_work_area');
    var fileTab = $('fileTab');
    var dirTab  = $('dirTab');

    if (tab == 'file') {
        fileTab.className     = 'current';
        fileTab.style.display = 'inline';
        dirTab.className      = '';
        if (hideTabs) {
            dirTab.style.display = 'none';
        } else {
            dirTab.style.display = 'inline';
        }
        fileDiv.style.display = 'block';
        dirDiv.style.display  = 'none';
    } else if (tab == 'dir') {
        fileTab.className     = '';
        if (hideTabs) {
            fileTab.style.display = 'none';
        } else {
            fileTab.style.display = 'inline';
        }
        dirTab.className      = 'current';
        dirTab.style.display = 'inline';
        fileDiv.style.display = 'none';
        dirDiv.style.display  = 'block';
    }
}

/**
 */
function uploadswitch(checked)
{
    if (checked) {
        $('edit_entry').style.display = 'none';
        $('file_entry').style.display = 'block';
    } else {
        $('edit_entry').style.display = 'block';
        $('file_entry').style.display = 'none';
    }
}

/**
 * ReOrder list
 */
function reOrderFiles()
{
    getDG('fb_datagrid', 0, false);
}

/**
 * Change directory
 */
function cwd(dir)
{
    $('path').value = dir;
    getDG('fb_datagrid', 0, true);
}

/**
 * Edit a file
 */
function editFile(element, fname)
{
    $('oldname').value          = fname;
    $('filename').value         = fname;
    $('file_title').value       = '';
    $('file_description').value = '';
    $('file_fast_url').value    = '';
    selectDataGridRow(element.parentNode.parentNode);
    $('upload_switch').checked = false;
    uploadswitch(false);
    switchTab('file');
    $('upload_switch').disabled = false;

    var dbfile = filebrowserSync.dbfileinfo($('path').value, fname);
    if (dbfile['id']) {
        $('file_title').value       = dbfile['title'];
        $('file_description').value = dbfile['description'];
        $('file_fast_url').value    = dbfile['fast_url'];
    }
}

/**
 * Save/Upload file
 */
function saveFile()
{
    if ($('uploadfile').value.blank() && 
        $('filename').value.blank())
    {
        alert(incompleteFields);
        return false;
    }
    if ($('upload_switch').checked && 
       !$('uploadfile').value.blank())
    {
        document.fb_form.submit();
    } else {
        filebrowser.updatedbfileinfo(
                                $('path').value,
                                $('filename').value,
                                $('file_title').value,
                                $('file_description').value,
                                $('file_fast_url').value,
                                $('oldname').value
                                );
    }
}

/**
 * Delete a file
 */
function delFile(element, file)
{
    if (confirm(confirmFileDelete)) {
        filebrowser.deletefile($('path').value, file);
    }
}

/**
 * Edit a file
 */
function editDir(element, dirname)
{
    $('oldname').value         = dirname;
    $('dirname').value         = dirname;
    $('dir_title').value       = '';
    $('dir_description').value = '';
    $('dir_fast_url').value    = '';
    selectDataGridRow(element.parentNode.parentNode);
    switchTab('dir');

    var dbfile = filebrowserSync.dbfileinfo($('path').value, dirname);
    if (dbfile['id']) {
        $('dir_title').value       = dbfile['title'];
        $('dir_description').value = dbfile['description'];
        $('dir_fast_url').value    = dbfile['fast_url'];
    }
}

/**
 * Save/Upload file
 */
function saveDir()
{
    if ($('dirname').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    filebrowser.updatedbdirinfo(
                            $('path').value,
                            $('dirname').value,
                            $('dir_title').value,
                            $('dir_description').value,
                            $('dir_fast_url').value,
                            $('oldname').value
                            );
}

/**
 * Delete a directory
 */
function delDir(element, dir)
{
    if (confirm(confirmDirDelete)) {
        filebrowser.deletedir($('path').value, dir);
    }
    
}

/**
 * Get directories & files
 */
function getFiles(name, offset, reset)
{
    var result = filebrowserSync.getdirectory($('path').value, offset, $('order_type').value);
    if (reset) {
        var total = filebrowserSync.getdircontentscount($('path').value);
        var loc   = filebrowserSync.getlocation($('path').value);
        $('location').innerHTML = loc;
    }
    resetGrid(name, result, total);
}

/**
 * Clean the form
 *
 */
function stopAction(tab) 
{
    unselectDataGridRow();
    $('upload_switch').checked = true;
    uploadswitch(true);
    $('upload_switch').disabled = true;
    switchTab(tab);
    $('oldname').value          = '';

    $('filename').value         = '';
    $('uploadfile').value       = '';
    $('file_title').value       = '';
    $('file_description').value = '';
    $('file_fast_url').value    = '';

    $('dirname').value          = '';
    $('dir_title').value        = '';
    $('dir_description').value  = '';
    $('dir_fast_url').value     = '';
}

var filebrowser = new filebrowseradminajax(FileBrowserCallback);
filebrowser.serverErrorFunc = Jaws_Ajax_ServerError;
filebrowser.onInit = showWorkingNotification;
filebrowser.onComplete = hideWorkingNotification;

var filebrowserSync = new filebrowseradminajax();
filebrowserSync.serverErrorFunc = Jaws_Ajax_ServerError;
filebrowserSync.onInit = showWorkingNotification;
filebrowserSync.onComplete = hideWorkingNotification;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
