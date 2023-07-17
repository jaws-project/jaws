/**
 * FileBrowser Javascript actions
 *
 * @category   Ajax
 * @package    FileBrowser
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2010-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var FileBrowserCallback = {
    UpdateDBFileInfo: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG('fb_datagrid');
        }
    },

    UpdateDBDirInfo: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG('fb_datagrid');
        }
    },

    DeleteFile2: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG('fb_datagrid');
        }
    },

    DeleteDir2: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG('fb_datagrid');
        }
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
    var fileDiv = $('#f_work_area'),
        dirDiv  = $('#d_work_area'),
        fileTab = $('#fileTab'),
        dirTab  = $('#dirTab');

    if (tab == 'file') {
        fileTab.addClass('current');
        fileTab.css('display', 'inline');
        dirTab.removeClass();
        if (hideTabs) {
            dirTab.css('display', 'none');
        } else {
            dirTab.css('display', 'inline');
        }
        fileDiv.css('display', 'block');
        dirDiv.css('display', 'none');
    } else if (tab == 'dir') {
        fileTab.removeClass();
        if (hideTabs) {
            fileTab.css('display', 'none');
        } else {
            fileTab.css('display', 'inline');
        }
        dirTab.addClass('current');
        dirTab.css('display', 'inline');
        fileDiv.css('display', 'none');
        dirDiv.css('display', 'block');
    }
}

/**
 *
 */
function uploadswitch(checked)
{
    if (checked) {
        $('#edit_entry').css('display', 'none');
        $('#file_entry').css('display', 'block');
    } else {
        $('#edit_entry').css('display', 'block');
        $('#file_entry').css('display', 'none');
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
    $('#path').val(dir);
    getDG('fb_datagrid', 0, true);
    stopAction('dir');
}

/**
 * Edit a file
 */
function editFile(element, fname)
{
    $('#oldname').val(fname);
    $('#filename').val(fname);
    $('#file_title').val('');
    $('#file_description').val('');
    $('#file_fast_url').val('');
    selectDataGridRow(element.parentNode.parentNode);
    $('upload_switch').prop('checked', false);
    uploadswitch(false);
    switchTab('file');
    $('#upload_switch').prop('disabled', false);

    var dbfile = FileBrowserAjax.call('DBFileInfo', [$('#path').val(), fname], false, {'async': false});
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
    if (!$('#uploadfile').val() &&
        !$('#filename').val())
    {
        alert(Jaws.gadgets.FileBrowser.defines.incompleteFields);
        return false;
    }
    if ($('#upload_switch').prop('checked') &&
       $('#uploadfile').val())
    {
        document.fb_form.submit();
    } else {
        FileBrowserAjax.call(
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
    if (confirm(Jaws.gadgets.FileBrowser.defines.confirmFileDelete)) {
        FileBrowserAjax.call('DeleteFile2', [$('#path').val(), file]);
    }
}

/**
 * Edit a file
 */
function editDir(element, dirname)
{
    $('#oldname').val(dirname);
    $('#dirname').val(dirname);
    $('#dir_title').val('');
    $('#dir_description').val('');
    $('#dir_fast_url').val('');
    selectDataGridRow(element.parentNode.parentNode);
    switchTab('dir');

    var dbfile = FileBrowserAjax.call('DBFileInfo', [$('#path').val(), dirname], false, {'async': false});
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
    if (!$('#dirname').val()) {
        alert(Jaws.gadgets.FileBrowser.defines.incompleteFields);
        return false;
    }

    FileBrowserAjax.call(
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
    if (confirm(Jaws.gadgets.FileBrowser.defines.confirmDirDelete)) {
        FileBrowserAjax.call('DeleteDir2', [$('#path').val(), dir]);
    }
    
}

/**
 * Get directories & files
 */
function getFiles(name, offset, reset)
{
    var result = FileBrowserAjax.call(
        'GetDirectory',
        [$('#path').val(), offset, $('#order_type').val()],
        false, {'async': false}
    );
    if (reset) {
        var total = FileBrowserAjax.call('GetDirContentsCount', $('#path').val(), false, {'async': false});
        var loc   = FileBrowserAjax.call('GetLocation', $('#path').val(), false, {'async': false});
        $('#location').html(loc);
    }
    resetGrid(name, result, total);
}

/**
 * Clean the form
 */
function stopAction(tab) 
{
    unselectDataGridRow();
    $('#upload_switch').prop('checked', true);
    uploadswitch(true);
    $('#upload_switch').prop('disabled', true);
    switchTab(tab);
    $('#oldname').val('');

    $('#filename').val('');
    $('#uploadfile').val('');
    $('#file_title').val('');
    $('#file_description').val('');
    $('#file_fast_url').val('');

    $('#dirname').val('');
    $('#dir_title').val('');
    $('#dir_description').val('');
    $('#dir_fast_url').val('');
}

$(document).ready(function () {
    $('#path').val(Jaws.gadgets.FileBrowser.defines.path);
    stopAction('file');
    initDataGrid('fb_datagrid', FileBrowserAjax, 'getFiles');
});

var FileBrowserAjax = new JawsAjax('FileBrowser', FileBrowserCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
