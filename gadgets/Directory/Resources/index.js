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
    SaveFile: function(response) {
        if (response.type === 'alert-success') {
            stopAction();
        }
        DirectoryAjax.showResponse(response);
    },
    DeleteFile: function(response) {
        if (response.type === 'alert-success') {
            stopAction();
        }
        DirectoryAjax.showResponse(response);
    }
};

/**
 * stop Action
 */
function stopAction()
{
    uploadedFileInfo = {};
    uploadedThumbnailPath = null;
    setEditorValue('#description', '');
    $('#frm_upload')[0].reset()
    $('#frm_thumbnail_upload')[0].reset()
    $('#frm_upload').show();
    $('#tr_file').hide();
    $('#frm_file #file_link').html('');
    $('#frm_file #title').val('');
    $('#frm_file #tags').val('');
    $('#frm_file #published').prop('checked', '');

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
 * Uploads thumbnail file on the server
 */
function uploadThumbnailFile() {
    var iframe = $('<iframe>');
    iframe.attr('id', 'ifrm_upload');
    iframe.attr('name', 'ifrm_upload');
    $('body').append(iframe);
    $('#btn_ok').attr('disabled', true);
    $('#frm_thumbnail_upload').submit();
}

/**
 * Submits file data to create or update
 */
function submitFile()
{
    uploadedFileInfo.id = jaws.gadgets.Directory.fileId;
    uploadedFileInfo.parent = jaws.gadgets.Directory.parentId;
    uploadedFileInfo.description = getEditorValue('#description');
    uploadedFileInfo.title = $('#frm_file #title').val();
    uploadedFileInfo.tags = $('#frm_file #tags').val();
    uploadedFileInfo.thumbnailPath = uploadedThumbnailPath;
    uploadedFileInfo.public = $('#frm_file #public').prop('checked');
    if ($('#frm_file #published').prop('checked') != undefined) {
        uploadedFileInfo.published = $('#frm_file #published').prop('checked');
    }


    DirectoryAjax.callAsync('SaveFile', uploadedFileInfo);
}

/**
 * Submits file data to create or update
 */
function deleteFile(id) {
    if (confirm(jaws.gadgets.Directory.confirmDelete)) {
        DirectoryAjax.callAsync('DeleteFile', {fileId: id});
    }
}

/**
 * Applies uploaded file into the form
 */
function onUpload(response) {
    if (response.type === 'error') {
        alert(response.message);
        if(response.upload_type=='file') {
            $('#frm_upload').reset();
        } else {
            $('#frm_thumbnail_upload').reset();
        }
    } else {
        var hostFilename = encodeURIComponent(response.host_filename);
        if(response.upload_type == 'file') {
            setFilename(hostFilename, '');
            uploadedFileInfo = {};
            uploadedFileInfo.user_filename = response.user_filename;
            uploadedFileInfo.host_filename = hostFilename;
            uploadedFileInfo.mime_type = response.mime_type;
            uploadedFileInfo.file_size = response.file_size;
            if ($('#frm_file').find('[name=title]').val() === '') {
                $('#frm_file').find('[name=title]').val(response.user_filename.replace(/\.[^/.]+$/, ''));
            }
        } else {
            uploadedThumbnailPath = hostFilename;
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

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Directory':
            initDatePicker('filter_from_date');
            initDatePicker('filter_to_date');
            break;

        case 'UploadFileUI':
            $('#tr_file').hide();
            $('#tr_thumbnail').hide();
            $('#frm_upload').show();
            break;

    }
});

var DirectoryAjax = new JawsAjax('Directory', DirectoryCallback, 'index.php');
var uploadedFileInfo = {}, uploadedThumbnailPath = "";
var fileId = 0;
