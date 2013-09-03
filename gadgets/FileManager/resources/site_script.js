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
            fillFilesCombo(currentDir);
        }
        $('simple_response').set('html', response);
    }
}

/**
 * Initiates FileManager
 */
function initFileManager()
{
    comboFiles = $('files');
    currentDir = 0;
    fillFilesCombo(currentDir);
}

/**
 * Fills files combobox
 */
function fillFilesCombo(parent)
{
    var files = fmAjax.callSync('GetFiles', parent);
    if (files) {
        comboFiles.options.length = 0;
        files.each(function (file) {
            fileById[file.id] = file;
            comboFiles.options[comboFiles.options.length] = new Option(file.title, file.id);
        });
    }
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
 * Opens directory
 */
function openFile()
{
    if (fileById[comboFiles.value].is_dir) {
        changeDir(comboFiles.value);
        updatePath();
    }
}

/**
 * Builds the directory path
 */
function updatePath()
{
    var pathArr = fmAjax.callSync('GetPath', currentDir),
        path = $('path').set('html', ''),
        link = new Element('span', {'html':'Root'});
    link.addEvent('click', changeDir.pass(0));
    path.grab(link);
    pathArr.reverse().each(function (dir, i) {
        path.appendText(' > ');
        if (i === pathArr.length - 1) {
            path.appendText(dir.title);
        } else {
            link = new Element('span');
            link.set('html', dir.title);
            link.addEvent('click', changeDir.pass(dir.id));
            path.grab(link);
        }
    });
}

/**
 * Changes current path to the given directory
 */
function changeDir(id)
{
    currentDir = id;
    selectedId = null;
    fillFilesCombo(currentDir);
    updatePath();
}

/**
 * Deselects file and hides the form
 */
function cancel()
{
    selectedId = null;
    $('form').set('html', '');
    comboFiles.selectedIndex = -1;
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
    comboFiles.selectedIndex = -1;
    $('frm_dir').title.focus();
    $('frm_dir').parent.value = currentDir;
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
    form.action.value = 'UpdateDir';
    form.id.value = selectedId;
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
    fileById = {},
    selectedId,
    cachedDirForm = null,
    cachedFileForm = null,
    currentDir = 0;
