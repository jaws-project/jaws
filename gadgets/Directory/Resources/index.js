/**
 * Directory Javascript actions
 *
 * @category    Ajax
 * @package     Directory
 */

/**
 * Use async mode, create Callback
 */
var DirectoryCallback = {
    CreateFile: function(response) {
        if (response.type === 'response_notice') {
            cancel();
        }
        DirectoryAjax.showResponse(response);
    }
};

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
 * Submits file data to create or update
 */
function submitFile()
{
    var action = (fileId === 0)? 'CreateFile' : 'UpdateFile';

    uploadedFileInfo.parent = 0;
    uploadedFileInfo.description = getEditorValue('description');
    uploadedFileInfo.title = $('#frm_file #title').val();
    uploadedFileInfo.tags = $('#frm_file #tags').val();
    uploadedFileInfo.hidden = $('#frm_file #hidden').val();

    DirectoryAjax.callAsync(action, uploadedFileInfo);
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
        uploadedFileInfo = {};
        uploadedFileInfo.user_filename = response.user_filename;
        uploadedFileInfo.host_filename = hostFilename;
        uploadedFileInfo.mime_type = response.mime_type;
        uploadedFileInfo.file_size = response.file_size;
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
    $('#file_link').append(link);
    //$('#file_link').append(imgDeleteFile);
    $('#tr_file').show();
    $('#frm_upload').hide();
}

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback, 'index.php');
var uploadedFileInfo = {};