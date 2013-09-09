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
    CreateDirectory: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    UpdateDirectory: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    DeleteDirectory: function(response) {
        if (response.css === 'notice-message') {
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    CreateFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    UpdateFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    DeleteFile: function(response) {
        if (response.css === 'notice-message') {
            displayFiles(currentDir);
        }
        //$('simple_response').set('html', response.message);
    },

    UpdateFileUsers: function(response) {
        if (response.css === 'notice-message') {
            cancel();
        }
        //$('simple_response').set('html', response.message);
    }
}

/**
 * Initiates Directory
 */
function initDirectory()
{
    DirectoryAjax.backwardSupport();
    imgDeleteFile = new Element('img', {src:imgDeleteFile});
    imgDeleteFile.addEvent('click', removeFile);
    currentDir = Number(DirectoryStorage.fetch('current_dir'));
    fileTemplate = $('workspace').get('html');
    statusTemplate = $('statusbar').get('html');
    $('workspace').addEvent('click', cancel);
    openDirectory(currentDir);
}

/**
 * Displays files and directories
 */
function displayFiles(parent)
{
    var ws = $('workspace'),
        files = DirectoryAjax.callSync('GetFiles', {'parent':parent});
    ws.empty();
    fileById = {};
    filesCount = files.length;
    files.each(function (file) {
        file.type = file.is_dir? 'folder' : 'file'
        ws.grab(getFileElement(file));
    });

    $('statusbar').set('html', filesCount + ' items').show();
}

/**
 * Builds a file element from passed data
 */
function getFileElement(fileData)
{
    var html = fileTemplate.substitute(fileData),
        div = new Element('div', {'html':html}).getFirst();
    div.addEvent('click', fileSelect);
    div.addEvent('dblclick', fileOpen);
    div.data = {};
    div.data.id = fileData.id;
    return div;
}

/**
 * Fetches and displays details on a file/directory
 */
function fileSelect(e)
{
    var ws = $('workspace'),
        st = $('statusbar');
    cancel();
    this.addClass('selected');
    selectedId = this.data.id;
    if (!fileById[selectedId]) {
        fileById[selectedId] = DirectoryAjax.callSync('GetFile', {id:selectedId});
    }
    st.set('html', statusTemplate.substitute(fileById[selectedId]));
    e.stop();
    updateActions();

    /*if (data.is_dir) {
        if (!cachedForms.viewDir) {
            cachedForms.viewDir = DirectoryAjax.callSync('DirectoryForm', {type:'view'});
        }
        var form = cachedForms.viewDir;
    } else {
        if (!cachedForms.viewFile) {
            cachedForms.viewFile = DirectoryAjax.callSync('FileForm', {type:'view'});
        }
        var form = cachedForms.viewFile;
    }
    $('form').set('html', form.substitute(data));
    if (data.filename) {
        $('filelink').grab(getDownloadLink(data.filename, data_url));
    }*/
}

/**
 * Opens directory
 */
function fileOpen()
{
    if (fileById[selectedId].is_dir) {
        openDirectory(selectedId);
    }
}

/**
 * Navigates to the given directory
 */
function openDirectory(id)
{
    currentDir = id;
    selectedId = null;
    DirectoryStorage.update('current_dir', id);
    displayFiles(id);
    updatePath();
    cancel();
}

/**
 * Builds the directory path
 */
function updatePath()
{
    var pathArr = DirectoryAjax.callSync('GetPath', {'id':currentDir}),
        path = $('path').set('html', ''),
        link = new Element('span', {'html':'My Drive'});
    link.addEvent('click', openDirectory.pass(0));
    path.grab(link);
    pathArr.reverse().each(function (dir, i) {
        path.appendText(' > ');
        if (i === pathArr.length - 1) {
            path.appendText(dir.title);
        } else {
            link = new Element('span');
            link.set('html', dir.title);
            link.addEvent('click', openDirectory.pass(dir.id));
            path.grab(link);
        }
    });
}

/**
 * Deselects file and hides the form
 */
function cancel()
{
    selectedId = null;
    $('form').set('html', '');
    $('workspace').getElements('.selected').removeClass('selected');
    $('statusbar').set('html', filesCount + ' items').show();
    updateActions();
}

/**
 * Shows/Hides appropriate buttons
 */
function updateActions()
{
    if (selectedId === null) {
        $('file_actions').hide();
    } else {
        $('file_actions').show();
    }
}

/**
 * Calls file/directory edit function
 */
function edit()
{
    if (selectedId === null) return;
    if (fileById[selectedId].is_dir) {
        editDirectory();
    } else {
        editFile();
    }
}

/**
 * Deletes selected directory/file
 */
function _delete()
{
    if (selectedId === null) return;
    if (fileById[selectedId].is_dir) {
        if (confirm('Are you sure you want to delete directory?')) {
            DirectoryAjax.callAsync('DeleteDirectory', {'id':selectedId});
        }
    } else {
        if (confirm('Are you sure you want to delete file?')) {
            DirectoryAjax.callAsync('DeleteFile', {'id':selectedId});
        }
    }
}

/**
 * Brings the directory creation UI up
 */
function newDirectory()
{
    selectedId = null;
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {type:'edit'});
    }
    $('form').set('html', cachedForms.editDir);
    $('frm_dir').title.focus();
    $('frm_dir').parent.value = currentDir;
}

/**
 * Brings the edit directory UI up
 */
function editDirectory()
{
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {type:'edit'});
    }
    $('form').set('html', cachedForms.editDir);
    //var data = DirectoryAjax.callSync('GetFile', {'id':selectedId});
    var data = fileById[selectedId];
    var form = $('frm_dir');
    form.id.value = selectedId;
    form.title.value = data.title;
    form.description.value = data.description;
    form.parent.value = data.parent;
}

/**
 * Brings the file creation UI up
 */
function newFile()
{
    selectedId = null;
    if (!cachedForms.editFile) {
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {type:'edit'});
    }
    $('form').set('html', cachedForms.editFile);
    $('tr_file').hide();
    $('frm_upload').show();
    $('frm_file').parent.value = currentDir;
    $('frm_file').title.focus();
}

/**
 * Brings the edit file UI up
 */
function editFile()
{
    if (!cachedForms.editFile) {
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {type:'edit'});
    }
    $('form').set('html', cachedForms.editFile);
    var data = fileById[selectedId];
    var form = $('frm_file');
    form.action.value = 'UpdateFile';
    form.id.value = selectedId;
    form.title.value = data.title;
    form.description.value = data.description;
    form.url.value = data.url;
    form.parent.value = data.parent;
    if (data.filename) {
        setFile(data.filename, data_url);
        $('filename').value = ':nochange:';
    } else {
        $('tr_file').hide();
        $('frm_upload').show();
    }
}

/**
 * Uploads file on the server
 */
function uploadFile() {
    var iframe = new Element('iframe', {id:'ifrm_upload'});
    document.body.grab(iframe);
    $('frm_upload').submit();
}

/**
 * Applies uploaded file into the form
 */
function onUpload(response) {
    if (response.type === 'error') {
        alert(response.message);
        $('frm_upload').reset();
    } else {
        var filename = encodeURIComponent(response.message);
        setFile(filename, '');
        $('filename').value = filename;
        if ($('frm_file').title.value == '') {
            $('frm_file').title.value = filename;
        }
    }
    $('ifrm_upload').destroy();
}

/**
 * Creates a link to attached file
 */
function getDownloadLink(filename, url)
{
    var link = new Element('a', {'html':filename, 'target':'_blank'});
    if (url !== '') {
        link.href = url + '/' + filename;
    }
    return link;
    $('filelink').grab(link);
    $('filelink').grab(imgDeleteFile);
    $('tr_file').show();
    $('frm_upload').hide();
    $('filename').value = filename;
}

/**
 * Creates a link to the attached file
 */
function setFile(filename, url)
{
    var link = new Element('a', {'html':filename, 'target':'_blank'});
    if (url !== '') {
        link.href = url + '/' + filename;
    }
    $('filelink').grab(link);
    $('filelink').grab(imgDeleteFile);
    $('tr_file').show();
    $('frm_upload').hide();
}

/**
 * Removes the attached file
 */
function removeFile()
{
    $('filename').value = '';
    $('filelink').set('html', '');
    $('frm_upload').reset();
    $('tr_file').hide();
    $('frm_upload').show();
}

/**
 * Submits directory data to create or update
 */
function submitDirectory()
{
    var action = (selectedId === null)? 'CreateDirectory' : 'UpdateDirectory';
    DirectoryAjax.callAsync(action, $('frm_dir').toQueryString().parseQueryString());
}

/**
 * Submits file data to create or update
 */
function submitFile()
{
    var action = (selectedId === null)? 'CreateFile' : 'UpdateFile';
    DirectoryAjax.callAsync(action, $('frm_file').toQueryString().parseQueryString());
}

/**
 * Brings the share UI up
 */
function share()
{
    if (!cachedForms.share) {
        cachedForms.share = DirectoryAjax.callSync('GetShareForm');
    }
    $('form').set('html', cachedForms.share);
    $('groups').selectedIndex = -1;

    var users = DirectoryAjax.callSync('GetFileUsers', {'id':selectedId});
    sharedFileUsers = {};
    users.each(function (user) {
        sharedFileUsers[user.id] = user.nickname;
    });
    updateShareUsers()
}

/**
 * Fetches and displays users of selected group
 */
function toggleUsers(gid)
{
    var container = $('users').empty();
    if (usersByGroup[gid] === undefined) {
        usersByGroup[gid] = DirectoryAjax.callSync('GetUsers', {'gid':gid});
    }
    usersByGroup[gid].each(function (user) {
        var div = new Element('div'),
            input = new Element('input', {type:'checkbox', id:'chk_'+user.id, value:user.id}),
            label = new Element('label', {'for':'chk_'+user.id});
        input.set('checked', (sharedFileUsers[user.id] !== undefined));
        input.addEvent('click', selectUser)
        label.set('html', user.nickname);
        div.adopt(input, label);
        container.grab(div);
    });
}

/**
 * Adds/removes user to/from shares
 */
function selectUser()
{
    if (this.checked) {
        sharedFileUsers[this.value] = this.getNext('label').get('html');
    } else {
        delete sharedFileUsers[this.value];
    }
    updateShareUsers();
}

/**
 * Updates list of file users
 */
function updateShareUsers()
{
    var list = $('share_users').empty();
    Object.each(sharedFileUsers, function(name, id) {
        list.options[list.options.length] = new Option(name, id);
    });
}

/**
 * Submits share data
 */
function submitShare()
{
    var users = [];
    Array.each($('share_users').options, function(opt) {
        users.push(opt.value);
    });
    DirectoryAjax.callAsync(
        'UpdateFileUsers',
        {'id':selectedId, 'users':users.toString()}
    );
}

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback),
    DirectoryStorage = new JawsStorage('Directory'),
    fileById = {},
    usersByGroup = {},
    sharedFileUsers = {},
    cachedForms = {},
    currentDir = 0,
    filesCount = 0,
    fileTemplate = '',
    statusTemplate = '',
    selectedId;
