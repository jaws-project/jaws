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
    DeleteDirectory: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            fillFilesCombo(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    DeleteFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            fillFilesCombo(currentDir);
        }
        $('simple_response').set('html', response.message);
    }
}

/**
 * Initiates Directory
 */
function initDirectory()
{
    comboFiles = $('files');
    currentDir = Number(fmStorage.fetch('current_dir'));
    changeDirectory(currentDir);
    imgDeleteFile = new Element('img', {src:imgDeleteFile});
    imgDeleteFile.addEvent('click', removeFile);
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
function openDirectory()
{
    if (fileById[comboFiles.value].is_dir) {
        changeDirectory(comboFiles.value);
    }
}

/**
 * Changes current path to the given directory
 */
function changeDirectory(id)
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
    link.addEvent('click', changeDirectory.pass(0));
    path.grab(link);
    pathArr.reverse().each(function (dir, i) {
        path.appendText(' > ');
        if (i === pathArr.length - 1) {
            path.appendText(dir.title);
        } else {
            link = new Element('span');
            link.set('html', dir.title);
            link.addEvent('click', changeDirectory.pass(dir.id));
            path.grab(link);
        }
    });
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
            editDirectory(data);
            break;
        case false:
            editFile(data);
            break;
    }
}

/**
 * Deletes selected directory/file
 */
function _delete()
{
    if (selectedId === null) return;
    var form = $('frm_files');
    if (fileById[comboFiles.value].is_dir) {
        if (confirm('Are you sure you want to delete directory?')) {
            //fmAjax.callAsync('DeleteDirectory', selectedId);
            form.action.value = 'DeleteDirectory';
            form.submit();
        }
    } else {
        if (confirm('Are you sure you want to delete file?')) {
            //fmAjax.callAsync('DeleteFile', selectedId);
            form.action.value = 'DeleteFile';
            form.submit();
        }
    }
}

/**
 * Displays the blank form to create a new directory
 */
function newDirectory()
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
 * Goes for editing selected directory
 */
function editDirectory(data)
{
    if (cachedDirectoryForm === null) {
        cachedDirectoryForm = fmAjax.callSync('GetDirectoryForm');
    }
    $('form').set('html', cachedDirectoryForm);
    var form = $('frm_dir');
    form.action.value = 'UpdateFile';
    form.id.value = selectedId;
    form.title.value = data.title;
    form.description.value = data.description;
    form.parent.value = data.parent;
}

/**
 * Sets selected file for edit/delete
 */
function newFile()
{
    if (cachedFileForm === null) {
        cachedFileForm = fmAjax.callSync('GetFileForm');
    }
    $('form').set('html', cachedFileForm);
    comboFiles.selectedIndex = -1;
    $('frm_file').title.focus();
    $('frm_file').parent.value = currentDir;
}

/**
 * Goes for editing selected file
 */
function editFile(data)
{
    if (cachedFileForm === null) {
        cachedFileForm = fmAjax.callSync('GetFileForm');
    }
    $('form').set('html', cachedFileForm);
    var form = $('frm_file');
    form.action.value = 'UpdateFile';
    form.id.value = selectedId;
    form.title.value = data.title;
    form.description.value = data.description;
    form.url.value = data.url;
    form.parent.value = data.parent;
    if (data.filename) {
        $('filename').set('html', data.filename);
        $('filename').grab(imgDeleteFile);
        $('filename').show();
        $('file').hide();
    }
}

/**
 * Removes attached file
 */
function removeFile()
{
    $('filename').set('html', '');
    $('filename').hide();
    $('file').show();
}

var fmAjax = new JawsAjax('Directory', DirectoryCallback),
    fmStorage = new JawsStorage('Directory'),
    comboFiles,
    fileById = {},
    selectedId,
    cachedDirectoryForm = null,
    cachedFileForm = null,
    currentDir = 0;
