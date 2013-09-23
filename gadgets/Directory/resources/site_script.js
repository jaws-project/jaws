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
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    UpdateDirectory: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    DeleteDirectory: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    CreateFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    UpdateFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    DeleteFile: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    PublishFile: function(response) {
        if (response.css === 'notice-message') {
            fileById[selectedId]['public'] = response.data;
            showFileURL(response.data);
        }
        $('simple_response').set('html', response.message);
    },

    Move: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    UpdateFileUsers: function(response) {
        if (response.css === 'notice-message') {
            cancel();
            updateFiles(currentDir);
        }
        $('simple_response').set('html', response.message);
    },

    Search: function(response) {
        if (response.css === 'notice-message') {
            displayFiles(response.data);
            $('dir_pathbar').innerHTML = response.message;
        } else {
            $('simple_response').innerHTML = response.message;
        }
    }
};

/**
 * Initiates Directory
 */
function initDirectory()
{
    DirectoryAjax.backwardSupport();
    imgDeleteFile = new Element('img', {src:imgDeleteFile});
    imgDeleteFile.addEvent('click', removeFile);
    fileTemplate = $('file_arena').get('html');
    statusTemplate = $('statusbar').get('html');
    pageBody = document.body;
    // viewType = DirectoryStorage.fetch('view_type');
    // if (viewType === null) viewType = 'list';
    // $('view_type').value = viewType;

    // Build icons map (ext => icon)
    Object.each(fileTypes, function (values, type) {
        values.each(function (ext) {
            if (!iconByExt[ext]) {
                iconByExt[ext] = type;
            }
        });
    });
    iconByExt.folder = 'folder';

    viewType = 'list';
    //changeFileView(viewType);
    currentDir = Number(DirectoryStorage.fetch('current_dir'));
    openDirectory(currentDir);
}

/**
 * Re-feches files and directories
 */
function updateFiles(parent)
{
    if (parent === undefined) {
        parent = currentDir;
    }
    var shared = ($('file_filter').value === 'shared')? true : null,
        foreign = ($('file_filter').value === 'foreign')? true : null,
        files = DirectoryAjax.callSync('GetFiles', 
            {'parent':parent, 'shared':shared, 'foreign':foreign});
    toggleSearch(false);
    updatePath();
    displayFiles(files);
}

/**
 * Displays files and directories
 */
function displayFiles(files)
{
    // Creates a file element from raw data
    function getFileElement(data)
    {
        var html = fileTemplate.substitute(data),
            div = new Element('div', {'html':html}).getFirst();
        div.addEvent('click', fileSelect);
        div.addEvent('dblclick', fileOpen);
        div.fid = data.id;
        return div;
    }

    var ws = $('file_arena').empty().show();
    fileById = {};
    filesCount = files.length;
    files.each(function (file) {
        file.ext = file.is_dir? 'folder' : file.filename.split('.').pop();
        file.type = iconByExt[file.ext] || 'file-generic';
        file.icon = '<img src="' + icon_url + file.type + '.png" />';
        file.size = formatSize(file.filesize, 0);
        file.foreign = (file.user !== file.owner);
        fileById[file.id] = Object.clone(file);
        file.filename = (file.filename === null)? '' : file.filename;
        file.shared = file.shared? 'shared' : '';
        file.foreign = file.foreign? 'foreign' : '';
        file['public'] = file['public']? 'public' : '';
        ws.grab(getFileElement(file));
    });
    //$('statusbar').set('html', filesCount + ' items');
}

/**
 * Changes display type to icon/list
 */
function changeFileView(view)
{
    $('file_arena').set('class', view + '-view');
    //DirectoryStorage.update('view_type', view);
}

/**
 * Highlights file/directory on click
 */
function fileSelect(e)
{
    var ws = $('file_arena'),
        st = $('statusbar'),
        data = Object.clone(fileById[this.fid]);
    cancel();
    this.addClass('selected');
    selectedId = this.fid;
    // data.size = formatSize(data.filesize, 2);
    // st.set('html', statusTemplate.substitute(data));
    // st.getElement('#stat_file_size').style.display =
        // (data.size === '')? 'none' : '';
    e.stop();
    updateActions();
}

/**
 * Opens, plays or downloads the file/directory on dblclick
 */
function fileOpen()
{
    var file = fileById[selectedId],
        id = file.id;
    if (file.is_dir) {
        if (file.foreign) {
            id = file.reference;
        }
        openDirectory(id);
    } else {
        if (['wav', 'mp3', 'ogg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'audio');
        } else if (['webm', 'mp4', 'ogg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'video');
        } else {
            openFile(id);
        }
    }
}

/**
 * Navigates into the directory
 */
function openDirectory(id)
{
    currentDir = id;
    selectedId = null;
    DirectoryStorage.update('current_dir', id);
    updateFiles(id);
    cancel();
    toggleSearch(false);
}

/**
 * Opens/Downloads the file
 */
function openFile(id)
{
    var file = fileById[id],
        docDim = document.getSize(),
        height = 500,
        width = 800,
        left = (docDim.x - width) / 2,
        top = (docDim.y - height) / 2,
        specs = 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top;
    if (!file) {
        file = fileById[id] = DirectoryAjax.callSync('GetFile', {'id':id});
    }
    if (!file.open_url) {
        fileById[id].open_url = DirectoryAjax.callSync(
            'GetDownloadURL',
            {'id':id, 'inline':true}
        );
    }
    if (file.filename) {
        window.open(
            fileById[id].open_url,
            '_blank',
            specs,
            true
        );
    }
}

/**
 * Plays audio/video file
 */
function openMedia(id, type)
{
    var tpl = DirectoryAjax.callSync('PlayMedia', {'id':id, 'type':type});
    $('form').innerHTML = tpl;
    pageBody.removeEvent('click', cancel);
}

/**
 * Downloads the file
 */
function downloadFile()
{
    if (selectedId === null) return;
    var id = selectedId,
        file = fileById[id];
    if (!file) {
        file = fileById[id] = DirectoryAjax.callSync('GetFile', {'id':id});
    }
    if (!file.dl_url) {
        fileById[id].dl_url = DirectoryAjax.callSync(
            'GetDownloadURL',
            {'id':id, 'inline':false}
        );
    }
    window.location.assign(fileById[id].dl_url);
}

/**
 * Builds the directory path
 */
function updatePath()
{
    var pathArr = DirectoryAjax.callSync('GetPath', {'id':currentDir}),
        path = $('dir_pathbar').set('html', ''),
        link = new Element('img', {
            'src':'gadgets/Directory/images/home.png',
            'title': 'Home'
        });
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
 * Shows/Hides appropriate buttons
 */
function updateActions()
{
    if (selectedId === null) {
        $('file_actions').style.visibility = 'hidden';
    } else {
        $('file_actions').style.visibility = 'visible';
        if (fileById[selectedId].is_dir) {
            $('btn_download').hide();
        } else {
            $('btn_download').show();
        }
        if (fileById[selectedId].foreign) {
            $('btn_share').hide();
        } else {
            $('btn_share').show();
        }
    }
}

/**
 * Displays file/directory properties
 */
function props()
{
    if (selectedId === null) return;
    var data = fileById[selectedId],
        form;
    // console.log(selectedId);
    if (!data.users) {
        var users = DirectoryAjax.callSync('GetFileUsers', {id:selectedId}),
            id_set = [];
        users.each(function (user) {
            id_set.push(user.username);
        });
        data.users = id_set.join(', ');
    }
    if (data.is_dir) {
        form = cachedForms.viewDir;
        if (!form) {
            form = DirectoryAjax.callSync('DirectoryForm', {mode:'view'});
        }
        cachedForms.viewDir = form;
    } else {
        form = cachedForms.viewFile;
        if (!form) {
            form = DirectoryAjax.callSync('FileForm', {mode:'view'});
        }
        cachedForms.viewFile = form;
    }
    $('form').set('html', form.substitute(data));
    if (data['public'] && !data.dl_url) {
        data.dl_url = DirectoryAjax.callSync('GetDownloadURL', {id:selectedId});
    }
    if (data.dl_url) {
        showFileURL(data.dl_url);
    }
    pageBody.removeEvent('click', cancel);
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
    pageBody.removeEvent('click', cancel);
}

/**
 * Deletes selected directory/file
 */
function del()
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
 * Moves selected directory/file to another directory
 */
function move() {
    var tree = DirectoryAjax.callSync('GetTree', {'root':0}),
        form = $('form');
    form.set('html', tree);
    form.getElements('a').addEvent('click', function () {
        $('form').getElements('a').removeClass('selected');
        this.className = 'selected';
    });
    pageBody.removeEvent('click', cancel);
}

/**
 * Performs moving file/directory
 */
function submitMove() {
    var tree = $('dir_tree'),
        selected = tree.getElement('a.selected'),
        target = selected.id.substr(5, selected.id.length - 5);
    //console.log(id);
    DirectoryAjax.callAsync('Move', {'id':selectedId, 'target':target});
}

/**
 * Deselects file and hides the form
 */
function cancel()
{
    selectedId = null;
    $('form').set('html', '');
    $('file_arena').getElements('.selected').removeClass('selected');
    $('statusbar').set('html', filesCount + ' items');
    updateActions();
    pageBody.addEvent('click', cancel);
}

/**
 * Brings the directory creation UI up
 */
function newDirectory()
{
    selectedId = null;
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {mode:'edit'});
    }
    $('form').set('html', cachedForms.editDir);
    $('frm_dir').title.focus();
    $('frm_dir').parent.value = currentDir;
    pageBody.removeEvent('click', cancel);
}

/**
 * Brings the edit directory UI up
 */
function editDirectory()
{
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {mode:'edit'});
    }
    $('form').set('html', cachedForms.editDir);
    var data = fileById[selectedId],
        form = $('frm_dir');
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
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {mode:'edit'});
    }
    $('form').set('html', cachedForms.editFile);
    $('tr_file').hide();
    $('frm_upload').show();
    $('frm_file').parent.value = currentDir;
    $('frm_file').title.focus();
    pageBody.removeEvent('click', cancel);
}

/**
 * Brings the edit file UI up
 */
function editFile()
{
    if (!cachedForms.editFile) {
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {mode:'edit'});
    }
    $('form').set('html', cachedForms.editFile);
    var form = $('frm_file'),
        file = fileById[selectedId];
    if (file.foreign) {
        $('frm_upload').remove();
        $('parent').remove();
        $('filename').remove();
        $('filetype').remove();
        $('filesize').remove();
        $('tr_file').remove();
        $('tr_url').remove();
    } else {
        form.url.value = file.url;
        form.parent.value = file.parent;
        form.filetype.value = file.filetype;
        form.filesize.value = file.filesize;
        if (file.filename && !file.dl_url) {
            var url = DirectoryAjax.callSync('GetDownloadURL', {id:selectedId});
            fileById[selectedId].dl_url = url;
            setFilename(file.filename, url);
            $('filename').value = ':nochange:';
        } else {
            $('tr_file').hide();
            $('frm_upload').show();
        }
    }
    form.action.value = 'UpdateFile';
    form.id.value = selectedId;
    form.title.value = file.title;
    form.description.value = file.description;
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
        var filename = encodeURIComponent(response.filename);
        setFilename(filename, '');
        $('filename').value = filename;
        $('filetype').value = response.filetype;
        $('filesize').value = response.filesize;
        if ($('frm_file').title.value === '') {
            $('frm_file').title.value = filename;
        }
    }
    $('ifrm_upload').destroy();
}

/**
 * Sets file (not)to be available publicly
 */
function publishFile(published)
{
    DirectoryAjax.callAsync('PublishFile', {
        'id':selectedId,
        'public':published
    });
}

/**
 * Shows/Hides file URL
 */
function showFileURL(url)
{
    var link = $('public_url');
    if (url !== '') {
        link.innerHTML = site_url + url;
        link.href = url;
        link.show();
        $('btn_unpublic').show();
        $('btn_public').hide();
    } else {
        link.hide();
        $('btn_public').show();
        $('btn_unpublic').hide();
    }
}

/**
 * Sets download link of the file
 */
function setFilename(filename, url)
{
    var link = new Element('a', {'html':filename, 'target':'_blank'});
    if (url !== '') {
        link.href = url;
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
    //console.log(cachedForms);
    $('form').set('html', cachedForms.share);
    $('groups').selectedIndex = -1;

    var users = DirectoryAjax.callSync('GetFileUsers', {'id':selectedId});
    sharedFileUsers = {};
    users.each(function (user) {
        sharedFileUsers[user.id] = user.username;
    });
    updateShareUsers();
    pageBody.removeEvent('click', cancel);
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
        if (user.id == UID) return;
        var div = new Element('div'),
            input = new Element('input', {type:'checkbox', id:'chk_'+user.id, value:user.id}),
            label = new Element('label', {'for':'chk_'+user.id});
        input.set('checked', (sharedFileUsers[user.id] !== undefined));
        input.addEvent('click', selectUser);
        label.set('html', user.username);
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
        {'id':selectedId, 'users':users.join(',')}
    );
}

/**
 * Shows/hides search form
 */
function toggleSearch(show)
{
    var form = $('frm_search');
    if (show === undefined) {
        show = !form.isDisplayed();
    }
    if (show) {
        form.show();
        $('file_filter').hide();
        $('file_search').focus();
    } else {
        form.hide();
        $('file_filter').show();
        $('file_search').value = '';
    }
}

/**
 * Search among files and directories
 */
function performSearch()
{
    var query = $('file_search').value;
    if (query.length < 2) {
        alert('The search query must contains 2 letters at least.')
    }
    DirectoryAjax.callAsync('Search', {'query':query});
}

/**
 * Formats size in bytes to human readbale
 */
function formatSize(size, precision)
{
    var i = -1,
        byteUnits = [' KB', ' MB', ' GB', ' TB'];
    if (size === null) return '';
    size = Number(size);
    if (precision > 0 && size < 1024) return size + ' bytes';
    do {
        size = size / 1024;
        i++;
    } while (size > 1024);

    return Math.max(size, 1).toFixed(precision) + byteUnits[i];
}

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback),
    DirectoryStorage = new JawsStorage('Directory'),
    fileById = {},
    iconByExt = {},
    usersByGroup = {},
    sharedFileUsers = {},
    cachedForms = {},
    currentDir = 0,
    filesCount = 0,
    fileTemplate = '',
    statusTemplate = '',
    wsClickEvent = null,
    pageBody,
    selectedId;

var fileTypes = {
    'font-generic' : ['ttf', 'otf', 'fon', 'pfa', 'afm', 'pfb'],
    'audio-generic' : ['mp3', 'wav', 'aac', 'flac', 'ogg', 'wma', 'cda', 'voc', 'midi', 'ac3', 'bonk', 'mod'],
    'image-generic' : ['gif', 'png', 'jpg', 'jpeg', 'raw', 'bmp', 'tiff', 'svg'],
    'package-generic' : ['tar', 'tar.gz', 'tgz', 'zip', 'gzip', 'rar', 'rpm', 'deb', 'iso', 'bz2', 'bak', 'gz'],
    'video-generic' : ['mpg', 'mpeg', 'avi', 'wma', 'rm', 'asf', 'flv', 'mov', 'mp4'],
    'help-contents' : ['hlp', 'chm', 'manual', 'man'],
    'text-generic' : ['txt', ''],
    'text-html' : ['html', 'htm', 'mht'],
    'text-java' : ['jsp', 'java', 'jar'],
    'text-python' : ['py'],
    'text-script' : ['sh', 'pl', 'asp', 'c', 'css', 'htaccess'],
    'office-document-template' : ['stw', 'ott'],
    'office-document' : ['doc', 'docx', 'sxw', 'odt', 'rtf', 'sdw'],
    'office-presentation-template' : ['pot', 'otp', 'sti'],
    'office-presentation' : ['ppt', 'odp', 'sxi'],
    'office-spreadsheet-template' : ['xlt', 'ots', 'stc'],
    'office-spreadsheet' : ['xls', 'ods', 'sxc', 'sdc'],
    'office-drawing-template' : [],
    'office-drawing' : ['sxd', 'sda', 'sdd', 'odg'],
    'application-executable' : ['exe'],
    'application-php' : ['php', 'phps'],
    'application-rss+xml' : ['xml', 'rss', 'atom', 'rdf'],
    'application-pdf' : ['pdf'],
    'application-flash' : ['swf'],
    'application-ruby' : ['rb']
}