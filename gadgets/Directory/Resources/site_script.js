/**
 * Directory Javascript actions
 *
 * @category    Ajax
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Use async mode, create Callback
 */
var DirectoryCallback = {
    CreateDirectory: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    UpdateDirectory: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    CreateFile: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    UpdateFile: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    Delete: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    Move: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    PublishFile: function(response) {
        if (response.type === 'response_notice') {
            fileById[idSet[0]]['public'] = response.data;
            showFileURL(response.data);
        }
        DirectoryAjax.showResponse(response);
    },

    UpdateFileUsers: function(response) {
        if (response.type === 'response_notice') {
            cancel();
            updateFiles(currentDir);
        }
        DirectoryAjax.showResponse(response);
    },

    Search: function(response) {
        if (response.type === 'response_notice') {
            $('#dir_pathbar').hide();
            $('#dir_searchbar').show();
            $('#btn_search_close').show();
            $('#search_res').html(' > ' + response.text);
            displayFiles(response.data);
        } else {
            DirectoryAjax.showResponse(response);
        }
    }
};

/**
 * Initiates Directory
 */
function initDirectory()
{
    var imgDeleteFileObj = $('<img>');

    imgDeleteFileObj.attr('src', imgDeleteFile);
    imgDeleteFileObj.on('click', removeFile);

    imgDeleteFile = imgDeleteFileObj;

    fileTemplate = $('#file_arena').html();

    // Builds icons map (ext => icon)
    for (var type in fileTypes) {
        if (fileTypes.hasOwnProperty(type)) {
            fileTypes[type].forEach(function (ext) {
                if (!iconByExt[ext]) {
                    iconByExt[ext] = type;
                }
            });
        }
    }
    iconByExt.folder = 'folder';

    updateFiles(currentDir);
}

/**
 * Re-fetches files and directories
 */
function updateFiles(parent)
{
    if (parent === undefined) {
        parent = currentDir;
    }
    var shared = ($('#file_filter').val() === 'shared')? true : null,
        foreign = ($('#file_filter').val() === 'foreign')? true : null,
        files = DirectoryAjax.callSync('GetFiles',
            {'id':parent, 'shared':shared, 'foreign':foreign});

    if (files[0] && files[0].user != UID) {
        $('#dir_path').html(' > ' + files[0].username);
    } else {
        updatePath();
    }
    displayFiles(files);
    $('#dir_pathbar').show();
    $('#dir_searchbar').hide();
}

/**
 * Displays files and directories
 */
function displayFiles(files)
{
    // Creates a file element from raw data
    function getFileElement(data)
    {
        var html = $($.parseHTML(substitute(fileTemplate, data)));
        html.find('td')
            .on('click', fileSelect)
            .on('dblclick', function() { fileOpen(data.id); });
        html.find('input').on('click', fileCheck);
        return html;
    }

    var ws = $('#file_arena').empty().css('display', 'table-row-group');
    fileById = {};
    filesCount = files.length;
    files.forEach(function (file) {
        file.ext = file.is_dir? 'folder' : file.filename.split('.').pop();
        file.type = iconByExt[file.ext] || 'file-generic';
        file.icon = '<img src="' + icon_url + file.type + '.png" />';
        file.size = formatSize(file.filesize, 0);
        file.foreign = (file.user !== file.owner);
        if (!file.is_dir) {
            file.url = 'javascript:fileOpen(' + file.id + ');';
        }
        // @TODO: not sure how to clone an object in jQuery, so this is a workaround:
        // http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-an-object
        fileById[file.id] = $.extend(true, {}, file);

        file.filename = (file.filename === null)? '' : file.filename;
        file.shared = file.shared? 'shared' : '';
        file.foreign = file.foreign? 'foreign' : '';
        file['public'] = file['public']? 'public' : '';
        ws.append(getFileElement(file));
    });
}

/**
 * Highlights file/directory on click
 */
function fileSelect(e)
{
    if (this.tagName === 'INPUT' || this.tagName === 'A') {
        return;
    }

    $('#file_arena')
        .find('tr').removeClass('selected')
        .find('input').prop('checked', false);

    $(this)
        .parent().addClass('selected')
        .find('input').prop('checked', true);

    idSet = getSelected();
    updateActions();
    $('#form').empty();
}

/**
 * Checks/Unchecks file/directory
 */
function fileCheck()
{
    if (!$(this).prop('checked')) {
        $(this).parents('tr:first').addClass('selected');
    } else {
        $(this).parents('tr:first').removeClass('selected');
    }
    idSet = getSelected();
    updateActions();
    $('#form').html('');
}

/**
 * Checks/Unchecks all files/directories
 */
function checkAll(checked)
{
    var fileArena = $('#file_arena');
    fileArena.find('input').prop('checked', checked);
    if (checked) {
        fileArena.find('tr').addClass('selected');
    } else {
        fileArena.find('tr').removeClass('selected');
    }
    idSet = getSelected();
    updateActions();
}

/**
 * Fetches ID set of selected files/directories
 */
function getSelected() {
    var checkedList = $('#file_arena').find('input:checkbox:checked'),
        values = [];

    $(checkedList).each(function () {
        values.push($(this).val());
    });

    return values;
}

/**
 * Opens, plays or downloads the file/directory on dblclick
 */
function fileOpen(id)
{
    var file = fileById[id];
    if (file.is_dir) {
        if (file.foreign) {
            id = file.reference;
        }
        window.location.assign(file.url);
    } else {
        if (file.ext === 'txt') {
            openMedia(id, 'text');
        } else if (['jpg', 'jpeg', 'png', 'gif', 'svg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'image');
        } else if (['wav', 'mp3', 'ogg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'audio');
        } else if (['webm', 'mp4', 'ogg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'video');
        } else {
            downloadFile();
        }
    }
}

/**
 * Plays audio/video file
 */
function openMedia(id, type)
{
    var tpl = DirectoryAjax.callSync('PlayMedia', {'id':id, 'type':type});
    $('#form').html(tpl);
}

/**
 * Downloads the file
 */
function downloadFile()
{
    if (idSet === null) return;
    var id = idSet[0],
        file = fileById[id];
    if (!file) {
        file = fileById[id] = DirectoryAjax.callSync('GetFile', {'id':id});
    }
    if (!file.dl_url) {
        fileById[id].dl_url = DirectoryAjax.callSync(
            'GetDownloadURL',
            {'id':id}
        );
    }
    window.location.assign(fileById[id].dl_url);
}

/**
 * Builds directory path
 */
function updatePath()
{
    var pathArr = DirectoryAjax.callSync('GetPath', {'id':currentDir}),
        path = $('#dir_path').html('');
    (pathArr.reverse()).forEach(function (dir, i) {
        path.append(' > ');
        if (i === pathArr.length - 1) {
            path.append(dir.title);
        } else {
            var link = $('<a>');
            link.html(dir.title);
            link.attr('href', dir.url);
            path.append(link);
        }
    });
}

/**
 * Shows/Hides appropriate buttons
 */
function updateActions()
{
    $('#file_actions').find('img').addClass('disabled');
    $('#btn_new_file').removeClass('disabled');
    $('#btn_new_dir').removeClass('disabled');
    if (idSet.length === 0) {
        return;
    }

    $('#btn_delete').removeClass('disabled');
    $('#btn_move').removeClass('disabled');
    if (idSet.length === 1) {
        var selId = idSet[0];
        if (fileById[selId].foreign) {
            $('#btn_share').addClass('disabled');
        } else {
            $('#btn_share').removeClass('disabled');
        }
        if (fileById[selId].is_dir) {
            $('#btn_dl').addClass('disabled');
        } else {
            $('#btn_dl').removeClass('disabled');
        }
        $('#btn_props').removeClass('disabled');
        $('#btn_edit').removeClass('disabled');
    }
}

/**
 * Displays file/directory properties
 */
function props()
{
    if (idSet.length === 0) return;
    var id = idSet[0],
        data = fileById[idSet],
        form;
    if (!data.users) {
        var users = DirectoryAjax.callSync('GetFileUsers', {id:id}),
            id_set = [];
        $.each(users, function (i, user) {
            id_set.push(user.username);
        });
        data.users = id_set.join(', ');
    }
    if (data.is_dir) {
        form = cachedForms.viewDir;
        if (!form) {
            form = DirectoryAjax.callSync('DirectoryForm');
        }
        cachedForms.viewDir = form;
    } else {
        form = cachedForms.viewFile;
        if (!form) {
            form = DirectoryAjax.callSync('FileForm');
        }
        cachedForms.viewFile = form;
    }
    $('#form').html(substitute(form, data));
    if (data['public'] && !data.dl_url) {
        data.dl_url = DirectoryAjax.callSync('GetDownloadURL', {id:id});
    }
    if (data.dl_url) {
        var link = $('#public_url');
        link.innerHTML = site_url + data.dl_url;
        link.href = data.dl_url;
        link.show();
    }
}

/**
 * Calls file/directory edit function
 */
function edit()
{
    if (idSet.length === 0) return;
    if (fileById[idSet[0]].is_dir) {
        editDirectory(idSet[0]);
    } else {
        editFile(idSet[0]);
    }
}

/**
 * Deletes selected files/directories
 */
function del()
{
    if (idSet.length === 0) return;
    if (confirm(confirmDelete)) {
        DirectoryAjax.callAsync('Delete', {'id_set':idSet.join(',')});
    }
}

/**
 * Moves selected directory/file to another directory
 */
function move() {
    if (idSet.length === 0) return;
    var tree = DirectoryAjax.callSync('GetTree', {'id_set':idSet.join(',')}),
        form = $('#form');
    form.html(tree);
    form.find('a').on('click', function () {
        $('#form').find('a').removeClass('selected');
        $(this).addClass('selected');
    });
}

/**
 * Performs moving file/directory
 */
function submitMove() {
    var tree = $('#dir_tree'),
        selected = tree.find('a.selected')[0],
        target = $(selected).attr('id').substring(5, $(selected).attr('id').length);
    DirectoryAjax.callAsync('Move', {'id_set':idSet.join(','), 'target':target});
}

/**
 * Deselects file and hides active form
 */
function cancel()
{
    idSet = [];
    $('#form').html('');
    $('#file_arena').find('.selected').removeClass('selected');
    $('#file_arena').find('input').prop('checked', false);
    updateActions();
}

/**
 * Brings the directory creation UI up
 */
function newDirectory()
{
    cancel();
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {mode:'edit'});
    }
    $('#form').html(cachedForms.editDir);
    $('#frm_dir').find('input[name=title]').focus();
    $('#frm_dir').find('[name=parent]').val(currentDir);
}

/**
 * Brings the edit directory UI up
 */
function editDirectory(id)
{
    if (!cachedForms.editDir) {
        cachedForms.editDir = DirectoryAjax.callSync('DirectoryForm', {mode:'edit'});
    }
    $('#form').html(cachedForms.editDir);
    var data = fileById[id],
        form = $('#frm_dir');
    form.find('[name=id]').val(id);
    form.find('[name=title]').val(data.title);
    form.find('[name=description]').val(data.description);
    form.find('[name=parent]').val(data.parent);
}

/**
 * Brings the file creation UI up
 */
function newFile()
{
    cancel();
    if (!cachedForms.editFile) {
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {mode:'edit'});
    }
    $('#form').html(cachedForms.editFile);
    $('#tr_file').hide();
    $('#frm_upload').show();
    $('#frm_file').find('[name=parent]').val(currentDir);
    $('#frm_file').find('[name=title]').focus();
}

/**
 * Brings the edit file UI up
 */
function editFile(id)
{
    if (!cachedForms.editFile) {
        cachedForms.editFile = DirectoryAjax.callSync('FileForm', {mode:'edit'});
    }
    $('#form').html(cachedForms.editFile);
    var form = $('#frm_file')[0],
        file = fileById[id];
    if (file.foreign) {
        $('#frm_upload').remove();
        $('#parent').remove();
        $('#filename').remove();
        $('#filetype').remove();
        $('#filesize').remove();
        $('#tr_file').remove();
        $('#tr_url').remove();
    } else {
        form.url.value = file.url;
        form.parent.value = file.parent;
        form.filetype.value = file.filetype;
        form.filesize.value = file.filesize;
        if (file.filename) {
            var url = file.dl_url;
            if (!url) {
                url = DirectoryAjax.callSync('GetDownloadURL', {id:id});
                fileById[id].dl_url = url;
            }
            setFilename(file.filename, url);
            $('#filename').val(':nochange:');
        } else {
            $('#tr_file').hide();
            $('#frm_upload').show();
        }
    }
    form.action.value = 'UpdateFile';
    form.id.value = id;
    form.title.value = file.title;
    form.description.value = file.description;
}

/**
 * Uploads file on the server
 */
function uploadFile() {
    var iframe = $('<iframe>');
    iframe.attr('id', 'ifrm_upload');
    iframe.attr('name', 'ifrm_upload');
    $('body').append(iframe);
    $('#btn_ok').attr('disabled', true);
    $('#frm_upload').submit();
}

/**
 * Applies uploaded file into the form
 */
function onUpload(response) {
    if (response.type === 'error') {
        alert(response.message);
        $('#frm_upload').reset();
    } else {
        var filename = encodeURIComponent(response.filename);
        setFilename(filename, '');
        $('#filename').val(filename);
        $('#filetype').val(response.filetype);
        $('#filesize').val(response.filesize);
        if ($('#frm_file').find('[name=title]').val() === '') {
            $('#frm_file').find('[name=title]').val(filename);
        }
    }
    $('#ifrm_upload').remove();
    $('#btn_ok').attr('disabled', false);
}

/**
 * Sets file (not)to be available publicly
 */
function publishFile(published)
{
    DirectoryAjax.callAsync('PublishFile', {
        'id':idSet[0],
        'public':published
    });
}

/**
 * Shows/Hides file URL
 */
function showFileURL(url)
{
    var link = $('#public_url'),
        span = $('#file_' + idSet[0]).find('span');
    if (url !== '') {
        link.innerHTML = site_url + url;
        link.href = url;
        link.show();
        span.addClass('public');
        $('#btn_unpublic').css('display', 'inline');
        $('#btn_public').hide();
    } else {
        link.hide();
        span.removeClass('public');
        $('#btn_public').show();
        $('#btn_unpublic').hide();
    }
}

/**
 * Sets download link of the file
 */
function setFilename(filename, url)
{
    var link = $('<a>');

    link.html(filename);
    if (url !== '') {
        link.attr('href', url);
    }
    $('#filelink').append(link);
    //$('#filelink').append(imgDeleteFile);
    $('#tr_file').show();
    $('#frm_upload').hide();
}

/**
 * Removes the attached file
 */
function removeFile()
{
    $('#filename').val('');
    $('#filelink').html('');
    $('#frm_upload').reset();
    $('#tr_file').hide();
    $('#frm_upload').show();
}

/**
 * Submits directory data to create or update
 */
function submitDirectory()
{
    var action = (idSet.length === 0)? 'CreateDirectory' : 'UpdateDirectory';
    DirectoryAjax.callAsync(action, $.unserialize($('#frm_dir').serialize()));
}

/**
 * Submits file data to create or update
 */
function submitFile()
{
    var action = (idSet.length === 0)? 'CreateFile' : 'UpdateFile';
    DirectoryAjax.callAsync(action, $.unserialize($('#frm_file').serialize()));
}

/**
 * Brings the share UI up
 */
function share()
{
    var id = idSet[0];
    if (!id) return;
    if (!cachedForms.share) {
        cachedForms.share = DirectoryAjax.callSync('ShareForm');
    }
    $('#form').html(cachedForms.share);
    $('#groups').selectedIndex = -1;

    var users = DirectoryAjax.callSync('GetFileUsers', {'id':id});
    sharedFileUsers = {};
    $.each(users, function (i, user) {
        sharedFileUsers[user.id] = user.username;
    });
    updateShareUsers();

    // Public link
    var file = fileById[id];
    $('#public_ui').css('display', file.is_dir? 'none' : '');
    if (file['public'] && !file.dl_url) {
        file.dl_url = DirectoryAjax.callSync('GetDownloadURL', {id:id});
    }
    if (file.dl_url) {
        showFileURL(file.dl_url);
    }
}

/**
 * Fetches and displays users of selected group
 */
function toggleUsers(gid)
{
    var container = $('#users').empty();
    if (usersByGroup[gid] === undefined) {
        usersByGroup[gid] = DirectoryAjax.callSync('GetUsers', {'gid':gid});
    }
    $.each(usersByGroup[gid], function(i, user) {
        if (user.id == UID) return;

        var div = $('<div>'),
            input = $('<input>').attr({
                type: 'checkbox',
                id: 'chk_' + user.id,
                value: user.id
            }),
            label = $('<label>').attr({'for': 'chk_' + user.id});

        input.prop('checked', (sharedFileUsers[user.id] !== undefined));
        input.on('click', selectUser);
        label.html(user.nickname + ' (' + user.username + ')');
        div.append(input, label);
        container.append(div);
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
    var list = $('#share_users').empty();
    $.each(sharedFileUsers, function(id, name) {
        list.options[list.options.length] = new Option(name, id);
    });
}

/**
 * Submits share data
 */
function submitShare()
{
    var users = [];
    $.each($('#share_users').find('options'), function(opt) {
        users.push(opt.value);
    });
    DirectoryAjax.callAsync(
        'UpdateFileUsers',
        {'id':idSet.join(','), 'users':users.join(',')}
    );
}

/**
 * Shows/Hides search close button
 */
function onSearchChange(input)
{
    $('#btn_search_close').css('display',(input.value === '') ? 'none' : 'inline');
}

/**
 * Search among files and directories
 */
function performSearch()
{
    var query = $.unserialize($('#frm_search').serialize());
    query.id = currentDir;
    DirectoryAjax.callAsync('Search', query);
}

/**
 * Displays advanced search UI
 */
function advancedSearch(self)
{
    $('#advanced_search').css('display', 'table');
    self.hide();
}

/**
 * Clears the search box and resets data grid
 */
function closeSearch()
{
    $('#btn_search_close').hide();
    $('#advanced_search').hide();
    $('#file_search').val('');
    updateFiles();
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

/**
 * Substitute Method (jQuery Doesn't Have, Unlike MooTools)
 * http://stackoverflow.com/questions/2621170/has-any-method-like-substitute-of-mootools-in-jquery
 *
 * @param str
 * @param sub
 * @returns {*}
 */
function substitute(str, sub) {
    return str.replace(/\{(.+?)\}/g, function($0, $1) {
        return $1 in sub ? sub[$1] : $0;
    });
}

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback),
    fileById = {},
    iconByExt = {},
    usersByGroup = {},
    sharedFileUsers = {},
    cachedForms = {},
    filesCount = 0,
    fileTemplate = '',
    statusTemplate = '',
    wsClickEvent = null,
    idSet = [];

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
};