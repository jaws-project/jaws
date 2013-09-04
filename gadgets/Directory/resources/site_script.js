/**
 * Directory Javascript actions
 *
 * @category    Ajax
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var DirectoryCallback = {
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
 * Initiates Directory
 */
function initDirectory()
{
    comboFiles = $('files');
    currentDir = Number(fmStorage.fetch('current_dir'));
    changeDir(currentDir);
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
 * Sets selected file/directory ID for edit/delete operations
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
    comboFiles.selectedIndex = -1;
}

/**
 * Opens directory
 */
function openDir()
{
    if (fileById[comboFiles.value].is_dir) {
        changeDir(comboFiles.value);
    }
}

/**
 * Changes current path to the given directory
 */
function changeDir(id)
{
    selectedId = null;
    currentDir = id;
    fmStorage.update('current_dir', id)
    fillFilesCombo(currentDir);
    updatePath();
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
 * Displays the blank form to create a new directory
 */
function newDir()
{
    if (cachedDirectoryForm === null) {
        cachedDirectoryForm = fmAjax.callSync('GetDirectoryForm');
    }
    $('form').set('html', cachedDirectoryForm);
    comboFiles.selectedIndex = -1;
    $('frm_dir').title.focus();
    $('frm_dir').parent.value = currentDir;
}

/**
 * Goes to edit file or directory
 */
function edit()
{
    if (selectedId === null) return;
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
    if (cachedDirectoryForm === null) {
        cachedDirectoryForm = fmAjax.callSync('GetDirectoryForm');
    }
    $('form').set('html', cachedDirectoryForm);
    var form = $('frm_dir');
    form.action.value = 'UpdateDirectory';
    form.id.value = selectedId;
    form.title.value = data.title;
    form.description.value = data.description;
    form.parent.value = data.parent;
}

/**
 * Deletes selected file/directory
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
    $('form').set('html', cachedForm);
}

var fmAjax = new JawsAjax('Directory', DirectoryCallback),
    fmStorage = new JawsStorage('Directory'),
    comboFiles,
    fileById = {},
    selectedId,
    cachedDirectoryForm = null,
    cachedFileForm = null,
    currentDir = 0;
