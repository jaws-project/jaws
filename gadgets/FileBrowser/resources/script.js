/**
 * FileBrowser Javascript actions
 *
 * @category   Ajax
 * @package    FileBrowser
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2010-2013 Jaws Development Group
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
    var fileDiv = _('f_work_area');
    var dirDiv  = _('d_work_area');
    var fileTab = _('fileTab');
    var dirTab  = _('dirTab');

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
        _('edit_entry').style.display = 'none';
        _('file_entry').style.display = 'block';
    } else {
        _('edit_entry').style.display = 'block';
        _('file_entry').style.display = 'none';
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
    _('path').value = dir;
    getDG('fb_datagrid', 0, true);
    stopAction('dir');
}

/**
 * Edit a file
 */
function editFile(element, fname)
{
    _('oldname').value          = fname;
    _('filename').value         = fname;
    _('file_title').value       = '';
    _('file_description').value = '';
    _('file_fast_url').value    = '';
    selectDataGridRow(element.parentNode.parentNode);
    _('upload_switch').checked = false;
    uploadswitch(false);
    switchTab('file');
    _('upload_switch').disabled = false;

    var dbfile = FileBrowserAjax.callSync('dbfileinfo', _('path').value, fname);
    if (dbfile['id']) {
        _('file_title').value       = dbfile['title'].defilter();
        _('file_description').value = dbfile['description'].defilter();
        _('file_fast_url').value    = dbfile['fast_url'];
    }
}

/**
 * Save/Upload file
 */
function saveFile()
{
    if (_('uploadfile').value.blank() && 
        _('filename').value.blank())
    {
        alert(incompleteFields);
        return false;
    }
    if (_('upload_switch').checked && 
       !_('uploadfile').value.blank())
    {
        document.fb_form.submit();
    } else {
        FileBrowserAjax.callAsync('updatedbfileinfo',
                                _('path').value,
                                _('filename').value,
                                _('file_title').value,
                                _('file_description').value,
                                _('file_fast_url').value,
                                _('oldname').value
                                );
    }
}

/**
 * Delete a file
 */
function delFile(element, file)
{
    if (confirm(confirmFileDelete)) {
        FileBrowserAjax.callAsync('deletefile', _('path').value, file);
    }
}

/**
 * Edit a file
 */
function editDir(element, dirname)
{
    _('oldname').value         = dirname;
    _('dirname').value         = dirname;
    _('dir_title').value       = '';
    _('dir_description').value = '';
    _('dir_fast_url').value    = '';
    selectDataGridRow(element.parentNode.parentNode);
    switchTab('dir');

    var dbfile = FileBrowserAjax.callSync('dbfileinfo', _('path').value, dirname);
    if (dbfile['id']) {
        _('dir_title').value       = dbfile['title'];
        _('dir_description').value = dbfile['description'];
        _('dir_fast_url').value    = dbfile['fast_url'];
    }
}

/**
 * Save/Upload file
 */
function saveDir()
{
    if (_('dirname').value.blank()) {
        alert(incompleteFields);
        return false;
    }

    FileBrowserAjax.callAsync('updatedbdirinfo',
                            _('path').value,
                            _('dirname').value,
                            _('dir_title').value,
                            _('dir_description').value,
                            _('dir_fast_url').value,
                            _('oldname').value
                            );
}

/**
 * Delete a directory
 */
function delDir(element, dir)
{
    if (confirm(confirmDirDelete)) {
        FileBrowserAjax.callAsync('deletedir', _('path').value, dir);
    }
    
}

/**
 * Get directories & files
 */
function getFiles(name, offset, reset)
{
    var result = FileBrowserAjax.callSync('getdirectory', _('path').value, offset, _('order_type').value);
    if (reset) {
        var total = FileBrowserAjax.callSync('getdircontentscount', _('path').value);
        var loc   = FileBrowserAjax.callSync('getlocation', _('path').value);
        _('location').innerHTML = loc;
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
    _('upload_switch').checked = true;
    uploadswitch(true);
    _('upload_switch').disabled = true;
    switchTab(tab);
    _('oldname').value          = '';

    _('filename').value         = '';
    _('uploadfile').value       = '';
    _('file_title').value       = '';
    _('file_description').value = '';
    _('file_fast_url').value    = '';

    _('dirname').value          = '';
    _('dir_title').value        = '';
    _('dir_description').value  = '';
    _('dir_fast_url').value     = '';
}

var FileBrowserAjax = new JawsAjax('FileBrowser', FileBrowserCallback);

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
