/**
 * FileBrowser Javascript actions
 *
 * @category   Ajax
 * @package    FileBrowser
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2010-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FileBrowserCallback = {
    UpdateDBFileInfo: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG('fb_datagrid');
        }
        FileBrowserAjax.showResponse(response);
    },

    UpdateDBDirInfo: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG('fb_datagrid');
        }
        FileBrowserAjax.showResponse(response);
    },

    DeleteFile2: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG('fb_datagrid');
        }
        FileBrowserAjax.showResponse(response);
    },

    DeleteDir2: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG('fb_datagrid');
        }
        FileBrowserAjax.showResponse(response);
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
        $('edit_entry').css('display', 'none');
        $('file_entry').css('display', 'block');
    } else {
        $('edit_entry').css('display', 'block');
        $('file_entry').css('display', 'none');
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
    stopAction('dir');
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

    var dbfile = FileBrowserAjax.callSync('DBFileInfo', [$('#path').val(), fname]);
    if (dbfile['id']) {
        $('#file_title').val(dbfile['title'].defilter());
        $('#file_description').val(dbfile['description'].defilter());
        $('#file_fast_url').val(dbfile['fast_url']);
    }
}

/**
 * Save/Upload file
 */
function saveFile()
{
    if (!$('uploadfile').val() && 
        !$('filename').val())
    {
        alert(incompleteFields);
        return false;
    }
    if ($('upload_switch').checked && 
       $('uploadfile').val())
    {
        document.fb_form.submit();
    } else {
        FileBrowserAjax.callAsync(
            'UpdateDBFileInfo', [
                $('#path').val(),
                $('#filename').val(),
                $('#file_title').val(),
                $('#file_description').val(),
                $('#file_fast_url').val(),
                $('#oldname').val()
            ]
        );
    }
}

/**
 * Delete a file
 */
function delFile(element, file)
{
    if (confirm(confirmFileDelete)) {
        FileBrowserAjax.callAsync('DeleteFile2', [$('#path').val(), file]);
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

    var dbfile = FileBrowserAjax.callSync('DBFileInfo', [$('#path').val(), dirname]);
    if (dbfile['id']) {
        $('#dir_title').val(dbfile['title']);
        $('#dir_description').val(dbfile['description']);
        $('#dir_fast_url').val(dbfile['fast_url']);
    }
}

/**
 * Save/Upload file
 */
function saveDir()
{
    if (!$('dirname').val()) {
        alert(incompleteFields);
        return false;
    }

    FileBrowserAjax.callAsync(
        'UpdateDBDirInfo', [
            $('#path').val(),
            $('#dirname').val(),
            $('#dir_title').val(),
            $('#dir_description').val(),
            $('#dir_fast_url').val(),
            $('#oldname').val()
        ]
    );
}

/**
 * Delete a directory
 */
function delDir(element, dir)
{
    if (confirm(confirmDirDelete)) {
        FileBrowserAjax.callAsync('DeleteDir2', [$('#path').val(), dir]);
    }
    
}

/**
 * Get directories & files
 */
function getFiles(name, offset, reset)
{
    var result = FileBrowserAjax.callSync(
        'GetDirectory',
        [$('#path').val(), offset, $('#order_type').val()]
    );
    if (reset) {
        var total = FileBrowserAjax.callSync('GetDirContentsCount', $('#path').val());
        var loc   = FileBrowserAjax.callSync('GetLocation', $('#path').val());
        $('location').html(loc);
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

var FileBrowserAjax = new JawsAjax('FileBrowser', FileBrowserCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
