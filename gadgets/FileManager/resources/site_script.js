/**
 * FileManager Javascript actions
 *
 * @category    Ajax
 * @package     FileManager
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FileManagerCallback = {
    DeleteFile: function(response) {
        console.log(response);
        if (response) {
            cancel();
            initFileManager();
        }
        $('simple_response').set('html', response);
    }
}

/**
 * Initiates FileManager
 */
function initFileManager()
{
    var files = fmAjax.callSync('GetFiles', 0);
    comboFiles = $('files');
    if (files) {
        fillFilesCombo(files);
    }
}

/**
 * Fills files combobox
 */
function fillFilesCombo(files)
{
    comboFiles.options.length = 0;
    files.each(function (file) {
        comboFiles.options[comboFiles.options.length] = new Option(file.title, file.id);
    });
}

/**
 * Sets selected file/dir ID for edit/delete operations
 */
function selectFile()
{
    selectedId = comboFiles.value;
    $('form').set('html', '');
}

/**
 * Deselects file and hides the form
 */
function cancel()
{
    selectedId = null;
    $('form').set('html', '');
    $('files').selectedIndex = -1;
}

/**
 * Displays the blank form to create a new directory
 */
function newDir()
{
    if (cachedDirForm === null) {
        cachedDirForm = fmAjax.callSync('GetDirForm');
    }
    $('form').set('html', cachedDirForm);
    $('files').selectedIndex = -1;
    $('frm_dir').title.focus();
}

/**
 * Goes to edit file or directory
 */
function edit()
{
    var data = fmAjax.callSync('GetFile', selectedId);
    switch (data.is_dir) {
        case true:
            editDir(data);
            break;
        case false:
            editFile(data);
            break;
    }
}

/**
 * Displays the form with data of selected directory to be edited
 */
function editDir(data)
{
    if (cachedDirForm === null) {
        cachedDirForm = fmAjax.callSync('GetDirForm');
    }
    $('form').set('html', cachedDirForm);
    var form = $('frm_dir');
    //console.log(form.title);
    form.title.value = data.title;
    form.description.value = data.description;
    form.parent.value = data.parent;
    form.published.value = (data.published === true)? 1 : 0;
}

/**
 * Deletes selected file/dir
 */
function deleteFile()
{
    if (confirm('Are you sure you want to delete selected item?')) {
        fmAjax.callAsync('DeleteFile', selectedId);
    }
}

/**
 * Sets selected file for edit/delete
 */
function newFile()
{
    if (cachedForm === null) {
        cachedForm = fmAjax.callSync('GetFileForm');
    }
    //console.log(cachedForm);
    $('form').set('html', cachedForm);
}

var fmAjax = new JawsAjax('FileManager', FileManagerCallback),
    comboFiles,
    selectedId,
    cachedDirForm = null,
    cachedFileForm = null;
