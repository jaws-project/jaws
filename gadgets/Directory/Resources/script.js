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

    $('#frm_search select, #file_search').change(performSearch);
    $('#start_date, #end_date').on('keypress', performSearch);

    updateFiles(currentDir);
    updateActions();
}

/**
 * Re-fetches files and directories
 */
function updateFiles(parent)
{
    if (parent === undefined) {
        parent = currentDir;
    }
    DirectoryAjax.callAsync('GetFiles', {'curr_action':currentAction, 'parent':parent}, displayFiles);

    updatePath();
    $('#dir_searchbar').hide();
    $('#dir_pathbar').show();
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
        if (data.hidden) {
            $(html[1]).addClass('is-hidden');
        }
        return html;
    }

    var ws = $('#file_arena').empty().css('display', 'table-row-group');
    fileById = {};
    filesCount = files.length;
    files.forEach(function (file) {
        file.ext = file.is_dir? 'folder' : file.host_filename.split('.').pop();
        file.type = file.filetype || '-';
        file.icon = '<img src="' + icon_url + (iconByExt[file.ext] || 'file-generic') + '.png" />';
        file.size = formatSize(file.filesize, 0);
        if (!file.is_dir) {
            file.url = 'javascript:fileOpen(' + file.id + ');';
        }
        // @TODO: not sure how to clone an object in jQuery, so this is a workaround:
        // http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-an-object
        fileById[file.id] = $.extend(true, {}, file);

        file.host_filename = (file.host_filename === null)? '' : file.host_filename;
        ws.append(getFileElement(file));
    });
}

/**
 * Highlights file/directory on click
 */
function fileSelect(e)
{
    if (e.target.tagName === 'A') {
        return;
    }

    var input = $(this).parent().find('input');
    if (e.target.tagName === 'INPUT') {
        if (input.prop('checked')) {
            $(this).parent().addClass('selected');
        } else {
            $(this).parent().removeClass('selected');
        }
    } else {
        $('#file_arena')
            .find('tr').removeClass('selected')
            .find('input').prop('checked', false);
        input.prop('checked', !input.prop('checked'));
        $(this).parent().addClass('selected');
    }

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
        window.location.assign(file.url);
    } else {
        if (file.ext === 'txt') {
            openMedia(id, 'text');
        } else if (['jpg', 'jpeg', 'png', 'gif', 'svg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'image');
        } else if (['wav', 'mp3'].indexOf(file.ext) !== -1) {
            openMedia(id, 'audio');
        } else if (['webm', 'mp4', 'ogg'].indexOf(file.ext) !== -1) {
            openMedia(id, 'video');
        } else {
            downloadFile(id);
        }
    }
}

/**
 * Plays audio/video file
 */
function openMedia(id, type)
{
    var action, params;
    if (currentAction == 'Directory' || (opener && opener.the_textarea)) {
        action = 'PlayMedia';
        params = {'id':id, 'type':type};
    } else {
        action = 'GetDownloadURL';
        params = {'id':id};
    }
    DirectoryAjax.callAsync(action, params, function(response) {
        if (currentAction == 'Directory') {
            $('#form').html(response);
            return;
        }

        if (top.tinymce) {
            top.tinymce.activeEditor.windowManager.getParams().oninsert(response);
            top.tinyMCE.activeEditor.windowManager.close();
        } else if (opener.CKEDITOR) {
            window.opener.CKEDITOR.tools.callFunction(ckFuncIndex, response);
            close();
        } else if (opener.the_textarea) {
            opener.insertTags(opener.the_textarea, response, '', '');
            close();
        }
    });
}

/**
 * Downloads the file
 */
function downloadFile(id)
{
    if (id == undefined) {
        id = getSelected()[0];
    }
    var file = fileById[id];
    if (!file) {
        file = fileById[id] = DirectoryAjax.callSync('GetFile', {'id':id});
    }
    if (file.is_dir) {
        return;
    }
    if (file.dl_url) {
        window.location.assign(file.dl_url);
    } else {
        DirectoryAjax.callAsync('GetDownloadURL', {'id':id}, function(resposne) {
            fileById[id].dl_url = resposne;
            window.location.assign(resposne);
        });
    }
}

/**
 * Builds directory path
 */
function updatePath()
{
    var path = $('#dir_path').html('');
    DirectoryAjax.callAsync('GetPath', {'curr_action':currentAction, 'id':currentDir}, function(pathArr) {
        pathArr.reverse().forEach(function (dir, i) {
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
    $('#btn_search').removeClass('disabled');
    if (idSet.length === 0) {
        return;
    }

    $('#btn_delete').removeClass('disabled');
    $('#btn_move').removeClass('disabled');
    if (idSet.length === 1) {
        var selId = idSet[0];
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
    form.find('[name=parent]').val(data.parent);
    form.find('[name=hidden]').prop('checked', data.hidden);
    changeEditorValue('description', data.description);
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
    form.parent.value = file.parent;
    form.filetype.value = file.filetype;
    form.filesize.value = file.filesize;
    form.user_filename.value = file.user_filename;
    form.host_filename.value = file.host_filename;
    if (file.user_filename) {
        setFilename(file.user_filename);
        $('#host_filename').val(':nochange:');
    } else {
        $('#tr_file').hide();
        $('#frm_upload').show();
    }
    form.action.value = 'UpdateFile';
    form.id.value = id;
    form.title.value = file.title;
    form.tags.value = file.tags;
    form.hidden.checked = file.hidden;
    changeEditorValue('description', file.description);
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
        var hostFilename = encodeURIComponent(response.host_filename);
        setFilename(hostFilename, '');
        $('#user_filename').val(response.user_filename);
        $('#host_filename').val(hostFilename);
        $('#filetype').val(response.filetype);
        $('#filesize').val(response.filesize);
        if ($('#frm_file').find('[name=title]').val() === '') {
            $('#frm_file').find('[name=title]').val(response.user_filename.replace(/\.[^/.]+$/, ''));
        }
    }
    $('#ifrm_upload').remove();
    $('#btn_ok').attr('disabled', false);
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
    var action = (idSet.length === 0)? 'CreateDirectory' : 'UpdateDirectory',
        data = $.unserialize($('#frm_dir').serialize());
    data.description = getEditorValue('description');
    DirectoryAjax.callAsync(action, data);
}

/**
 * Submits file data to create or update
 */
function submitFile()
{
    var action = (idSet.length === 0)? 'CreateFile' : 'UpdateFile',
        data = $.unserialize($('#frm_file').serialize());
    data.description = getEditorValue('description');
    DirectoryAjax.callAsync(action, data);
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
 * Shows/Hides search UI
 */
function toggleSearch()
{
    $('#frm_search').toggle();
}

/**
 * Clears the search box and resets data grid
 */
function closeSearch()
{
    $('#btn_search_close').hide();
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